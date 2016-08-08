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

class LibAssociation{

	function getFarbe($farbe){
		$farben['blau']          = '0000ff';
		$farben['dunkelblau']    = '000033';
		$farben['dunkelgrün']    = '003300';
		$farben['flieder']       = '336699';
		$farben['gelb']          = 'ffff00';
		$farben['gold']          = 'ffd700';
		$farben['grün']          = '009900';
		$farben['hellblau']      = '0066ff';
		$farben['hellgrün']      = '00ff00';
		$farben['hellrot']       = 'ff0000';
		$farben['himmelblau']    = '0066ff';
		$farben['moosgrün']      = '66ff66';
		$farben['orange']        = 'ff6600';
		$farben['purpur']        = '660066';
		$farben['rosa']          = 'ff99cc';
		$farben['rot']           = 'ff0000';
		$farben['schwarz']       = '000000';
		$farben['silber']        = 'C0C0C0';
		$farben['violett']       = 'B200CC';
		$farben['weinrot']       = '660000';
		$farben['weiß']          = 'FFFFFF';
		$farben['weiss']         = $farben['weiß'];
		$farben['zinnoberrot']   = 'cc0000';
		$farben['karmesinrot']   = '960018';
		$farben['grau']          = '808080';
		$farben['braun']         = 'A52A2A';
		$farben['saatgrün']      = '00FF00';
		$farben['kirschrot']     = 'FF0000';
		$farben['stahlblau']     = '30406A';
		$farben['purpur']        = 'FF0033';

		$farbe = strtolower($farbe);

		if($farben[$farbe] != ''){
			return '#'.$farben[$farbe];
		} else {
			return '#000000';
		}
	}

	function getGruendungString($date){
		$retstr = '';

		if($date != ''){
			if(substr($date, 8, 2) != '00' && substr($date, 5, 2) != '00'){ //day
				$retstr .= substr($date, 8, 2) .'.';
			}

			if(substr($date, 5, 2) != '00'){ //month
				$retstr .= substr($date, 5, 2) .'.';
			}

			if(substr($date, 0, 4) != '0000'){ //year
				$retstr .= substr($date, 0, 4);
			}
		}

		return $retstr;
	}

	function getVereinNameString($vereinid){
		global $libDb;

		$stmt = $libDb->prepare("SELECT titel, name FROM base_verein WHERE id = :id");
		$stmt->bindValue(':id', $vereinid, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		return $row['titel'] .' '. $row['name'];
	}

	function getToechterString($vereinid){
		global $libDb;

		$stmt = $libDb->prepare("SELECT tochter.id, tochter.titel, tochter.name FROM base_verein AS mutter, base_verein AS tochter WHERE mutter.id = tochter.mutterverein AND mutter.id = :id");
		$stmt->bindValue(':id', $vereinid, PDO::PARAM_INT);
		$stmt->execute();

		$retstr = '';

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($retstr != ''){
				$retstr .= ', ';
			}

			$retstr .= '<a href="index.php?pid=vereindetail&amp;verein=' .$row['id'] .'">' .$row['titel'] .' '. $row['name'] .'</a>';
		}

		return $retstr;
	}

	function getFusionertString($vereinid){
		global $libDb;

		$stmt = $libDb->prepare("SELECT fusionierend.id, fusionierend.titel, fusionierend.name FROM base_verein AS fusionierend, base_verein AS fusioniert WHERE fusioniert.id = fusionierend.fusioniertin AND fusioniert.id = :id");
		$stmt->bindValue(':id', $vereinid, PDO::PARAM_INT);
		$stmt->execute();

		$retstr = '';

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($retstr != ''){
				$retstr .= ', ';
			}

			$retstr .= '<a href="index.php?pid=vereindetail&amp;verein=' .$row['id'] .'">' .$row['titel'] .' '. $row['name'] .'</a>';
		}

		return $retstr;
	}

	function getAnsprechbarerAktivenVorstandIds(){
		global $libDb, $libTime;

		$aktuellermonat = @date('m');

		if($aktuellermonat == 2 || $aktuellermonat == 3 || $aktuellermonat == 8 || $aktuellermonat == 9){
			$vorstandssemester = $libTime->getFollowingSemesterName();
		} else {
			$vorstandssemester = $libTime->getSemesterName();
		}

		$stmt = $libDb->prepare("SELECT senior, jubelsenior, consenior, fuchsmajor, fuchsmajor2, scriptor, quaestor FROM base_semester WHERE semester = :semester");
		$stmt->bindValue(':semester', $vorstandssemester);
		$stmt->execute();

		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	function getValideInternetWarte(){
		global $libDb;

		// ein valider Internetwart
		// 1. muss als solcher mindestens einmal in einem Semester angegeben worden sein
		// 2. muss eine E-Mail-Adresse und einen Passwort-Hash haben
		// 3. darf nicht in der Gruppe T oder X (tot oder ausgetreten) sein

		$internetwarte = array();

		$stmt = $libDb->prepare('SELECT COUNT(*) AS anzahlsemester, base_person.id FROM base_person, base_semester WHERE base_semester.internetwart = base_person.id AND gruppe != "X" AND gruppe != "T" AND gruppe != "C" AND gruppe != "W" AND gruppe != "G" AND email IS NOT NULL AND email != "" AND password_hash IS NOT NULL AND password_hash != "" GROUP BY id');
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$internetwarte[$row['id']] = $row['anzahlsemester'];
		}

		return $internetwarte;
	}

	function importAssociations(){
		global $libGlobal, $libHttp;

		$mkHostname = $libGlobal->mkHostname;
		$path = '/inc.php?iid=dachverband_vereine_jsonfeed';
		$jsonUrl = 'https://' .$mkHostname.$path;
		$json = $libHttp->get($jsonUrl);

		if(empty($json)){
			$libGlobal->errorTexts[] = 'Die Vereinsdaten konnten nicht von ' .$jsonUrl. ' geladen werden.';
		} else {
			if(!is_array($json)){
				$json = json_decode($json, true);
			}

			if(!is_array($json)){
				$libGlobal->errorTexts[] = 'Die Vereinsdaten können nicht verarbeitet werden.';
			} else {
				$libGlobal->notificationTexts[] = 'Importiere ' .count($json). ' Vereins-Datensätze.';

				foreach($json as $association){
					if($this->isValidKvAssociation($association)){
						$this->importAssociation($association);
					}
				}
			}
		}
	}

	function isValidKvAssociation($association){
		$result = isset($association['id']) && is_numeric($association['id']) &&
				isset($association['name']) && !empty($association['name']) &&
				isset($association['dachverbandnr']) && is_numeric($association['dachverbandnr']);
		return $result;
	}

	function importAssociation($association){
		global $libDb, $libString;

		$stmt = $libDb->prepare("SELECT COUNT(*) as number FROM base_verein WHERE name = :name AND dachverbandnr = :dachverbandnr");
		$stmt->bindValue(':name', $association['name']);
		$stmt->bindValue(':dachverbandnr', $association['dachverbandnr'], PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('number', $number);
		$stmt->fetch();

		if($number > 0){
			$stmt = $libDb->prepare('UPDATE base_verein SET kuerzel=:kuerzel, aktivitas=:aktivitas, ahahschaft=:ahahschaft, titel=:titel, rang=:rang, dachverband=:dachverband, zusatz1=:zusatz1, strasse1=:strasse1, ort1=:ort1, plz1=:plz1, land1=:land1, telefon1=:telefon1, datum_gruendung=:datum_gruendung, webseite=:webseite, wahlspruch=:wahlspruch, farbenstrophe=:farbenstrophe, fuchsenstrophe=:fuchsenstrophe, bundeslied=:bundeslied, farbe1=:farbe1, farbe2=:farbe2, farbe3=:farbe3, farbe4=:farbe4 WHERE name=:name AND dachverbandnr=:dachverbandnr');

			$stmt->bindValue(':kuerzel', $libString->protectXss($association['kuerzel']));
			$stmt->bindValue(':aktivitas', $libString->protectXss($association['aktivitas']), PDO::PARAM_INT);
			$stmt->bindValue(':ahahschaft', $libString->protectXss($association['ahahschaft']), PDO::PARAM_INT);
			$stmt->bindValue(':titel', $libString->protectXss($association['titel']));
			$stmt->bindValue(':rang', $libString->protectXss($association['rang']));
			$stmt->bindValue(':dachverband', $libString->protectXss($association['dachverband']));
			$stmt->bindValue(':zusatz1', $libString->protectXss($association['zusatz1']));
			$stmt->bindValue(':strasse1', $libString->protectXss($association['strasse1']));
			$stmt->bindValue(':ort1', $libString->protectXss($association['ort1']));
			$stmt->bindValue(':plz1', $libString->protectXss($association['plz1']));
			$stmt->bindValue(':land1', $libString->protectXss($association['land1']));
			$stmt->bindValue(':telefon1', $libString->protectXss($association['telefon1']));
			$stmt->bindValue(':datum_gruendung', $libString->protectXss($association['datum_gruendung']));
			$stmt->bindValue(':webseite', $libString->protectXss($association['webseite']));
			$stmt->bindValue(':wahlspruch', $libString->protectXss($association['wahlspruch']));
			$stmt->bindValue(':farbenstrophe', $libString->protectXss($association['farbenstrophe']));
			$stmt->bindValue(':fuchsenstrophe', $libString->protectXss($association['fuchsenstrophe']));
			$stmt->bindValue(':bundeslied', $libString->protectXss($association['bundeslied']));
			$stmt->bindValue(':farbe1', $libString->protectXss($association['farbe1']));
			$stmt->bindValue(':farbe2', $libString->protectXss($association['farbe2']));
			$stmt->bindValue(':farbe3', $libString->protectXss($association['farbe3']));
			$stmt->bindValue(':farbe4', $libString->protectXss($association['farbe4']));

			$stmt->bindValue(':name', $association['name']);
			$stmt->bindValue(':dachverbandnr', $association['dachverbandnr'], PDO::PARAM_INT);
			$stmt->execute();
		} else {
			$stmt = $libDb->prepare('INSERT INTO base_verein (name, kuerzel, aktivitas, ahahschaft, titel, rang, dachverband, dachverbandnr, zusatz1, strasse1, ort1, plz1, land1, telefon1, datum_gruendung, webseite, wahlspruch, farbenstrophe, fuchsenstrophe, bundeslied, farbe1, farbe2, farbe3, farbe4) VALUES (:name, :kuerzel, :aktivitas, :ahahschaft, :titel, :rang, :dachverband, :dachverbandnr, :zusatz1, :strasse1, :ort1, :plz1, :land1, :telefon1, :datum_gruendung, :webseite, :wahlspruch, :farbenstrophe, :fuchsenstrophe, :bundeslied, :farbe1, :farbe2, :farbe3, :farbe4)');

			$stmt->bindValue(':name', $libString->protectXss($association['name']));
			$stmt->bindValue(':kuerzel', $libString->protectXss($association['kuerzel']));
			$stmt->bindValue(':aktivitas', $libString->protectXss($association['aktivitas']), PDO::PARAM_INT);
			$stmt->bindValue(':ahahschaft', $libString->protectXss($association['ahahschaft']), PDO::PARAM_INT);
			$stmt->bindValue(':titel', $libString->protectXss($association['titel']));
			$stmt->bindValue(':rang', $libString->protectXss($association['rang']));
			$stmt->bindValue(':dachverband', $libString->protectXss($association['dachverband']));
			$stmt->bindValue(':dachverbandnr', $libString->protectXss($association['dachverbandnr']), PDO::PARAM_INT);
			$stmt->bindValue(':zusatz1', $libString->protectXss($association['zusatz1']));
			$stmt->bindValue(':strasse1', $libString->protectXss($association['strasse1']));
			$stmt->bindValue(':ort1', $libString->protectXss($association['ort1']));
			$stmt->bindValue(':plz1', $libString->protectXss($association['plz1']));
			$stmt->bindValue(':land1', $libString->protectXss($association['land1']));
			$stmt->bindValue(':telefon1', $libString->protectXss($association['telefon1']));
			$stmt->bindValue(':datum_gruendung', $libString->protectXss($association['datum_gruendung']));
			$stmt->bindValue(':webseite', $libString->protectXss($association['webseite']));
			$stmt->bindValue(':wahlspruch', $libString->protectXss($association['wahlspruch']));
			$stmt->bindValue(':farbenstrophe', $libString->protectXss($association['farbenstrophe']));
			$stmt->bindValue(':fuchsenstrophe', $libString->protectXss($association['fuchsenstrophe']));
			$stmt->bindValue(':bundeslied', $libString->protectXss($association['bundeslied']));
			$stmt->bindValue(':farbe1', $libString->protectXss($association['farbe1']));
			$stmt->bindValue(':farbe2', $libString->protectXss($association['farbe2']));
			$stmt->bindValue(':farbe3', $libString->protectXss($association['farbe3']));
			$stmt->bindValue(':farbe4', $libString->protectXss($association['farbe4']));

			$stmt->execute();
		}
	}
}