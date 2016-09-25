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
	$id = '';
	if(isset($_REQUEST['id'])){
		$id = $_REQUEST['id'];
	}

	$aktion = '';
	if(isset($_REQUEST['aktion'])){
		$aktion = $_REQUEST['aktion'];
	}

	$array = array();
	//Felder in der Tabelle angeben -> Metadaten
	$felder = array('name', 'kuerzel', 'aktivitas', 'ahahschaft', 'titel', 'rang', 'dachverband', 'dachverbandnr', 'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1', 'telefon1', 'anschreiben_zusenden', 'mutterverein', 'fusioniertin', 'datum_gruendung', 'webseite', 'wahlspruch', 'farbenstrophe', 'farbenstrophe_inoffiziell', 'fuchsenstrophe', 'bundeslied', 'farbe1', 'farbe2', 'farbe3', 'farbe4', 'beschreibung');

	/**
	*
	* Verschiedene Aktionen auf der Datenbank durchführen, je nach Kontext
	* der durch aktion definiert wird
	*
	*/

	//neuer Verein, leerer Datensatz
	if($aktion == 'blank'){
		foreach($felder as $feld){
			$array[$feld] = '';
		}

		$array['id'] = '';
		$array['name'] = 'Namen angeben!';
		$array['aktivitas'] = 1;
		$array['ahahschaft'] = 1;
		$array['anschreiben_zusenden'] = 1;
		$array['datum_adresse1_stand'] = '';
	}
	//Daten wurden mit blank eingegeben, werden nun gespeichert
	elseif($aktion == 'insert'){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt'])
			die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');

		$valueArray = $_REQUEST;
		$valueArray['datum_gruendung'] = $libTime->assureMysqlDate($valueArray['datum_gruendung']);
		$array = $libDb->insertRow($felder, $valueArray, 'base_verein', array('id' => ''));
		updateAdresseStand('base_verein', 'datum_adresse1_stand', $array['id']);
	}
	//bestehende Daten werden modifiziert
	elseif($aktion == 'update'){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
			die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
		}

		$stmt = $libDb->prepare('SELECT * FROM base_verein WHERE id=:id');
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$array = $stmt->fetch(PDO::FETCH_ASSOC);

		//Adressänderungen prüfen und vermerken im Stand
		if($_REQUEST['strasse1'] != $array['strasse1'] || $_REQUEST['ort1'] != $array['ort1'] || $_REQUEST['plz1'] != $array['plz1']){
			updateAdresseStand('base_verein', 'datum_adresse1_stand', $array['id']);
		}

		$valueArray = $_REQUEST;
		$valueArray['datum_gruendung'] = $libTime->assureMysqlDate($valueArray['datum_gruendung']);
		$array = $libDb->updateRow($felder, $valueArray, 'base_verein', array('id' => $id));
	} else {
		$stmt = $libDb->prepare('SELECT * FROM base_verein WHERE id=:id');
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$array = $stmt->fetch(PDO::FETCH_ASSOC);
	}



	/**
	*
	* Einleitender Text
	*
	*/

	echo '<h1>Verein</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	/**
	*
	* Löschoption
	*
	*/
	if($array['id'] != ''){
		echo '<p><a href="index.php?pid=intranet_admin_associations&amp;aktion=delete&amp;id='.$array['id'].'" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')"><i class="fa fa-trash" aria-hidden="true"></i> Datensatz löschen</a></p>';
	}

	/**
	*
	* Ausgabe des Forms starten
	*
	*/
	if($aktion == 'blank'){
		$extraActionParam = '&amp;aktion=insert';
	} else {
		$extraActionParam = '&amp;aktion=update';
	}

	echo '<form action="index.php?pid=intranet_admin_association' .$extraActionParam. '" method="post" class="form-horizontal">';
	echo '<fieldset>';
	echo '<input type="hidden" name="formtyp" value="vereinsdaten" />';
	echo '<input type="hidden" name="id" value="' .$array['id']. '" />';

	$libForm->printTextInput('id', 'Id', $array['id'], 'text', true);
	$libForm->printTextInput('name', 'Name', $array['name']);
	$libForm->printTextInput('kuerzel', 'Kürzel', $array['kuerzel']);

	$libForm->printBoolSelectBox('aktivitas', 'Aktivitas', $array['aktivitas']);
	$libForm->printBoolSelectBox('ahahschaft', 'Altherrenschaft', $array['ahahschaft']);

	$libForm->printTextInput('titel', 'Titel', $array['titel']);
	$libForm->printTextInput('rang', 'Rang', $array['rang']);
	$libForm->printTextInput('dachverband', 'Dachverband', $array['dachverband']);
	$libForm->printTextInput('dachverbandnr', 'Dachverbandnummer', $array['dachverbandnr']);
	$libForm->printTextInput('zusatz1', 'Zusatz', $array['zusatz1']);
	$libForm->printTextInput('strasse1', 'Strasse', $array['strasse1']);
	$libForm->printTextInput('ort1', 'Ort', $array['ort1']);
	$libForm->printTextInput('plz1', 'Plz', $array['plz1']);
	$libForm->printTextInput('land1', 'Land', $array['land1']);
	$libForm->printTextInput('datum_adresse1_stand', 'Stand', $array['datum_adresse1_stand'], 'date', true);
	$libForm->printTextInput('telefon1', 'Telefon 1', $array['telefon1']);

	$libForm->printBoolSelectBox('anschreiben_zusenden', 'Anschreiben zusenden', $array['anschreiben_zusenden']);
	$libForm->printVereineDropDownBox('mutterverein', 'Mutterverein', $array['mutterverein']);
	$libForm->printVereineDropDownBox('fusioniertin', 'Fusioniert in', $array['fusioniertin']);

	$libForm->printTextInput('datum_gruendung', 'Gründungsdatum', $array['datum_gruendung'], 'date');
	$libForm->printTextInput('webseite', 'Webseite', $array['webseite']);
	$libForm->printTextInput('wahlspruch', 'Wahlspruch', $array['wahlspruch']);
	$libForm->printTextarea('farbenstrophe', 'Farbenstrophe', $array['farbenstrophe']);
	$libForm->printTextarea('farbenstrophe_inoffiziell', 'inoffizielle Farbenstrophe', $array['farbenstrophe_inoffiziell']);
	$libForm->printTextarea('fuchsenstrophe', 'Fuchsenstrophe', $array['fuchsenstrophe']);
	$libForm->printTextarea('bundeslied', 'Bundeslied', $array['bundeslied']);

	$libForm->printTextInput('farbe1', 'Farbe 1', $array['farbe1']);
	$libForm->printTextInput('farbe2', 'Farbe 2', $array['farbe2']);
	$libForm->printTextInput('farbe3', 'Farbe 3', $array['farbe3']);
	$libForm->printTextInput('farbe4', 'Farbe 4', $array['farbe4']);

	$libForm->printTextarea('beschreibung', 'Beschreibung', $array['beschreibung']);

	echo '<input type="hidden" name="formkomplettdargestellt" value="1" />';

	$libForm->printSubmitButton('Speichern');

	echo '</fielset>';
	echo '</form>';
}

function updateAdresseStand($table, $field, $id){
	global $libDb;

	$stmt = $libDb->prepare('UPDATE ' .$table. ' SET ' .$field. '=NOW() WHERE id=:id');
	$stmt->bindValue(':id', $id, PDO::PARAM_INT);
	$stmt->execute();
}
