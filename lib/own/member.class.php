<?php
/*
This file is part of VCMS.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

class LibMember{
	var $libTime;
	var $libDb;
	var $libConfig;

	function __construct(LibTime $libTime, LibDb $libDb, LibConfig $libConfig){
		$this->libTime = $libTime;
		$this->libDb = $libDb;
		$this->libConfig = $libConfig;
	}

	function getMitgliedNameString($id, $mode){
		$stmt = $this->libDb->prepare("SELECT anrede, titel, rang, vorname, praefix, name, suffix FROM base_person WHERE id=:id");
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$mitgliedarray = $stmt->fetch(PDO::FETCH_ASSOC);

		$mitgliedstring = $this->formatMitgliedNameString($mitgliedarray['anrede'], $mitgliedarray['titel'], $mitgliedarray['rang'], $mitgliedarray['vorname'], $mitgliedarray['praefix'], $mitgliedarray['name'], $mitgliedarray['suffix'], $mode);

		return $mitgliedstring;
	}

	function formatMitgliedNameString($anrede, $titel, $rang, $vorname, $praefix, $name, $suffix, $mode = 0){
		$string = '';

		if ($suffix != ''){
			$suffix = ' '.$suffix;
		} else {
			$suffix = '';
		}

		if($mode == 0){ //voller Name ohne Herr: Dr. Heinz van Husen LLM
			$string .= $titel.' '.$vorname.' '.$praefix.' '.$name.$suffix;
		} elseif($mode == 1){ //umgedreht: van Husen LLM, Dr. Heinz
			$string .= $praefix.' '.$name.$suffix.', '.$titel.' '.$vorname;
		} elseif($mode == 2){ //volle Anrede: Herr Dr. Professor Heinz van Husen LLM
			$string .= $anrede.' '.$titel.' '.$rang.' '.$vorname.' '.$praefix.' '.$name.$suffix;
		} elseif($mode == 3){ //Vorname: Heinz
			$string .= $vorname;
		} elseif($mode == 4){ //titulierter Name, aber nur mit dem ersten Vornamen
			$vornamen = explode(' ',$vorname);
			$erstervorname = $vornamen[0];
			$string .= $titel.' '.$erstervorname.' '.$praefix.' '.$name.$suffix;
		} elseif($mode == 5){ //Name ohne Herr und Titel: Heinz van Husen LLM
			$string .= $vorname.' '.$praefix.' '.$name.$suffix;
		} elseif($mode == 6){ //volle Anrede ohne Herr: Dr. Professor Heinz van Husen LLM
			$string .= $titel.' '.$rang.' '.$vorname.' '.$praefix.' '.$name.$suffix;
		} elseif($mode == 7){ //umgedreht ohne Titel: van Husen LLM, Heinz
			$string .= $praefix.' '.$name.$suffix.', '.$vorname;
		} elseif($mode == 8){ //abgekürzt: M. Meyer
			$string .= substr($vorname, 0, 1).'. '.$name;
		}

		$string = str_replace('  ', ' ', str_replace('  ', ' ', trim($string)));

		return $string;
	}

	function getMitgliedIntranetActivity($id){
		/*
		* define constants
		*/
		$durchschnittszeitraum = 14; //days
		$fullpercentlimit = 1; //points per day, that induce 100% activity

		/*
		* sum points
		*/
		$stmt = $this->libDb->prepare("SELECT SUM(punkte) AS summe FROM sys_log_intranet WHERE mitglied = :mitglied AND DATE_SUB(CURDATE(),INTERVAL :interval DAY) <= datum");
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

	function getMitgliedIntranetActivityBox($id){
		// determine group
		$stmt = $this->libDb->prepare("SELECT gruppe FROM base_person WHERE id=:id");
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('gruppe', $gruppe);
		$stmt->fetch();

		$retstr = '<div class="personActivityBox">';

		if($gruppe != 'T' && $gruppe != 'V'){
			$activityPercent = $this->getMitgliedIntranetActivity($id) * 100;
			$balkenBreiteActivity = ceil($activityPercent);
			$balkenBreiteInactivity = 100 - $balkenBreiteActivity;

			if($balkenBreiteActivity > 0){
				$retstr .= '<span class="personActivityBar personActivityBarActive" style="width:' .$balkenBreiteActivity. '%"></span>';
			}

			if($balkenBreiteInactivity > 0){
				$retstr .= '<span class="personActivityBar personActivityBarInactive" style="width:' .$balkenBreiteInactivity. '%"></span>';
			}
		} else {
			// required for correct height of cell in bootstrap row
			$retstr .= '<span class="personActivityBar"></span>';
		}

		$retstr .= '</div>';

		return $retstr;
	}

	function getMitgliedSignature($id){
		$retstr = '<div class="personSignatureBox center-block media-object">';
		$retstr .= '<div class="imgBox">';
		$retstr .= $this->getMitgliedImage($id);
		$retstr .= '</div>';

		$retstr .= $this->getMitgliedIntranetActivityBox($id);
		$retstr .= '</div>';

		return $retstr;
	}

	function getMitgliedImage($id, $large = false){
		$retstr = '<a href="index.php?pid=intranet_person_daten&amp;personid=' .$id. '">';

		/*
		* member image
		*/
		if(is_numeric($id)){
			$retstr .= '<img src="inc.php?iid=base_intranet_personenbild&amp;id=' . $id . '" class="img-responsive personImg';

			if($large){
				$retstr .= ' personImgLarge ';
			}

			$retstr .= '" alt="" />';
		}

		$retstr .= '</a>';

		return $retstr;
	}

	function setMitgliedIntranetActivity($id, $punkte, $enablelimit){
		if($enablelimit){
			$stmt = $this->libDb->prepare("SELECT COUNT(*) AS number FROM sys_log_intranet WHERE mitglied=:mitglied AND DATE_SUB(CURDATE(),INTERVAL 0 DAY) <= datum");
			$stmt->bindValue(':mitglied', $id, PDO::PARAM_INT);
			$stmt->execute();
			$stmt->bindColumn('number', $punkteadditionheute);
			$stmt->fetch();

			if($punkteadditionheute < 2){
				$stmt = $this->libDb->prepare("INSERT INTO sys_log_intranet (mitglied, datum, punkte) VALUES (:mitglied, NOW(), :punkte)");
				$stmt->bindValue(':mitglied', $id, PDO::PARAM_INT);
				$stmt->bindValue(':punkte', $punkte, PDO::PARAM_INT);
				$stmt->execute();
			}
		} else {
			$stmt = $this->libDb->prepare("INSERT INTO sys_log_intranet (mitglied, datum, punkte) VALUES (:mitglied, NOW(), :punkte)");
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
		// ein valider Internetwart
		// 1. muss als solcher mindestens einmal in einem Semester angegeben worden sein
		// 2. muss einen Benutzernamen, Passworthash und Passwortsalt haben
		// 3. darf nicht in der Gruppe T oder X (tot oder ausgetreten) sein

		$stmt = $this->libDb->prepare('SELECT COUNT(*) AS number FROM base_semester WHERE internetwart=:internetwart');
		$stmt->bindValue(':internetwart', $mitgliedid, PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('number', $anzahlSemester);
		$stmt->fetch();

		return $anzahlSemester;
	}

	function couldBeValidInternetWart($mitgliedid){
		// ein valider Internetwart
		// 1. muss als solcher mindestens einmal in einem Semester angegeben worden sein
		// 2. muss einen Benutzernamen, Passworthash und Passwortsalt haben
		// 3. darf nicht in der Gruppe T oder X (tot oder ausgetreten) sein

		// hier wird geprüft, ob ein Mitglied Kondition 2 und 3 erfüllt, ob er also Internetwart werden darf
		$stmt = $this->libDb->prepare('SELECT username, password_hash, gruppe FROM base_person WHERE id = :id');
		$stmt->bindValue(':id', $mitgliedid, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		// hier wird geprüft, ob ein Mitglied Kondition 2 und 3 erfüllt, ob er also Internetwart werden darf
		return $row['username'] != '' && $row['password_hash'] != '' && $row['gruppe'] != 'X' && $row['gruppe'] != 'T';
	}

	function getChargenString($id){
		/*
		* aktuelle Chargen, ist das Mitglied aktuell in einem Vorstand?
		*/
		$stmt = $this->libDb->prepare("
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
		$stmt->bindValue(':semester', $this->libTime->getSemesterName());
		$stmt->execute();

		$chargenAktuell = array();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($row['senior'] == $id)
				$chargenAktuell[] = $this->libConfig->chargenSenior;
			if($row['jubelsenior'] == $id)
				$chargenAktuell[] = $this->libConfig->chargenJubelSenior;
			if($row['consenior'] == $id)
				$chargenAktuell[] = $this->libConfig->chargenConsenior;
			if($row['fuchsmajor'] == $id)
				$chargenAktuell[] = $this->libConfig->chargenFuchsmajor;
			if($row['fuchsmajor2'] == $id)
				$chargenAktuell[] = $this->libConfig->chargenFuchsmajor2;
			if($row['scriptor'] == $id)
				$chargenAktuell[] = $this->libConfig->chargenScriptor;
			if($row['quaestor'] == $id)
				$chargenAktuell[] = $this->libConfig->chargenQuaestor;
			if($row['ahv_senior'] == $id)
				$chargenAktuell[] = $this->libConfig->chargenAHVSenior;
			if($row['ahv_consenior'] == $id)
				$chargenAktuell[] = $this->libConfig->chargenAHVConsenior;
			if($row['ahv_keilbeauftragter'] == $id)
				$chargenAktuell[] = $this->libConfig->chargenAHVKeilbeauftragter;
			if($row['ahv_scriptor'] == $id)
				$chargenAktuell[] = $this->libConfig->chargenAHVScriptor;
			if($row['ahv_quaestor'] == $id)
				$chargenAktuell[] = $this->libConfig->chargenAHVQuaestor;
			if($row['hv_vorsitzender'] == $id)
				$chargenAktuell[] = $this->libConfig->chargenHVVorsitzender;
			if($row['hv_kassierer'] == $id)
				$chargenAktuell[] = $this->libConfig->chargenHVKassierer;
			if($row['archivar'] == $id)
				$chargenAktuell[] = $this->libConfig->chargenArchivar;
			if($row['redaktionswart'] == $id)
				$chargenAktuell[] = $this->libConfig->chargenRedaktionswart;
			if($row['vop'] == $id)
				if(isset($this->libConfig->chargenVOP))
					$chargenAktuell[] = $this->libConfig->chargenVOP;
			if($row['vvop'] == $id)
				if(isset($this->libConfig->chargenVVOP))
					$chargenAktuell[] = $this->libConfig->chargenVVOP;
			if($row['vopxx'] == $id)
				if(isset($this->libConfig->chargenVOPxx))
					$chargenAktuell[] = $this->libConfig->chargenVOPxx;
			if($row['vopxxx'] == $id)
				if(isset($this->libConfig->chargenVOPxxx))
					$chargenAktuell[] = $this->libConfig->chargenVOPxxx;
			if($row['vopxxxx'] == $id)
				if(isset($this->libConfig->chargenVOPxxxx))
					$chargenAktuell[] = $this->libConfig->chargenVOPxxxx;
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
		$stmt = $this->libDb->prepare("
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
		$stmt->bindValue(':semester', $this->libTime->getSemesterName());
		$stmt->execute();

		$chargen = array();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($row['senior'] == $id)
				$chargen[] = $this->libConfig->chargenSenior;
			if($row['jubelsenior'] == $id)
				$chargen[] = $this->libConfig->chargenJubelSenior;
			if($row['consenior'] == $id)
				$chargen[] = $this->libConfig->chargenConsenior;
			if($row['fuchsmajor'] == $id)
				$chargen[] = $this->libConfig->chargenFuchsmajor;
			if($row['fuchsmajor2'] == $id)
				$chargen[] = $this->libConfig->chargenFuchsmajor2;
			if($row['scriptor'] == $id)
				$chargen[] = $this->libConfig->chargenScriptor;
			if($row['quaestor'] == $id)
				$chargen[] = $this->libConfig->chargenQuaestor;
			if($row['ahv_senior'] == $id)
				if(!in_array($this->libConfig->chargenAHVSenior, $chargen) &&
					!in_array($this->libConfig->chargenAHVSenior, $chargenAktuell))
					$chargen[] = $this->libConfig->chargenAHVSenior;
			if($row['ahv_consenior'] == $id)
				if(!in_array($this->libConfig->chargenAHVConsenior, $chargen) &&
					!in_array($this->libConfig->chargenAHVConsenior, $chargenAktuell))
					$chargen[] = $this->libConfig->chargenAHVConsenior;
			if($row['ahv_keilbeauftragter'] == $id)
				if(!in_array($this->libConfig->chargenAHVKeilbeauftragter, $chargen) &&
					!in_array($this->libConfig->chargenAHVKeilbeauftragter, $chargenAktuell))
					$chargen[] = $this->libConfig->chargenAHVKeilbeauftragter;
			if($row['ahv_scriptor'] == $id)
				if(!in_array($this->libConfig->chargenAHVScriptor, $chargen) &&
					!in_array($this->libConfig->chargenAHVScriptor, $chargenAktuell))
					$chargen[] = $this->libConfig->chargenAHVScriptor;
			if($row['ahv_quaestor'] == $id)
				if(!in_array($this->libConfig->chargenAHVQuaestor, $chargen) &&
					!in_array($this->libConfig->chargenAHVQuaestor, $chargenAktuell))
					$chargen[] = $this->libConfig->chargenAHVQuaestor;
			if($row['hv_vorsitzender'] == $id)
				if(!in_array($this->libConfig->chargenHVVorsitzender, $chargen) &&
					!in_array($this->libConfig->chargenHVVorsitzender, $chargenAktuell))
					$chargen[] = $this->libConfig->chargenHVVorsitzender;
			if($row['hv_kassierer'] == $id)
				if(!in_array($this->libConfig->chargenHVKassierer, $chargen) &&
					!in_array($this->libConfig->chargenHVKassierer, $chargenAktuell))
					$chargen[] = $this->libConfig->chargenHVKassierer;
			if($row['archivar'] == $id)
				if(!in_array($this->libConfig->chargenArchivar, $chargen) &&
					!in_array($this->libConfig->chargenArchivar, $chargenAktuell))
					$chargen[] = $this->libConfig->chargenArchivar;
			if($row['redaktionswart'] == $id)
				if(!in_array($this->libConfig->chargenRedaktionswart, $chargen) &&
					!in_array($this->libConfig->chargenRedaktionswart, $chargenAktuell))
					$chargen[] = $this->libConfig->chargenRedaktionswart;
			if($row['vop'] == $id)
				if(!in_array($this->libConfig->chargenVOP, $chargen) &&
					!in_array($this->libConfig->chargenVOP, $chargenAktuell))
						if(isset($this->libConfig->chargenVOP))
							$chargen[] = $this->libConfig->chargenVOP;
			if($row['vvop'] == $id)
				if(!in_array($this->libConfig->chargenVVOP, $chargen) &&
					!in_array($this->libConfig->chargenVVOP, $chargenAktuell))
						if(isset($this->libConfig->chargenVVOP))
							$chargen[] = $this->libConfig->chargenVVOP;
			if($row['vopxx'] == $id)
				if(!in_array($this->libConfig->chargenVOPxx, $chargen) &&
					!in_array($this->libConfig->chargenVOPxx, $chargenAktuell))
						if(isset($this->libConfig->chargenVOPxx))
							$chargen[] = $this->libConfig->chargenVOPxx;
			if($row['vopxxx'] == $id)
				if(!in_array($this->libConfig->chargenVOPxxx, $chargen) &&
					!in_array($this->libConfig->chargenVOPxxx, $chargenAktuell))
						if(isset($this->libConfig->chargenVOPxxx))
							$chargen[] = $this->libConfig->chargenVOPxxx;
			if($row['vopxxxx'] == $id)
				if(!in_array($this->libConfig->chargenVOPxxxx, $chargen) &&
					!in_array($this->libConfig->chargenVOPxxxx, $chargenAktuell))
						if(isset($this->libConfig->chargenVOPxxxx))
							$chargen[] = $this->libConfig->chargenVOPxxxx;
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
		$stmt = $this->libDb->prepare("SELECT base_verein.id, base_verein.kuerzel, base_verein_mitgliedschaft.ehrenmitglied
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

			$vereinestr .= '<a href="index.php?pid=vereindetail&amp;verein=' .$row['id'] .'">' .$ehrenstring.$row['kuerzel'] .'</a>';
			unset($ehrenstring);
		}

		if($vereinestr != ''){
			return '('.$vereinestr.')';
		} else {
			return '';
		}
	}
}
?>