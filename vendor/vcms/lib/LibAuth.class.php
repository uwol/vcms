<?php
/*
This file is part of VCMS.

VCMS is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

VCMS is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with VCMS. If not, see <http://www.gnu.org/licenses/>.
*/

namespace vcms;

use PDO;

class LibAuth{
	var $id;
	var $anrede;
	var $titel;
	var $praefix;
	var $vorname;
	var $suffix;
	var $nachname;

	var $gruppe;
	var $aemter = array();
	var $possibleGruppen = array();

	var $isLoggedIn = false;

	/*
	* tries to login with email and password
	*/
	function login($email, $password){
		global $libGlobal, $libDb, $libPerson, $libTime, $libSecurityManager, $libString;

		$email = trim(strtolower($email));
		$password = trim($password);

		//clean memory
		$this->id = '';
		$this->anrede = '';
		$this->titel = '';
		$this->praefix = '';
		$this->vorname = '';
		$this->suffix = '';
		$this->nachname = '';

		$this->gruppe = '';
		$this->aemter = array();
		$this->possibleGruppen = array();

		$this->isLoggedIn = false;

		//collect potential valid groups
		$stmt = $libDb->prepare('SELECT bezeichnung FROM base_gruppe');
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($row['bezeichnung'] != 'T' && $row['bezeichnung'] != 'X' && $row['bezeichnung'] != 'V'){
				$this->possibleGruppen[] = $row['bezeichnung'];
			}
		}

		/*
		* check for problem cases
		*/

		//1. no email given
		if($email == ''){
			$libGlobal->errorTexts[] = 'Die E-Mail-Adresse fehlt.';
			return false;
		}

		//2. no password given
		if($password == ''){
			$libGlobal->errorTexts[] = 'Das Passwort fehlt.';
			return false;
		}

		$stmt = $libDb->prepare('SELECT id, anrede, titel, praefix, vorname, suffix, gruppe, name, email, password_hash FROM base_person WHERE email=:email');
		$stmt->bindValue(':email', $email);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		//3. no user with that email address given
		if(!is_array($row) || !isset($row['id']) || !is_numeric($row['id']) || !($row['id'] > 0)){
			//error message has to be imprecise
			$libGlobal->errorTexts[] = 'E-Mail-Adresse oder Passwort falsch.';
			return false;
		}

		//4. user is in an invalid group
		if(!in_array($row['gruppe'], $this->possibleGruppen)){
			$libGlobal->errorTexts[] = 'Gruppe falsch.';
			return false;
		}

		//5. missing password hash
		if(trim($row['password_hash'] == '')){
			$libGlobal->errorTexts[] = 'In der Datenbank ist kein Passwort-Hash vorhanden.';
			return false;
		}

		//6. check number of mistaken login attempts; brute force prevention
		$stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM sys_log_intranet WHERE mitglied=:mitglied AND aktion=2 AND DATEDIFF(NOW(), datum) = 0');
		$stmt->bindValue(':mitglied', $row['id'], PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('number', $numberOfMistakenLoginsToday);
		$stmt->fetch();

		if($numberOfMistakenLoginsToday > 0){
			$stmt = $libDb->prepare('SELECT datum FROM sys_log_intranet WHERE mitglied=:mitglied AND aktion=2 AND DATEDIFF(NOW(), datum) = 0 ORDER BY datum DESC LIMIT 0,1');
			$stmt->bindValue(':mitglied', $row['id'], PDO::PARAM_INT);
			$stmt->execute();
			$stmt->bindColumn('datum', $lastMistakenLoginToday);
			$stmt->fetch();

			$nextPossibleLoginTimeStamp = strtotime($lastMistakenLoginToday) + pow(2, $numberOfMistakenLoginsToday);
			$secondsToNextPossibleLogin = $nextPossibleLoginTimeStamp - time();

			if($secondsToNextPossibleLogin > 0){
				if($secondsToNextPossibleLogin < 120){
					$libGlobal->errorTexts[] = 'Dieses Konto ist für die nächsten ' .$secondsToNextPossibleLogin. ' Sekunden gesperrt, da zu viele erfolglose Anmeldeversuche unternommen wurden.';
				} else {
					$minutesToNextPossibleLogin = floor($secondsToNextPossibleLogin / 60);
					$libGlobal->errorTexts[] = 'Dieses Konto ist für die nächsten ' .$minutesToNextPossibleLogin. ' Minuten gesperrt, da zu viele erfolglose Anmeldeversuche unternommen wurden.';
				}

				return false;
			}
		}

		//7. check password
		if($this->checkPassword($password, $row['password_hash'])){
			//a. login successful
			$this->isLoggedIn = true;

			$this->id = $row['id'];
			$this->anrede = $row['anrede'];
			$this->titel = $row['titel'];
			$this->praefix = $row['praefix'];
			$this->vorname = $row['vorname'];
			$this->suffix = $row['suffix'];
			$this->nachname = $row['name'];
			$this->gruppe = $row['gruppe'];

			//b. determine functions
			$stmt = $libDb->prepare('SELECT * FROM base_semester WHERE semester=:semester_aktuell OR semester=:semester_naechst OR semester=:semester_vorherig');
			$stmt->bindValue(':semester_aktuell', $libTime->getSemesterName());
			$stmt->bindValue(':semester_naechst', $libTime->getFollowingSemesterName());
			$stmt->bindValue(':semester_vorherig', $libTime->getPreviousSemesterName());
			$stmt->execute();

			//for all semesters
			while($row2 = $stmt->fetch(PDO::FETCH_ASSOC)){
				$possibleAemter = $libSecurityManager->getPossibleAemter();

				//for all functions
				foreach($possibleAemter as $amt){
					//does the member has the function in the semester?
					if($row2[$amt] == $row['id']){
						//save function
						$this->aemter[] = $amt;
					}
				}
			}

			//remove redundant functions from multiple semesters
			$this->aemter = array_unique($this->aemter);

			//c. log successful login attempt
			$stmt = $libDb->prepare('INSERT INTO sys_log_intranet (mitglied, aktion, datum, punkte, ipadresse) VALUES (:mitglied, :aktion, NOW(), :punkte, :ipadresse)');
			$stmt->bindValue(':mitglied', $row['id'], PDO::PARAM_INT);
			$stmt->bindValue(':aktion', 1, PDO::PARAM_INT);
			$stmt->bindValue(':punkte', 0, PDO::PARAM_INT);
			$stmt->bindValue(':ipadresse', $_SERVER['REMOTE_ADDR']);
			$stmt->execute();

			$libPerson->setIntranetActivity($row['id'], 1, 1);

			return true;
		}

		//8. log mistaken login attempt
		$stmt = $libDb->prepare('INSERT INTO sys_log_intranet (mitglied, aktion, datum, punkte, ipadresse) VALUES (:mitglied, :aktion, NOW(), :punkte, :ipadresse)');
		$stmt->bindValue(':mitglied', $row['id'], PDO::PARAM_INT);
		$stmt->bindValue(':aktion', 2, PDO::PARAM_INT);
		$stmt->bindValue(':punkte', 0, PDO::PARAM_INT);
		$stmt->bindValue(':ipadresse', $_SERVER['REMOTE_ADDR']);
		$stmt->execute();

		//error message has to be imprecise
		$libGlobal->errorTexts[] = 'E-Mail-Adresse oder Passwort falsch.';
		return false;
	}

	function encryptPassword($password){
		$phpassHasher = new \phpass\PasswordHash(12, FALSE);
		return $phpassHasher->HashPassword($password);
	}

	function savePassword($personId, $newPassword, $quiet = false, $checkIsValidPassword = true){
		global $libGlobal, $libDb;

		//1. validation of person id
		if(!is_numeric($personId)){
			return false;
		}

		//2. validation of password
		$newPassword = trim($newPassword);

		//a. empty password
		if($newPassword == ''){
			if(!$quiet){
				$libGlobal->errorTexts[] = 'Das neue Passwort ist leer.';
			}

			return false;
		}

		//b. invalid password
		if($checkIsValidPassword){
			if(!$this->isValidPassword($newPassword)){
				if(!$quiet){
					$libGlobal->errorTexts[] = 'Das neue Passwort ist nicht komplex genug. '. $this->getPasswordRequirements();
				}

				return false;
			}
		}

		//3. generate hash from password
		$passwdHash = $this->encryptPassword($newPassword);

		//4. save hash
		$stmt = $libDb->prepare('UPDATE base_person SET password_hash = :password_hash WHERE id = :id');
		$stmt->bindValue(':password_hash', $passwdHash);
		$stmt->bindValue(':id', $personId, PDO::PARAM_INT);
		$stmt->execute();

		if(!$quiet){
			$libGlobal->notificationTexts[] = 'Das Passwort wurde gespeichert.';
		}

		return true;
	}

	function checkPassword($password, $storedHash){
		$password = trim($password);
		$storedHash = trim($storedHash);

		// check by BCrypt
		if($password != '' && $storedHash != ''){
			$phpassHasher = new \phpass\PasswordHash(12, FALSE);
			return $phpassHasher->CheckPassword($password, $storedHash);
		}

		return false;
	}

	function checkPasswordForPerson($personId, $password){
		global $libDb;

		if(!is_numeric($personId)){
			return false;
		}

		$stmt = $libDb->prepare('SELECT password_hash FROM base_person WHERE id = :id');
		$stmt->bindValue(':id', $personId, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		return $this->checkPassword($password, $row['password_hash']);
	}

	function isValidPassword($password){
		//min 1 Ziffer, min 1 Kleinbuchstabe, min 1 Großbuchstabe, kein Leerzeichen, min 10 Zeichen
		return preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).{10,}$/", trim($password));
	}

	function getPasswordRequirements(){
		return 'Das Passwort muss aus mindestens 10 Zeichen bestehen, mit mindestens einer Ziffer, mindestens einem Kleinbuchstaben und mindestens einem Großbuchstaben. Leerzeichen sind nicht erlaubt.';
	}

	//-------------------------------------------------------------------------

	function getId(){
		return $this->id;
	}

	function getAnrede(){
		return $this->anrede;
	}

	function getTitel(){
		return $this->titel;
	}

	function getVorname(){
		return $this->vorname;
	}

	function getPraefix(){
		return $this->praefix;
	}

	function getNachname(){
		return $this->nachname;
	}

	function getSuffix(){
		return $this->suffix;
	}

	function getGruppe(){
		return $this->gruppe;
	}

	function getAemter(){
		return $this->aemter;
	}

	function isLoggedin(){
		if($this->isLoggedIn && is_numeric($this->id) &&
				$this->id > 0 && $this->gruppe != '' &&
				in_array($this->gruppe, $this->possibleGruppen)){
			return true;
		} else {
			return false;
		}
	}
}
