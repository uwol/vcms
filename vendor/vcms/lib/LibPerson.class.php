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

class LibPerson{

	function getNameString($id, $mode){
		global $libDb;

		$stmt = $libDb->prepare("SELECT anrede, titel, rang, vorname, praefix, name, suffix FROM base_person WHERE id=:id");
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$mitgliedarray = $stmt->fetch(PDO::FETCH_ASSOC);

		$mitgliedstring = $this->formatNameString($mitgliedarray['anrede'], $mitgliedarray['titel'], $mitgliedarray['rang'], $mitgliedarray['vorname'], $mitgliedarray['praefix'], $mitgliedarray['name'], $mitgliedarray['suffix'], $mode);

		return $mitgliedstring;
	}

	function formatNameString($anrede, $titel, $rang, $vorname, $praefix, $name, $suffix, $mode = 0){
		$string = '';

		if ($suffix != ''){
			$suffix = ' '.$suffix;
		} else {
			$suffix = '';
		}

		if($mode == 0){ //voller Name ohne Herr: Dr. Heinz van Husen LLM
			$string .= $titel. ' ' .$vorname. ' ' .$praefix. ' ' .$name.$suffix;
		} elseif($mode == 1){ //umgedreht: van Husen LLM, Dr. Heinz
			$string .= $praefix. ' ' .$name.$suffix. ', ' .$titel. ' ' .$vorname;
		} elseif($mode == 2){ //volle Anrede: Herr Dr. Professor Heinz van Husen LLM
			$string .= $anrede. ' ' .$titel. ' ' .$rang. ' ' .$vorname. ' ' .$praefix. ' ' .$name.$suffix;
		} elseif($mode == 3){ //Vorname: Heinz
			$string .= $vorname;
		} elseif($mode == 4){ //titulierter Name, aber nur mit dem ersten Vornamen
			$vornamen = explode(' ',$vorname);
			$erstervorname = $vornamen[0];
			$string .= $titel. ' ' .$erstervorname. ' ' .$praefix. ' ' .$name.$suffix;
		} elseif($mode == 5){ //Name ohne Herr und Titel: Heinz van Husen LLM
			$string .= $vorname. ' ' .$praefix. ' ' .$name.$suffix;
		} elseif($mode == 6){ //volle Anrede ohne Herr: Dr. Professor Heinz van Husen LLM
			$string .= $titel. ' ' .$rang. ' ' .$vorname. ' ' .$praefix. ' ' .$name.$suffix;
		} elseif($mode == 7){ //umgedreht ohne Titel: van Husen LLM, Heinz
			$string .= $praefix. ' ' .$name.$suffix. ', ' .$vorname;
		} elseif($mode == 8){ //abgekürzt: M. Meyer
			$string .= substr($vorname, 0, 1). '. ' .$name;
		}

		$string = str_replace('  ', ' ', str_replace('  ', ' ', trim($string)));

		return $string;
	}

	function getIntranetActivity($id){
		global $libDb;

		/*
		* define constants
		*/
		$durchschnittszeitraum = 14; //days
		$fullpercentlimit = 1; //points per day, that induce 100% activity

		/*
		* sum points
		*/
		$stmt = $libDb->prepare("SELECT SUM(punkte) AS summe FROM sys_log_intranet WHERE mitglied = :mitglied AND DATE_SUB(CURDATE(),INTERVAL :interval DAY) <= datum");
		$stmt->bindValue(':mitglied', $id, PDO::PARAM_INT);
		$stmt->bindValue(':interval', $durchschnittszeitraum, PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('summe', $fullpunkte);
		$stmt->fetch();

		/*
		* average points per day
		*/
		$avg_punkte_perday = $fullpunkte / $durchschnittszeitraum;

		/*
		* calculate activity metric
		*/
		$activity = min($avg_punkte_perday / $fullpercentlimit, 1);
		return $activity;
	}

	function getIntranetActivityBox($id){
		global $libDb;

		// determine group
		$stmt = $libDb->prepare("SELECT gruppe FROM base_person WHERE id=:id");
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('gruppe', $gruppe);
		$stmt->fetch();

		$retstr = '<div class="person-activity-box">';

		if($gruppe != 'T' && $gruppe != 'V'){
			$activityPercent = $this->getIntranetActivity($id) * 100;
			$balkenBreiteActivity = ceil($activityPercent);
			$balkenBreiteInactivity = 100 - $balkenBreiteActivity;

			if($balkenBreiteActivity > 0){
				$retstr .= '<span class="person-activity-bar person-activity-bar-active" style="width:' .$balkenBreiteActivity. '%"></span>';
			}

			if($balkenBreiteInactivity > 0){
				$retstr .= '<span class="person-activity-bar person-activity-bar-inactive" style="width:' .$balkenBreiteInactivity. '%"></span>';
			}
		} else {
			// required for correct height of cell in bootstrap row
			$retstr .= '<span class="person-activity-bar"></span>';
		}

		$retstr .= '</div>';

		return $retstr;
	}

	function getSignature($id){
		$retstr = '<div class="person-signature-box center-block media-object">';
		$retstr .= '<div class="img-box">';
		$retstr .= $this->getImage($id);
		$retstr .= '</div>';

		$retstr .= $this->getIntranetActivityBox($id);
		$retstr .= '</div>';

		return $retstr;
	}

	function getImage($id, $size = 'md'){
		$retstr = '<a href="index.php?pid=intranet_person&amp;id=' .$id. '" class="personProfileLink">';

		$classes = 'img-responsive';

		switch($size){
			case 'lg':
				$sizeClass = 'person-img-lg';
				break;
			case 'xs':
				$sizeClass = 'person-img-xs';
				break;
			default:
				$sizeClass = 'person-img-md';
				break;
		}

		$classes .= ' ' .$sizeClass;

		if($this->hasImageFile($id)){
			$retstr .= '<img src="api.php?iid=base_intranet_personenbild&amp;id=' . $id . '" class="' .$classes. '" alt="" />';
		} else {
			$retstr .= '<div class="' .$classes. '"></div>';
		}

		$retstr .= '</a>';

		return $retstr;
	}

	function hasImageFile($id){
		return is_numeric($id) && is_file($this->getImageFilePath($id));
	}

	function getImageFilePath($id){
		global $libFilesystem;

		return $libFilesystem->getAbsolutePath('custom/intranet/mitgliederfotos/' .$id. '.jpg');
	}

	function setIntranetActivity($id, $punkte, $enablelimit){
		global $libDb;

		if($enablelimit){
			$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM sys_log_intranet WHERE mitglied=:mitglied AND DATE_SUB(CURDATE(),INTERVAL 0 DAY) <= datum");
			$stmt->bindValue(':mitglied', $id, PDO::PARAM_INT);
			$stmt->execute();
			$stmt->bindColumn('number', $punkteadditionheute);
			$stmt->fetch();

			if($punkteadditionheute < 2){
				$stmt = $libDb->prepare("INSERT INTO sys_log_intranet (mitglied, datum, punkte) VALUES (:mitglied, NOW(), :punkte)");
				$stmt->bindValue(':mitglied', $id, PDO::PARAM_INT);
				$stmt->bindValue(':punkte', $punkte, PDO::PARAM_INT);
				$stmt->execute();
			}
		} else {
			$stmt = $libDb->prepare("INSERT INTO sys_log_intranet (mitglied, datum, punkte) VALUES (:mitglied, NOW(), :punkte)");
			$stmt->bindValue(':mitglied', $id, PDO::PARAM_INT);
			$stmt->bindValue(':punkte', $punkte, PDO::PARAM_INT);
			$stmt->execute();
		}
	}

	function hasBeenInternetWartAnyTime($mitgliedid){
		$anzahlSemester = $this->getAnzahlInternetWartsSemester($mitgliedid);
		return $anzahlSemester > 0;
	}

	function getAnzahlInternetWartsSemester($mitgliedid){
		global $libDb;

		// ein valider Internetwart
		// 1. muss als solcher mindestens einmal in einem Semester angegeben worden sein
		// 2. muss eine E-Mail-Adresse und einen Passwort-Hash haben
		// 3. darf nicht in der Gruppe T oder X (tot oder ausgetreten) sein

		$stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM base_semester WHERE internetwart=:internetwart');
		$stmt->bindValue(':internetwart', $mitgliedid, PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('number', $anzahlSemester);
		$stmt->fetch();

		return $anzahlSemester;
	}

	function couldBeValidInternetWart($mitgliedid){
		global $libDb;

		// ein valider Internetwart
		// 1. muss als solcher mindestens einmal in einem Semester angegeben worden sein
		// 2. muss eine E-Mail-Adresse und einen Passwort-Hash haben
		// 3. darf nicht in der Gruppe T oder X (tot oder ausgetreten) sein

		// hier wird geprüft, ob ein Mitglied Kondition 2 und 3 erfüllt, ob er also Internetwart werden darf
		$stmt = $libDb->prepare('SELECT email, password_hash, gruppe FROM base_person WHERE id = :id');
		$stmt->bindValue(':id', $mitgliedid, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		// hier wird geprüft, ob ein Mitglied Kondition 2 und 3 erfüllt, ob er also Internetwart werden darf
		return $row['email'] != '' && $row['password_hash'] != '' && $row['gruppe'] != 'X' && $row['gruppe'] != 'T';
	}

	function getChargenString($id){
		global $libDb, $libTime, $libConfig;

		/*
		* aktuelle Chargen, ist das Mitglied aktuell in einem Vorstand?
		*/
		$stmt = $libDb->prepare("
			SELECT *
			FROM base_semester
			WHERE ((base_semester.senior = :senior AND sen_dech = 0)
			OR (base_semester.jubelsenior = :jubelsenior AND jubelsen_dech = 0)
			OR (base_semester.consenior = :consenior AND con_dech = 0)
			OR (base_semester.fuchsmajor = :fuchsmajor AND fm_dech = 0)
			OR (base_semester.fuchsmajor2 = :fuchsmajor2 AND fm2_dech = 0)
			OR (base_semester.scriptor = :scriptor AND scr_dech = 0)
			OR (base_semester.quaestor = :quaestor AND quaes_dech = 0)
			OR base_semester.ahv_senior = :ahv_senior
			OR base_semester.ahv_consenior = :ahv_consenior
			OR base_semester.ahv_keilbeauftragter = :ahv_keilbeauftragter
			OR base_semester.ahv_scriptor = :ahv_scriptor
			OR base_semester.ahv_quaestor = :ahv_quaestor
			OR base_semester.hv_vorsitzender = :hv_vorsitzender
			OR base_semester.hv_kassierer = :hv_kassierer
			OR base_semester.archivar = :archivar
			OR base_semester.redaktionswart = :redaktionswart
			OR base_semester.vop = :vop
			OR base_semester.vvop = :vvop
			OR base_semester.vopxx = :vopxx
			OR base_semester.vopxxx = :vopxxx
			OR base_semester.vopxxxx = :vopxxxx)
			AND semester = :semester");
		$stmt->bindValue(':senior', $id, PDO::PARAM_INT);
		$stmt->bindValue(':jubelsenior', $id, PDO::PARAM_INT);
		$stmt->bindValue(':consenior', $id, PDO::PARAM_INT);
		$stmt->bindValue(':fuchsmajor', $id, PDO::PARAM_INT);
		$stmt->bindValue(':fuchsmajor2', $id, PDO::PARAM_INT);
		$stmt->bindValue(':scriptor', $id, PDO::PARAM_INT);
		$stmt->bindValue(':quaestor', $id, PDO::PARAM_INT);
		$stmt->bindValue(':ahv_senior', $id, PDO::PARAM_INT);
		$stmt->bindValue(':ahv_consenior', $id, PDO::PARAM_INT);
		$stmt->bindValue(':ahv_keilbeauftragter', $id, PDO::PARAM_INT);
		$stmt->bindValue(':ahv_scriptor', $id, PDO::PARAM_INT);
		$stmt->bindValue(':ahv_quaestor', $id, PDO::PARAM_INT);
		$stmt->bindValue(':hv_vorsitzender', $id, PDO::PARAM_INT);
		$stmt->bindValue(':hv_kassierer', $id, PDO::PARAM_INT);
		$stmt->bindValue(':archivar', $id, PDO::PARAM_INT);
		$stmt->bindValue(':redaktionswart', $id, PDO::PARAM_INT);
		$stmt->bindValue(':vop', $id, PDO::PARAM_INT);
		$stmt->bindValue(':vvop', $id, PDO::PARAM_INT);
		$stmt->bindValue(':vopxx', $id, PDO::PARAM_INT);
		$stmt->bindValue(':vopxxx', $id, PDO::PARAM_INT);
		$stmt->bindValue(':vopxxxx', $id, PDO::PARAM_INT);
		$stmt->bindValue(':semester', $libTime->getSemesterName());
		$stmt->execute();

		$chargenAktuell = array();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($row['senior'] == $id)
				$chargenAktuell[] = $libConfig->chargenSenior;
			if($row['jubelsenior'] == $id)
				$chargenAktuell[] = $libConfig->chargenJubelSenior;
			if($row['consenior'] == $id)
				$chargenAktuell[] = $libConfig->chargenConsenior;
			if($row['fuchsmajor'] == $id)
				$chargenAktuell[] = $libConfig->chargenFuchsmajor;
			if($row['fuchsmajor2'] == $id)
				$chargenAktuell[] = $libConfig->chargenFuchsmajor2;
			if($row['scriptor'] == $id)
				$chargenAktuell[] = $libConfig->chargenScriptor;
			if($row['quaestor'] == $id)
				$chargenAktuell[] = $libConfig->chargenQuaestor;
			if($row['ahv_senior'] == $id)
				$chargenAktuell[] = $libConfig->chargenAHVSenior;
			if($row['ahv_consenior'] == $id)
				$chargenAktuell[] = $libConfig->chargenAHVConsenior;
			if($row['ahv_keilbeauftragter'] == $id)
				$chargenAktuell[] = $libConfig->chargenAHVKeilbeauftragter;
			if($row['ahv_scriptor'] == $id)
				$chargenAktuell[] = $libConfig->chargenAHVScriptor;
			if($row['ahv_quaestor'] == $id)
				$chargenAktuell[] = $libConfig->chargenAHVQuaestor;
			if($row['hv_vorsitzender'] == $id)
				$chargenAktuell[] = $libConfig->chargenHVVorsitzender;
			if($row['hv_kassierer'] == $id)
				$chargenAktuell[] = $libConfig->chargenHVKassierer;
			if($row['archivar'] == $id)
				$chargenAktuell[] = $libConfig->chargenArchivar;
			if($row['redaktionswart'] == $id)
				$chargenAktuell[] = $libConfig->chargenRedaktionswart;
			if($row['vop'] == $id)
				if(isset($libConfig->chargenVOP))
					$chargenAktuell[] = $libConfig->chargenVOP;
			if($row['vvop'] == $id)
				if(isset($libConfig->chargenVVOP))
					$chargenAktuell[] = $libConfig->chargenVVOP;
			if($row['vopxx'] == $id)
				if(isset($libConfig->chargenVOPxx))
					$chargenAktuell[] = $libConfig->chargenVOPxx;
			if($row['vopxxx'] == $id)
				if(isset($libConfig->chargenVOPxxx))
					$chargenAktuell[] = $libConfig->chargenVOPxxx;
			if($row['vopxxxx'] == $id)
				if(isset($libConfig->chargenVOPxxxx))
					$chargenAktuell[] = $libConfig->chargenVOPxxxx;
		}

		$chargenAktuellNeu = array();

		foreach($chargenAktuell as $value){
			if($value){
				array_push($chargenAktuellNeu, $value);
			}
		}

		$chargenAktuellStr = implode(', ', $chargenAktuellNeu);

		/*
		* dechargierte Chargen
		*/
		$stmt = $libDb->prepare("
			SELECT *
			FROM base_semester
			WHERE (base_semester.senior = :senior AND sen_dech = 1)
			OR (base_semester.jubelsenior = :jubelsenior AND jubelsen_dech = 1)
			OR (base_semester.consenior = :consenior AND con_dech = 1)
			OR (base_semester.fuchsmajor = :fuchsmajor AND fm_dech = 1)
			OR (base_semester.fuchsmajor2 = :fuchsmajor2 AND fm2_dech = 1)
			OR (base_semester.scriptor = :scriptor AND scr_dech = 1)
			OR (base_semester.quaestor = :quaestor AND quaes_dech = 1)
			OR base_semester.ahv_senior = :ahv_senior
			OR base_semester.ahv_consenior = :ahv_consenior
			OR base_semester.ahv_keilbeauftragter = :ahv_keilbeauftragter
			OR base_semester.ahv_scriptor = :ahv_scriptor
			OR base_semester.ahv_quaestor = :ahv_quaestor
			OR base_semester.hv_vorsitzender = :hv_vorsitzender
			OR base_semester.hv_kassierer = :hv_kassierer
			OR base_semester.archivar = :archivar
			OR base_semester.redaktionswart = :redaktionswart
			OR base_semester.vop = :vop
			OR base_semester.vvop = :vvop
			OR base_semester.vopxx = :vopxx
			OR base_semester.vopxxx = :vopxxx
			OR base_semester.vopxxxx = :vopxxxx
			AND semester != :semester
			ORDER BY SUBSTRING(semester,3)");
		$stmt->bindValue(':senior', $id, PDO::PARAM_INT);
		$stmt->bindValue(':jubelsenior', $id, PDO::PARAM_INT);
		$stmt->bindValue(':consenior', $id, PDO::PARAM_INT);
		$stmt->bindValue(':fuchsmajor', $id, PDO::PARAM_INT);
		$stmt->bindValue(':fuchsmajor2', $id, PDO::PARAM_INT);
		$stmt->bindValue(':scriptor', $id, PDO::PARAM_INT);
		$stmt->bindValue(':quaestor', $id, PDO::PARAM_INT);
		$stmt->bindValue(':ahv_senior', $id, PDO::PARAM_INT);
		$stmt->bindValue(':ahv_consenior', $id, PDO::PARAM_INT);
		$stmt->bindValue(':ahv_keilbeauftragter', $id, PDO::PARAM_INT);
		$stmt->bindValue(':ahv_scriptor', $id, PDO::PARAM_INT);
		$stmt->bindValue(':ahv_quaestor', $id, PDO::PARAM_INT);
		$stmt->bindValue(':hv_vorsitzender', $id, PDO::PARAM_INT);
		$stmt->bindValue(':hv_kassierer', $id, PDO::PARAM_INT);
		$stmt->bindValue(':archivar', $id, PDO::PARAM_INT);
		$stmt->bindValue(':redaktionswart', $id, PDO::PARAM_INT);
		$stmt->bindValue(':vop', $id, PDO::PARAM_INT);
		$stmt->bindValue(':vvop', $id, PDO::PARAM_INT);
		$stmt->bindValue(':vopxx', $id, PDO::PARAM_INT);
		$stmt->bindValue(':vopxxx', $id, PDO::PARAM_INT);
		$stmt->bindValue(':vopxxxx', $id, PDO::PARAM_INT);
		$stmt->bindValue(':semester', $libTime->getSemesterName());
		$stmt->execute();

		$chargen = array();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($row['senior'] == $id)
				$chargen[] = $libConfig->chargenSenior;
			if($row['jubelsenior'] == $id)
				$chargen[] = $libConfig->chargenJubelSenior;
			if($row['consenior'] == $id)
				$chargen[] = $libConfig->chargenConsenior;
			if($row['fuchsmajor'] == $id)
				$chargen[] = $libConfig->chargenFuchsmajor;
			if($row['fuchsmajor2'] == $id)
				$chargen[] = $libConfig->chargenFuchsmajor2;
			if($row['scriptor'] == $id)
				$chargen[] = $libConfig->chargenScriptor;
			if($row['quaestor'] == $id)
				$chargen[] = $libConfig->chargenQuaestor;
			if($row['ahv_senior'] == $id)
				if(!in_array($libConfig->chargenAHVSenior, $chargen) &&
					!in_array($libConfig->chargenAHVSenior, $chargenAktuell))
					$chargen[] = $libConfig->chargenAHVSenior;
			if($row['ahv_consenior'] == $id)
				if(!in_array($libConfig->chargenAHVConsenior, $chargen) &&
					!in_array($libConfig->chargenAHVConsenior, $chargenAktuell))
					$chargen[] = $libConfig->chargenAHVConsenior;
			if($row['ahv_keilbeauftragter'] == $id)
				if(!in_array($libConfig->chargenAHVKeilbeauftragter, $chargen) &&
					!in_array($libConfig->chargenAHVKeilbeauftragter, $chargenAktuell))
					$chargen[] = $libConfig->chargenAHVKeilbeauftragter;
			if($row['ahv_scriptor'] == $id)
				if(!in_array($libConfig->chargenAHVScriptor, $chargen) &&
					!in_array($libConfig->chargenAHVScriptor, $chargenAktuell))
					$chargen[] = $libConfig->chargenAHVScriptor;
			if($row['ahv_quaestor'] == $id)
				if(!in_array($libConfig->chargenAHVQuaestor, $chargen) &&
					!in_array($libConfig->chargenAHVQuaestor, $chargenAktuell))
					$chargen[] = $libConfig->chargenAHVQuaestor;
			if($row['hv_vorsitzender'] == $id)
				if(!in_array($libConfig->chargenHVVorsitzender, $chargen) &&
					!in_array($libConfig->chargenHVVorsitzender, $chargenAktuell))
					$chargen[] = $libConfig->chargenHVVorsitzender;
			if($row['hv_kassierer'] == $id)
				if(!in_array($libConfig->chargenHVKassierer, $chargen) &&
					!in_array($libConfig->chargenHVKassierer, $chargenAktuell))
					$chargen[] = $libConfig->chargenHVKassierer;
			if($row['archivar'] == $id)
				if(!in_array($libConfig->chargenArchivar, $chargen) &&
					!in_array($libConfig->chargenArchivar, $chargenAktuell))
					$chargen[] = $libConfig->chargenArchivar;
			if($row['redaktionswart'] == $id)
				if(!in_array($libConfig->chargenRedaktionswart, $chargen) &&
					!in_array($libConfig->chargenRedaktionswart, $chargenAktuell))
					$chargen[] = $libConfig->chargenRedaktionswart;
			if($row['vop'] == $id)
				if(!in_array($libConfig->chargenVOP, $chargen) &&
					!in_array($libConfig->chargenVOP, $chargenAktuell))
						if(isset($libConfig->chargenVOP))
							$chargen[] = $libConfig->chargenVOP;
			if($row['vvop'] == $id)
				if(!in_array($libConfig->chargenVVOP, $chargen) &&
					!in_array($libConfig->chargenVVOP, $chargenAktuell))
						if(isset($libConfig->chargenVVOP))
							$chargen[] = $libConfig->chargenVVOP;
			if($row['vopxx'] == $id)
				if(!in_array($libConfig->chargenVOPxx, $chargen) &&
					!in_array($libConfig->chargenVOPxx, $chargenAktuell))
						if(isset($libConfig->chargenVOPxx))
							$chargen[] = $libConfig->chargenVOPxx;
			if($row['vopxxx'] == $id)
				if(!in_array($libConfig->chargenVOPxxx, $chargen) &&
					!in_array($libConfig->chargenVOPxxx, $chargenAktuell))
						if(isset($libConfig->chargenVOPxxx))
							$chargen[] = $libConfig->chargenVOPxxx;
			if($row['vopxxxx'] == $id)
				if(!in_array($libConfig->chargenVOPxxxx, $chargen) &&
					!in_array($libConfig->chargenVOPxxxx, $chargenAktuell))
						if(isset($libConfig->chargenVOPxxxx))
							$chargen[] = $libConfig->chargenVOPxxxx;
		}

		$chargenNeu = array();

		foreach($chargen as $value){
			if($value){
				array_push($chargenNeu, $value);
			}
		}

		$chargenDechStr = implode(', ', $chargenNeu);

		/*
		* result string
		*/
		$retstr = $chargenAktuellStr;

		if($chargenDechStr != ''){
			$retstr .= ' ('.$chargenDechStr.')';
		}

		return $retstr;
	}

	function getVereineString($id){
		global $libDb;

		$stmt = $libDb->prepare("SELECT base_verein.id, base_verein.kuerzel, base_verein_mitgliedschaft.ehrenmitglied
			FROM base_verein, base_verein_mitgliedschaft
			WHERE base_verein_mitgliedschaft.mitglied = :mitglied
			AND base_verein_mitgliedschaft.verein = base_verein.id");
		$stmt->bindValue(':mitglied', $id, PDO::PARAM_INT);
		$stmt->execute();

		$vereinestr = '';

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($vereinestr != ''){
				$vereinestr .= ', ';
			}

			$ehrenstring = '';

			if($row['ehrenmitglied'] == 1){
				$ehrenstring = 'E.d. ';
			}

			$vereinestr .= '<a href="index.php?pid=verein&amp;id=' .$row['id']. '">' .$ehrenstring.$row['kuerzel']. '</a>';
			unset($ehrenstring);
		}

		if($vereinestr != ''){
			return '('.$vereinestr.')';
		} else {
			return '';
		}
	}

	function getPersonSchema($row){
		global $libTime;

		$result = array();

		$result['@context'] = 'http://schema.org';
		$result['@type'] = 'Person';
		$result['honorificPrefix'] = $row['titel'];
		$result['givenName'] = $row['vorname'];
		$result['familyName'] = $row['name'];
		$result['jobTitle'] = $row['beruf'];
		$result['email'] = $row['email'];
		$result['telephone'] = $row['mobiltelefon'];
		$result['url'] = $row['webseite'];

		if($row['datum_geburtstag'] != ''){
			$result['birthDate'] = $libTime->formatDateString($row['datum_geburtstag']);
		}

		if($row['tod_datum'] != ''){
			$result['deathDate'] = $libTime->formatDateString($row['tod_datum']);
		}

		$address1 = array();
		$address1['@type'] = 'PostalAddress';
		$address1['streetAddress'] = $row['strasse1'];
		$address1['addressLocality'] = $row['ort1'];
		$address1['postalCode'] = $row['plz1'];
		$address1['addressCountry'] = $row['land1'];
		$address1['telephone'] = $row['telefon1'];

		$address2 = array();
		$address2['@type'] = 'PostalAddress';
		$address2['streetAddress'] = $row['strasse2'];
		$address2['addressLocality'] = $row['ort2'];
		$address2['postalCode'] = $row['plz2'];
		$address2['addressCountry'] = $row['land2'];
		$address2['telephone'] = $row['telefon2'];

		$result['contactPoint'] = array($address1, $address2);

		return $result;
	}
}
