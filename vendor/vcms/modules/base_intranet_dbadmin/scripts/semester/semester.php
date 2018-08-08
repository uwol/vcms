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

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();


if($libAuth->isLoggedin()){
	$aktion = '';

	if(isset($_REQUEST['aktion'])){
		$aktion = $_REQUEST['aktion'];
	}

	$semesterarray = array();

	//Felder in der Tabelle angeben -> Metadaten
	$vorstand = array('senior', 'sen_dech', 'consenior', 'con_dech', 'fuchsmajor', 'fm_dech', 'fuchsmajor2', 'fm2_dech', 'scriptor', 'scr_dech', 'quaestor', 'quaes_dech', 'jubelsenior', 'jubelsen_dech');
	$ahv = array('ahv_senior', 'ahv_consenior', 'ahv_keilbeauftragter', 'ahv_scriptor', 'ahv_quaestor', 'ahv_beisitzer1', 'ahv_beisitzer2');
	$hv = array('hv_vorsitzender', 'hv_kassierer', 'hv_beisitzer1', 'hv_beisitzer2');
	$warte = array(
		'archivar', 'ausflugswart',
		'bierwart', 'bootshauswart',
		'couleurartikelwart',
		'dachverbandsberichterstatter', 'datenpflegewart',
		'fechtwart', 'ferienordner', 'fotowart',
		'hauswart', 'huettenwart',
		'internetwart',
		'kuehlschrankwart',
		'musikwart',
		'redaktionswart',
		'sportwart', 'stammtischwart',
		'technikwart', 'thekenwart',
		'wichswart', 'wirtschaftskassenwart');
	$vorort = array('vop', 'vvop', 'vopxx', 'vopxxx', 'vopxxxx');
	$felder = array_merge(array('semester'), $vorstand, $ahv, $hv, $warte, $vorort);

	/**
	*
	* Verschiedene Aktionen auf der Datenbank durchführen, je nach Kontext
	* der durch aktion definiert wird
	*
	*/

	//neues Semester, leerer Datensatz
	if($aktion == 'blank'){
		$stmt = $libDb->prepare('SELECT * FROM base_semester ORDER BY SUBSTRING(semester,3) DESC LIMIT 0,1');
		$stmt->execute();
		$letztesSemester = $stmt->fetch(PDO::FETCH_ASSOC);

		$semesterarray['semester'] = $libTime->getFollowingSemesterName();

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
	}
	//Daten wurden mit blank eingegeben, werden nun gespeichert: INSERT
	elseif($aktion == 'insert'){
		if(!isset($_POST['form_complete']) || !$_POST['form_complete']){
			die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
		}

		if(!$libTime->isValidSemesterString($_REQUEST['semester'])){
			die('Das Format des Semesters '.$_REQUEST['semester'].' ist nicht korrekt. Erlaubt sind z. B. SS2015 oder WS20152016.');
		}

		$stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM base_semester WHERE semester=:semester');
		$stmt->bindValue(':semester', $_REQUEST['semester']);
		$stmt->execute();
		$stmt->bindColumn('number', $anzahl);
		$stmt->fetch();

		if($anzahl > 0){
			$libGlobal->errorTexts[] = 'Das Semester ist bereits vorhanden.';
			$semesterarray = $_REQUEST;
		} else {
			$semesterarray = $libDb->insertRow($felder, $_REQUEST, 'base_semester', array('semester' => $_REQUEST['semester']));
		}
	}
	//bestehende Mitgliedsdaten werden modifiziert: UPDATE
	elseif($aktion == 'update'){
		if(!isset($_POST['form_complete']) || !$_POST['form_complete']){
			die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
		}

		if(!$libTime->isValidSemesterString($_REQUEST['semester'])){
			die('Das Format des Semesters '.$_REQUEST['semester'].' ist nicht korrekt. Erlaubt sind z. B. SS2015 oder WS20152016.');
		}

		$stmt = $libDb->prepare('SELECT * FROM base_semester WHERE semester=:semester');
		$stmt->bindValue(':semester', $_REQUEST['semester']);
		$stmt->execute();
		$semesterarray = $stmt->fetch(PDO::FETCH_ASSOC);

		$semesterarray = $libDb->updateRow($felder,$_REQUEST, 'base_semester', array('semester' => $_REQUEST['semester']));
	}
	//keine Aktion
	else {
		$stmt = $libDb->prepare('SELECT * FROM base_semester WHERE semester=:semester');
		$stmt->bindValue(':semester', $_REQUEST['semester'], PDO::PARAM_INT);
		$stmt->execute();
		$semesterarray = $stmt->fetch(PDO::FETCH_ASSOC);
	}

	//Bildupload durchführen
	//wurde eine Datei hochgeladen?
	if(isset($_POST['formtyp']) && $_POST['formtyp'] == 'semestercoverupload'){
		if($semesterarray['semester'] != ''){
			$libImage->saveSemesterCoverByFilesArray($semesterarray['semester'], 'semestercover');
		}
	} elseif(isset($_GET['aktion']) && $_GET['aktion'] == 'semestercoverdelete'){
		if($semesterarray['semester'] != ''){
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

	echo '<p>Hier können sämtliche Daten eines Semesters bearbeitet werden. Diese Seite ist nur für den Internetwart zugänglich, weil über die Vergabe von Vorstands- und Wartsposten im Semester die Zugangsberechtigungen geregelt werden.</p>';
	echo '<hr />';

	/**
	*
	* Löschoption
	*
	*/
	if($semesterarray['semester'] != ''){
		echo '<p><a href="index.php?pid=intranet_admin_semesters&amp;aktion=delete&amp;semester=' .$semesterarray['semester']. '" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')"><i class="fa fa-trash" aria-hidden="true"></i> Datensatz löschen</a></p>';
	}

	echo '<div class="row">';
	echo '<div class="col-sm-9">';


	/**
	*
	* Ausgabe des Forms starten
	*
	*/
	if($aktion == 'blank'){
		$extraActionParam = "&amp;aktion=insert";
	} else {
		$extraActionParam = "&amp;aktion=update";
	}

	echo '<div class="panel panel-default">';
	echo '<div class="panel-body">';
	echo '<form action="index.php?pid=intranet_admin_semester' .$extraActionParam. '" method="post" class="form-horizontal">';
	echo '<fieldset>';
	echo '<input type="hidden" name="formtyp" value="semesterdaten" />';
	echo '<input type="hidden" name="semester" value="' .$semesterarray['semester']. '" />';

	$semesterDisabled = false;

	if($aktion != 'blank'){
		$semesterDisabled = true;
	}

	$libForm->printTextInput('semester', 'Semester', $semesterarray['semester'], 'text', $semesterDisabled);

	//Vorstand
	echo '<h2>Vorstand</h2>';
	$libForm->printMitgliederDropDownBox('senior', 'Senior', $semesterarray['senior']);
	$libForm->printBoolSelectBox('sen_dech', 'Senior Decharge', $semesterarray['sen_dech']);
	$libForm->printMitgliederDropDownBox('consenior', 'Consenior', $semesterarray['consenior']);
	$libForm->printBoolSelectBox('con_dech', 'Consenior Decharge', $semesterarray['con_dech']);
	$libForm->printMitgliederDropDownBox('fuchsmajor', 'Fuchsmajor', $semesterarray['fuchsmajor']);
	$libForm->printBoolSelectBox('fm_dech', 'Fuchsmajor Decharge', $semesterarray['fm_dech']);
	$libForm->printMitgliederDropDownBox('scriptor', 'Scriptor', $semesterarray['scriptor']);
	$libForm->printBoolSelectBox('scr_dech', 'Scriptor Decharge', $semesterarray['scr_dech']);
	$libForm->printMitgliederDropDownBox('quaestor', 'Quaestor', $semesterarray['quaestor']);
	$libForm->printBoolSelectBox('quaes_dech', 'Quaestor Decharge', $semesterarray['quaes_dech']);
	$libForm->printMitgliederDropDownBox('jubelsenior', 'Jubelsenior', $semesterarray['jubelsenior']);
	$libForm->printBoolSelectBox('jubelsen_dech', 'Jubelsenior Decharge', $semesterarray['jubelsen_dech']);
	$libForm->printMitgliederDropDownBox('fuchsmajor2', 'Fuchsmajor 2', $semesterarray['fuchsmajor2']);
	$libForm->printBoolSelectBox('fm2_dech', 'Fuchsmajor 2 Decharge', $semesterarray['fm2_dech']);

	echo '<h2>Philister-Vorstand</h2>';
	$libForm->printMitgliederDropDownBox('ahv_senior', 'AHV Senior', $semesterarray['ahv_senior']);
	$libForm->printMitgliederDropDownBox('ahv_consenior', 'AHV Consenior', $semesterarray['ahv_consenior']);
	$libForm->printMitgliederDropDownBox('ahv_keilbeauftragter', 'AHV Keilbeauftragter', $semesterarray['ahv_keilbeauftragter']);
	$libForm->printMitgliederDropDownBox('ahv_scriptor', 'AHV Scriptor', $semesterarray['ahv_scriptor']);
	$libForm->printMitgliederDropDownBox('ahv_quaestor', 'AHV Quaestor', $semesterarray['ahv_quaestor']);
	$libForm->printMitgliederDropDownBox('ahv_beisitzer1', 'AHV Beisitzer 1', $semesterarray['ahv_beisitzer1']);
	$libForm->printMitgliederDropDownBox('ahv_beisitzer2', 'AHV Beisitzer 2', $semesterarray['ahv_beisitzer2']);

	echo '<h2>Hausvereins-Vorstand</h2>';
	$libForm->printMitgliederDropDownBox('hv_vorsitzender', 'HV Vorsitzender', $semesterarray['hv_vorsitzender']);
	$libForm->printMitgliederDropDownBox('hv_kassierer', 'HV Kassierer', $semesterarray['hv_kassierer']);
	$libForm->printMitgliederDropDownBox('hv_beisitzer1', 'HV Beisitzer 1', $semesterarray['hv_beisitzer1']);
	$libForm->printMitgliederDropDownBox('hv_beisitzer2', 'HV Beisitzer 2', $semesterarray['hv_beisitzer2']);

	echo '<h2>Warte</h2>';
	$libForm->printMitgliederDropDownBox('archivar', 'Archivar', $semesterarray['archivar']);
	$libForm->printMitgliederDropDownBox('ausflugswart', 'Ausflugswart', $semesterarray['ausflugswart']);
	$libForm->printMitgliederDropDownBox('bierwart', 'Bierwart', $semesterarray['bierwart']);
	$libForm->printMitgliederDropDownBox('bootshauswart', 'Bootshauswart', $semesterarray['bootshauswart']);
	$libForm->printMitgliederDropDownBox('couleurartikelwart', 'Couleurartikelwart', $semesterarray['couleurartikelwart']);
	$libForm->printMitgliederDropDownBox('dachverbandsberichterstatter', 'Dachverbandsberichterstatter', $semesterarray['dachverbandsberichterstatter']);
	$libForm->printMitgliederDropDownBox('datenpflegewart', 'Datenpflegewart', $semesterarray['datenpflegewart']);
	$libForm->printMitgliederDropDownBox('fechtwart', 'Fechtwart', $semesterarray['fechtwart']);
	$libForm->printMitgliederDropDownBox('ferienordner', 'Ferienordner', $semesterarray['ferienordner']);
	$libForm->printMitgliederDropDownBox('fotowart', 'Fotowart', $semesterarray['fotowart']);
	$libForm->printMitgliederDropDownBox('hauswart', 'Hauswart', $semesterarray['hauswart']);
	$libForm->printMitgliederDropDownBox('huettenwart', 'Hüttenwart', $semesterarray['huettenwart']);
	$libForm->printMitgliederDropDownBox('internetwart', 'Internetwart', $semesterarray['internetwart']);
	$libForm->printMitgliederDropDownBox('kuehlschrankwart', 'Kühlschrankwart', $semesterarray['kuehlschrankwart']);
	$libForm->printMitgliederDropDownBox('musikwart', 'Musikwart', $semesterarray['musikwart']);
	$libForm->printMitgliederDropDownBox('redaktionswart', 'Redaktionswart', $semesterarray['redaktionswart']);
	$libForm->printMitgliederDropDownBox('sportwart', 'Sportwart', $semesterarray['sportwart']);
	$libForm->printMitgliederDropDownBox('stammtischwart', 'Stammtischwart', $semesterarray['stammtischwart']);
	$libForm->printMitgliederDropDownBox('technikwart', 'Technikwart', $semesterarray['technikwart']);
	$libForm->printMitgliederDropDownBox('thekenwart', 'Thekenwart', $semesterarray['thekenwart']);
	$libForm->printMitgliederDropDownBox('wirtschaftskassenwart', 'Wirtschaftskassenwart', $semesterarray['wirtschaftskassenwart']);
	$libForm->printMitgliederDropDownBox('wichswart', 'Wichswart', $semesterarray['wichswart']);

	$libForm->printMitgliederDropDownBox('vop', 'VOP', $semesterarray['vop']);
	$libForm->printMitgliederDropDownBox('vvop', 'VVOP', $semesterarray['vvop']);
	$libForm->printMitgliederDropDownBox('vopxx', 'VOPxx', $semesterarray['vopxx']);
	$libForm->printMitgliederDropDownBox('vopxxx', 'VOPxxx', $semesterarray['vopxxx']);
	$libForm->printMitgliederDropDownBox('vopxxxx', 'VOPxxxx', $semesterarray['vopxxxx']);

	echo '<input type="hidden" name="form_complete" value="1" />';

	$libForm->printSubmitButton('Speichern');

	echo '</fieldset>';
	echo '</form>';
	echo '</div>';
	echo '</div>';

	echo '</div>';
	echo '<div class="col-sm-3">';

	/**
	*
	* Fotoform einblenden
	*
	*/
	if($aktion != 'blank' && $semesterarray['semester'] != ''){
		echo '<div class="center-block">';
		echo '<div class="img-box">';

		$hasSemesterCover = $libTime->hasSemesterCover($semesterarray['semester']);

		if($hasSemesterCover){
			echo '<span class="delete-icon-box">';
			echo '<a href="index.php?pid=intranet_admin_semester&amp;semester=' .$semesterarray['semester']. '&amp;aktion=semestercoverdelete">';
			echo '<i class="fa fa-trash" aria-hidden="true"></i>';
			echo '</a>';
			echo '</span>';
		}

		echo $libTime->getSemesterCoverString($semesterarray['semester']);
		echo '</div>';
		echo '</div>';

		//image upload form
		echo '<form method="post" enctype="multipart/form-data" action="index.php?pid=intranet_admin_semester&amp;semester='. $semesterarray['semester'] .'" class="form-horizontal text-center">';
		echo '<input type="hidden" name="formtyp" value="semestercoverupload" />';
		$libForm->printFileUpload('semestercover', 'Semestercover hochladen', false, false, array(), array('image/jpeg'));
		echo '</form>';
	}

	echo '</div>';
	echo '</div>';
}
