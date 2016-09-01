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
	$felder = array('praefix', 'name', 'suffix', 'vorname', 'anrede', 'titel', 'rang', 'zusatz1', 'strasse1', 'plz1', 'ort1', 'land1', 'telefon1', 'status', 'grund', 'bemerkung');

	/**
	*
	* Verschiedene Aktionen auf der Datenbank durchführen, je nach Kontext
	* der durch aktion definiert wird
	*
	*/

	//neue, leerer Datensatz
	if($aktion == 'blank'){
		foreach($felder as $feld){
			$array[$feld] = '';
		}

		$array['id'] = '';
		$array['name'] = 'Namen angeben!';
		$array['datum_adresse1_stand'] = '';
	}
	//Daten wurden mit blank eingegeben, werden nun gespeichert
	elseif($aktion == 'insert'){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
			die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
		}

		$array = $libDb->insertRow($felder,$_REQUEST, 'base_vip', array('id' => ''));
		updateAdresseStand('base_vip', 'datum_adresse1_stand', $array['id']);
	}
	//bestehende Daten werden modifiziert
	elseif($aktion == 'update'){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
			die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
		}

		$stmt = $libDb->prepare('SELECT * FROM base_vip WHERE id=:id');
		$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
		$stmt->execute();
		$array = $stmt->fetch(PDO::FETCH_ASSOC);

		//Adressänderungen prüfen und vermerken im Stand
		if($_REQUEST['strasse1'] != $array['strasse1'] || $_REQUEST['ort1'] != $array['ort1'] || $_REQUEST['plz1'] != $array['plz1']){
			updateAdresseStand('base_vip', 'datum_adresse1_stand', $array['id']);
		}

		$array = $libDb->updateRow($felder,$_REQUEST, 'base_vip', array('id' => $id));
	} else {
		$stmt = $libDb->prepare('SELECT * FROM base_vip WHERE id=:id');
		$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
		$stmt->execute();
		$array = $stmt->fetch(PDO::FETCH_ASSOC);
	}


	/**
	*
	* Einleitender Text
	*
	*/
	echo '<h1>Vip</h1>';

	/**
	*
	* Löschoption
	*
	*/
	if($array['id'] != ''){
		echo '<p><a href="index.php?pid=intranet_admin_db_vipliste&amp;aktion=delete&amp;id='.$array['id'].'" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')"><i class="fa fa-trash" aria-hidden="true"></i> Datensatz löschen</a></p>';
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

	echo '<form action="index.php?pid=intranet_admin_db_vip' .$extraActionParam. '" method="post" class="form-horizontal">';
	echo '<fieldset>';
	echo '<input type="hidden" name="formtyp" value="vipdaten" />';
	echo '<input type="hidden" name="id" value="' .$array['id']. '" />';

	$libForm->printTextInput('id', 'Id', $array['id'], 'text', true);
	$libForm->printTextInput('praefix', 'Präfix', $array['praefix']);
	$libForm->printTextInput('name', 'Name', $array['name']);
	$libForm->printTextInput('suffix', 'Suffix', $array['suffix']);
	$libForm->printTextInput('vorname', 'Vorname', $array['vorname']);
	$libForm->printTextInput('anrede', 'Anrede', $array['anrede']);
	$libForm->printTextInput('titel', 'Titel', $array['titel']);
	$libForm->printTextInput('rang', 'Rang', $array['rang']);
	$libForm->printTextInput('zusatz1', 'Zusatz 1', $array['zusatz1']);
	$libForm->printTextInput('strasse1', 'Strasse 1', $array['strasse1']);
	$libForm->printTextInput('plz1', 'Plz 1', $array['plz1']);
	$libForm->printTextInput('ort1', 'Ort 1', $array['ort1']);
	$libForm->printTextInput('land1', 'Land 1', $array['land1']);
	$libForm->printTextInput('datum_adresse1_stand', 'Stand 1', $array['datum_adresse1_stand'], 'date', true);
	$libForm->printTextInput('telefon1', 'Telefon 1', $array['telefon1'], 'tel');
	$libForm->printTextInput('status', 'Status', $array['status']);
	$libForm->printTextInput('grund', 'Grund', $array['grund']);
	$libForm->printTextInput('bemerkung', 'Bemerkung', $array['bemerkung']);

	echo '<input type="hidden" name="formkomplettdargestellt" value="1" />';

	$libForm->printSubmitButton('Speichern');

	echo '</fieldset>';
	echo '</form>';
}

function updateAdresseStand($table, $field, $id){
	global $libDb;

	$stmt = $libDb->prepare('UPDATE ' .$table. ' SET ' .$field. '=NOW() WHERE id=:id');
	$stmt->bindValue(':id', $id, PDO::PARAM_INT);
	$stmt->execute();
}
