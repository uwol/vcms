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

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();

if($libAuth->isLoggedin()){
	$libForm = new LibForm();

	$aktion = '';
	if(isset($_REQUEST['aktion'])){
		$aktion = $_REQUEST['aktion'];
	}

	$semesterarray = array();

	//Felder in der Tabelle angeben -> Metadaten
	$vorstand = array("senior", "sen_dech", "consenior", "con_dech", "fuchsmajor", "fm_dech", "fuchsmajor2", "fm2_dech", "scriptor", "scr_dech", "quaestor", "quaes_dech", "jubelsenior", "jubelsen_dech");
	$ahv = array("ahv_senior", "ahv_consenior", "ahv_keilbeauftragter", "ahv_scriptor", "ahv_quaestor", "ahv_beisitzer1", "ahv_beisitzer2");
	$hv = array("hv_vorsitzender", "hv_kassierer", "hv_beisitzer1", "hv_beisitzer2");
	$warte = array("archivar", "redaktionswart", "hauswart", "bierwart", "kuehlschrankwart", "thekenwart", "technikwart", "fotowart", "wirtschaftskassenwart", "wichswart", "bootshauswart", "huettenwart", "fechtwart", "stammtischwart", "musikwart", "ausflugswart", "sportwart", "couleurartikelwart", "ferienordner", "dachverbandsberichterstatter"); //hier darf der Internetwart nicht enthalten sein!
	$vorort = array("vop", "vvop", "vopxx", "vopxxx", "vopxxxx");
	$sensiblefelder = array("internetwart");
	$felder = array_merge(array("semester"), $vorstand, $ahv, $hv, $warte, $vorort);

	//falls der Benutzer ein Internetwart ist
	if(in_array("internetwart", $libAuth->getAemter())){
		//dann auch die sensiblen Felder bearbeiten
		$felder = array_merge($felder, $sensiblefelder);
	}

	/**
	*
	* Verschiedene Aktionen auf der Datenbank durchführen, je nach Kontext
	* der durch aktion definiert wird
	*
	*/

	//neues Semester, leerer Datensatz
	if($aktion == "blank"){
		$stmt = $libDb->prepare("SELECT * FROM base_semester ORDER BY SUBSTRING(semester,3) DESC LIMIT 0,1");
		$stmt->execute();
		$letztesSemester = $stmt->fetch(PDO::FETCH_ASSOC);

		$semesterarray['semester'] = $libTime->getNaechstesSemester();

		foreach($vorstand as $amt){
			$semesterarray[$amt] = '';
		}

		foreach($vorort as $amt){
			$semesterarray[$amt] = '';
		}

		//Daten vom letzten Semester rüberkopieren
		foreach($warte as $amt){
			$semesterarray[$amt] = $letztesSemester[$amt];
		}

		foreach($ahv as $amt){
			$semesterarray[$amt] = $letztesSemester[$amt];
		}

		foreach($hv as $amt){
			$semesterarray[$amt] = $letztesSemester[$amt];
		}

		foreach($sensiblefelder as $amt){
			$semesterarray[$amt] = $letztesSemester[$amt];
		}
	}
	//Daten wurden mit blank eingegeben, werden nun gespeichert: INSERT
	elseif($aktion == "insert"){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
			die("Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.");
		}

		if(!$libTime->isValidSemesterString($_REQUEST['semester'])){
			die("Das Format des Semesters ".$_REQUEST['semester']." ist nicht korrekt. Erlaubt sind z. B. SS2015 oder WS20152016.");
		}

		$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_semester WHERE semester=:semester");
		$stmt->bindValue(':semester', $_REQUEST['semester']);
		$stmt->execute();
		$stmt->bindColumn('number', $anzahl);
		$stmt->fetch();

		if($anzahl > 0){
			$libGlobal->errorTexts[] = "Das Semester ist bereits vorhanden.";
			$semesterarray = $_REQUEST;
		} else {
			$semesterarray = $libDb->insertRow($felder, $_REQUEST, "base_semester", array("semester" => $_REQUEST['semester']));
		}
	}
	//bestehende Mitgliedsdaten werden modifiziert: UPDATE
	elseif($aktion == "update"){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
			die("Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.");
		}

		if(!$libTime->isValidSemesterString($_REQUEST['semester'])){
			die("Das Format des Semesters ".$_REQUEST['semester']." ist nicht korrekt. Erlaubt sind z. B. SS2015 oder WS20152016.");
		}

		$stmt = $libDb->prepare("SELECT * FROM base_semester WHERE semester=:semester");
		$stmt->bindValue(':semester', $_REQUEST['semester']);
		$stmt->execute();
		$semesterarray = $stmt->fetch(PDO::FETCH_ASSOC);

		//war bisher in diesem Semester ein Internetwart eingetragen?
		if($semesterarray['internetwart'] != ""){
			//soll der Internetwart modifiziert werden
			$internetwartWirdModifiziert = false;
			if(in_array("internetwart", $felder) && $_REQUEST['internetwart'] != $semesterarray['internetwart']){
				$internetwartWirdModifiziert = true;
			}

			//soll der bisherige Internetwart modifiziert werden?
			if($internetwartWirdModifiziert){
				//ist das bisherige Mitglied auf dem Intranetwartsposten nur in diesem einem Semester Internetwart?
				if($libMitglied->getAnzahlInternetWartsSemester($semesterarray['internetwart']) < 2){
					//ist das Mitglied auf dem Intranetwartsposten ein valider Intranetwart?
					if($libMitglied->couldBeValidInternetWart($semesterarray['internetwart'])){
						//ist der neue Internetwart nicht valid? (kein Login, ausgetreten tot)
						if(!$libMitglied->couldBeValidInternetWart($_REQUEST['internetwart'])){
							//dann ist das Löschen evtl ein Problem, wenn nämlich damit der letzte valide Internetwart ausgetragen wird
							$valideInternetWarte = $libVerein->getValideInternetWarte();

							//ist dies der letzte valide Internetwart?
							if(count($valideInternetWarte) < 2){
								//STOPP, DRAMA ahead, dann gibt es keinen validen Intranetwart mehr
								$dieText = "Fataler Fehler: Der bisherige Intranetwart ist der einzige valide, wenn er gelöscht wird, so gibt es keinen validen Intranetwart mehr! ";

								if($_REQUEST['internetwart'] != ""){
									$dieText .= "Das ausgewählte Mitglied ist kein valides, es hat entweder keine Logindaten oder ist tot, ausgetreten etc.";
								}

								die($dieText);
							}
						}
					}
				}
			}
		}

		$semesterarray = $libDb->updateRow($felder,$_REQUEST, "base_semester", array("semester" => $_REQUEST['semester']));
	}
	//keine Aktion
	else {
		$stmt = $libDb->prepare("SELECT * FROM base_semester WHERE semester=:semester");
		$stmt->bindValue(':semester', $_REQUEST['semester'], PDO::PARAM_INT);
		$stmt->execute();
		$semesterarray = $stmt->fetch(PDO::FETCH_ASSOC);
	}

	//Bildupload durchführen
	//wurde eine Datei hochgeladen?
	if(isset($_POST['formtyp']) && $_POST['formtyp'] == "semestercoverupload"){
		if($semesterarray['semester'] != ""){
			$libImage = new LibImage($libTime, $libGenericStorage);
			$libImage->saveSemesterCoverByFilesArray($semesterarray['semester'], "semestercover");
		}
	} elseif(isset($_POST['formtyp']) && $_POST['formtyp'] == "semestercoverdelete"){
		if($semesterarray['semester'] != ""){
			$libImage = new LibImage($libTime, $libGenericStorage);
			$libImage->deleteSemesterCover($semesterarray['semester']);
		}
	}




	/**
	*
	* Einleitender Text
	*
	*/
	echo '<h1>Semester</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo '<p>Hier können sämtliche Daten eines Semesters bearbeitet werden. Diese Seite ist nur für den Internetwart zugänglich, weil über die Vergabe von Vorstands- und Wartsposten im Semester die Zugangsberechtigungen geregelt werden. Wenn der Vorstand Semesterdaten ändern dürfte, könnte er seine eigenen Zugangsrechte erweitern.</p>';

	/**
	*
	* Löschoption
	*
	*/
	if($semesterarray['semester'] != ''){
		echo '<p><a href="index.php?pid=intranet_admin_db_semesterliste&amp;aktion=delete&amp;semester='.$semesterarray['semester'].'" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">Datensatz löschen</a></p>';
	}

	/**
	*
	* Fotoform einblenden
	*
	*/
	if($aktion != "blank"){
		echo '<h2>Semestercover hochladen</h2>';

		if($semesterarray['semester'] != ""){
			echo $libTime->getSemesterCoverString($semesterarray['semester']);
		}

		//Fotouploadform
		echo '<form method="post" enctype="multipart/form-data" action="index.php?pid=intranet_admin_db_semester&amp;semester='. $semesterarray['semester'] .'">';
		echo '<input type="hidden" name="formtyp" value="semestercoverupload" />';
		echo '<input name="semestercover" type="file"><br />';
		echo '<input type="submit" value="Semestercover hochladen"></form>';

		//Fotolöschform
		echo '<form method="post" action="index.php?pid=intranet_admin_db_semester&amp;semester='. $semesterarray['semester'] .'">';
		echo '<input type="hidden" name="formtyp" value="semestercoverdelete" />';
		echo '<input type="submit" value="Semestercover löschen"></form>';
	}

	/**
	*
	* Ausgabe des Forms starten
	*
	*/
	echo '<h2>Semesterdaten</h2>';

	if($aktion == "blank"){
		$extraActionParam = "&amp;aktion=insert";
	} else {
		$extraActionParam = "&amp;aktion=update";
	}

	echo '<form action="index.php?pid=intranet_admin_db_semester' .$extraActionParam. '" method="post">';
	echo '<input type="submit" value="Speichern" name="Save"><br />';
	echo '<input type="hidden" name="formtyp" value="semesterdaten" />';
	echo '<input type="hidden" name="semester" value="' .$semesterarray['semester']. '" />';
	echo '<input size="10" type="text" name="semester" value="' .$semesterarray['semester']. '" ';

	if($aktion != "blank"){
		echo "disabled";
	}

	echo ' /> Semester<br />';

	//Vorstand
	echo '<h3>Vorstand</h3>';
	echo $libForm->getMitgliederDropDownBox("senior", "Senior", $semesterarray['senior']);
	echo $libForm->getBoolSelectBox("sen_dech", "Senior Decharge", $semesterarray['sen_dech']);
	echo $libForm->getMitgliederDropDownBox("consenior", "Consenior", $semesterarray['consenior']);
	echo $libForm->getBoolSelectBox("con_dech", "Consenior Decharge", $semesterarray['con_dech']);
	echo $libForm->getMitgliederDropDownBox("fuchsmajor", "Fuchsmajor", $semesterarray['fuchsmajor']);
	echo $libForm->getBoolSelectBox("fm_dech", "Fuchsmajor Decharge", $semesterarray['fm_dech']);
	echo $libForm->getMitgliederDropDownBox("scriptor", "Scriptor", $semesterarray['scriptor']);
	echo $libForm->getBoolSelectBox("scr_dech", "Scriptor Decharge", $semesterarray['scr_dech']);
	echo $libForm->getMitgliederDropDownBox("quaestor", "Quaestor", $semesterarray['quaestor']);
	echo $libForm->getBoolSelectBox("quaes_dech", "Quaestor Decharge", $semesterarray['quaes_dech']);
	echo $libForm->getMitgliederDropDownBox("jubelsenior", "Jubelsenior", $semesterarray['jubelsenior']);
	echo $libForm->getBoolSelectBox("jubelsen_dech", "Jubelsenior Decharge", $semesterarray['jubelsen_dech']);
	echo $libForm->getMitgliederDropDownBox("fuchsmajor2", "Fuchsmajor 2", $semesterarray['fuchsmajor2']);
	echo $libForm->getBoolSelectBox("fm2_dech", "Fuchsmajor 2 Decharge", $semesterarray['fm2_dech']);
	echo '<h3>Philister-Vorstand</h3>';
	echo $libForm->getMitgliederDropDownBox("ahv_senior", "AHV Senior", $semesterarray['ahv_senior']);
	echo $libForm->getMitgliederDropDownBox("ahv_consenior", "AHV Consenior", $semesterarray['ahv_consenior']);
	echo $libForm->getMitgliederDropDownBox("ahv_keilbeauftragter", "AHV Keilbeauftragter", $semesterarray['ahv_keilbeauftragter']);
	echo $libForm->getMitgliederDropDownBox("ahv_scriptor", "AHV Scriptor", $semesterarray['ahv_scriptor']);
	echo $libForm->getMitgliederDropDownBox("ahv_quaestor", "AHV Quaestor", $semesterarray['ahv_quaestor']);
	echo $libForm->getMitgliederDropDownBox("ahv_beisitzer1", "AHV Beisitzer 1", $semesterarray['ahv_beisitzer1']);
	echo $libForm->getMitgliederDropDownBox("ahv_beisitzer2", "AHV Beisitzer 2", $semesterarray['ahv_beisitzer2']);
	echo '<h3>Hausvereins-Vorstand</h3>';
	echo $libForm->getMitgliederDropDownBox("hv_vorsitzender", "HV Vorsitzender", $semesterarray['hv_vorsitzender']);
	echo $libForm->getMitgliederDropDownBox("hv_kassierer", "HV Kassierer", $semesterarray['hv_kassierer']);
	echo $libForm->getMitgliederDropDownBox("hv_beisitzer1", "HV Beisitzer 1", $semesterarray['hv_beisitzer1']);
	echo $libForm->getMitgliederDropDownBox("hv_beisitzer2", "HV Beisitzer 2", $semesterarray['hv_beisitzer2']);
	echo '<h3>Warte</h3>';
	echo $libForm->getMitgliederDropDownBox("archivar", "Archivar", $semesterarray['archivar']);
	echo $libForm->getMitgliederDropDownBox("redaktionswart", "Redaktionswart", $semesterarray['redaktionswart']);
	echo $libForm->getMitgliederDropDownBox("hauswart", "Hauswart", $semesterarray['hauswart']);
	echo $libForm->getMitgliederDropDownBox("bierwart", "Bierwart", $semesterarray['bierwart']);
	echo $libForm->getMitgliederDropDownBox("kuehlschrankwart", "Kühlschrankwart", $semesterarray['kuehlschrankwart']);
	echo $libForm->getMitgliederDropDownBox("thekenwart", "Thekenwart", $semesterarray['thekenwart']);

	//Falls der bearbeitende Benutzer ein Internetwart ist, darf er Internetwarte angeben
	if(in_array("internetwart", $libAuth->getAemter())){
		//wird nicht ein Semester neu angelegt sondern ein bestehendes verwaltet?
		if($aktion != "blank"){
			//ist das Mitglied auf dem Intranetwartsposten nur in diesem einem Semester Internetwart?
			if($semesterarray['internetwart'] != "" && $semesterarray['internetwart'] != 0 && $libMitglied->getAnzahlInternetWartsSemester($semesterarray['internetwart']) < 2){
				//dann ist das Löschen evtl ein Problem, wenn nämlich damit der letzte Internetwart ausgetragen wird
				echo '<p><b>!!! ACHTUNG !!!</b></p>';
				echo 'Dies ist die einzige Eintragung als Intranetwart für das folgende Mitglied. Wenn dieser Eintrag entfernt wird, so ist das Mitglied kein Intranetwart mehr! Die folgenden Internetwarte haben Intranetzugang und sind nicht tot oder ausgetreten: ';
				$valideInternetWarte = $libVerein->getValideInternetWarte();

				foreach($valideInternetWarte as $key => $value){
					echo $libMitglied->getMitgliedNameString($key, 5). ", ";
				}

				echo '<br />';
				echo 'Bitte stelle sicher, dass die anderen Intranetwarte ansprechbar sind und sie ihre Einwahldaten für das Intranet kennen, bevor Du diesen Intranetwart austrägst und evtl. einen anderen einträgst.<br />';
			}
		}

		echo $libForm->getMitgliederDropDownBox("internetwart", "Internetwart", $semesterarray['internetwart']);
	}

	echo $libForm->getMitgliederDropDownBox("technikwart", "Technikwart", $semesterarray['technikwart']);
	echo $libForm->getMitgliederDropDownBox("fotowart", "Fotowart", $semesterarray['fotowart']);
	echo $libForm->getMitgliederDropDownBox("wirtschaftskassenwart", "Wirtschaftskassenwart", $semesterarray['wirtschaftskassenwart']);
	echo $libForm->getMitgliederDropDownBox("wichswart", "Wichswart", $semesterarray['wichswart']);
	echo $libForm->getMitgliederDropDownBox("bootshauswart", "Bootshauswart", $semesterarray['bootshauswart']);
	echo $libForm->getMitgliederDropDownBox("huettenwart", "Hüttenwart", $semesterarray['huettenwart']);
	echo $libForm->getMitgliederDropDownBox("fechtwart", "Fechtwart", $semesterarray['fechtwart']);
	echo $libForm->getMitgliederDropDownBox("stammtischwart", "Stammtischwart", $semesterarray['stammtischwart']);
	echo $libForm->getMitgliederDropDownBox("musikwart", "Musikwart", $semesterarray['musikwart']);
	echo $libForm->getMitgliederDropDownBox("ausflugswart", "Ausflugswart", $semesterarray['ausflugswart']);
	echo $libForm->getMitgliederDropDownBox("sportwart", "Sportwart", $semesterarray['sportwart']);
	echo $libForm->getMitgliederDropDownBox("couleurartikelwart", "Couleurartikelwart", $semesterarray['couleurartikelwart']);
	echo $libForm->getMitgliederDropDownBox("ferienordner", "Ferienordner", $semesterarray['ferienordner']);
	echo $libForm->getMitgliederDropDownBox("dachverbandsberichterstatter", "Dachverbandsberichterstatter", $semesterarray['dachverbandsberichterstatter']);
	echo $libForm->getMitgliederDropDownBox("vop", "VOP", $semesterarray['vop']);
	echo $libForm->getMitgliederDropDownBox("vvop", "VVOP", $semesterarray['vvop']);
	echo $libForm->getMitgliederDropDownBox("vopxx", "VOPxx", $semesterarray['vopxx']);
	echo $libForm->getMitgliederDropDownBox("vopxxx", "VOPxxx", $semesterarray['vopxxx']);
	echo $libForm->getMitgliederDropDownBox("vopxxxx", "VOPxxxx", $semesterarray['vopxxxx']);

	echo '<input type="hidden" name="formkomplettdargestellt" value="1" />';
	echo '<input type="submit" value="Speichern" name="Save"><br />';
	echo "</form>";
}
?>