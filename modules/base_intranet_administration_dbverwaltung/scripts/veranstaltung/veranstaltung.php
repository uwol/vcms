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

	$id = '';
	if(isset($_REQUEST['id'])){
		$id = $_REQUEST['id'];
	}

	$aktion = '';
	if(isset($_REQUEST['aktion'])){
		$aktion = $_REQUEST['aktion'];
	}

	$varray = array();
	//Felder in der Tabelle angeben -> Metadaten
	$felder = array("titel","datum","datum_ende","spruch","beschreibung","status","ort");

	/**
	*
	* Verschiedene Aktionen auf der Datenbank durchführen, je nach Kontext
	* der durch aktion definiert wird
	*
	*/

	//neue Veranstaltung, leerer Datensatz
	if($aktion == "blank"){
		$varray['id'] = '';
		$varray['datum'] = @date("Y-m-d H:i:s");
		$varray['datum_ende'] = '';
		$varray['titel'] = "Titel angeben!";
		$varray['spruch'] = '';
		$varray['beschreibung'] = '';
		$varray['status'] = '';
		$varray['ort'] = '';
	}
	//Daten wurden mit blank eingegeben, werden nun gespeichert
	elseif($aktion == "insert"){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
			die("Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.");
		}

		$valueArray = $_REQUEST;
		$valueArray['datum'] = $libTime->assureMysqlDateTime($valueArray['datum']);
		$valueArray['datum_ende'] = $libTime->assureMysqlDateTime($valueArray['datum_ende']);

		if($valueArray['datum_ende'] != '0000-00-00 00:00:00' &&
				$valueArray['datum_ende'] != '' &&
				$valueArray['datum_ende'] < $valueArray['datum']){
			$valueArray['datum_ende'] = '';
			$libGlobal->errorTexts[] = 'Das Enddatum liegt vor dem Startdatum.';
		}

		$varray = $libDb->insertRow($felder, $valueArray, "base_veranstaltung", array("id"=>''));
	}
	//bestehende Daten werden modifiziert
	elseif($aktion == "update"){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt'])
			die("Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.");

		$valueArray = $_REQUEST;
		$valueArray['datum'] = $libTime->assureMysqlDateTime($valueArray['datum']);
		$valueArray['datum_ende'] = $libTime->assureMysqlDateTime($valueArray['datum_ende']);

		if($valueArray['datum_ende'] != '0000-00-00 00:00:00' &&
				$valueArray['datum_ende'] != '' &&
				$valueArray['datum_ende'] < $valueArray['datum']){
			$valueArray['datum_ende'] = '';
			$libGlobal->errorTexts[] = 'Das Enddatum liegt vor dem Startdatum.';
		}

		$varray = $libDb->updateRow($felder, $valueArray, "base_veranstaltung", array("id" => $id));
	} else {
		$stmt = $libDb->prepare("SELECT * FROM base_veranstaltung WHERE id=:id");
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$varray = $stmt->fetch(PDO::FETCH_ASSOC);
	}



	/**
	*
	* Einleitender Text
	*
	*/

	echo '<h1>Veranstaltung</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo '<p>Hier können sämtliche Daten einer Veranstaltung bearbeitet werden.</p>';

	/**
	*
	* Löschoption
	*
	*/
	if($varray['id'] != ''){
		echo '<p><a href="index.php?pid=intranet_admin_db_veranstaltungsliste&amp;aktion=delete&amp;id='.$varray['id'].'" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">Datensatz löschen</a></p>';
	}

	/**
	*
	* Ausgabe des Forms starten
	*
	*/

	if($aktion == "blank"){
		$extraActionParam = "&amp;aktion=insert";
	} else {
		$extraActionParam = "&amp;aktion=update";
	}

	echo '<form action="index.php?pid=intranet_admin_db_veranstaltung' .$extraActionParam. '" method="post">';
	echo '<input type="submit" value="Speichern" name="Save"><br />';
	echo '<input type="hidden" name="formtyp" value="veranstaltungsdaten" />';
	echo '<input type="hidden" name="id" value="' .$varray['id']. '" />';
	echo '<input size="20" type="text" name="id" value="' .$varray['id']. '" disabled /> Id<br />';
	echo '<input size="20" type="text" name="datum" value="' .$varray['datum']. '" /> Startdatum (falls ganztägig: Uhrzeit 00:00:00)<br />';
	echo '<input size="20" type="text" name="datum_ende" value="' .$varray['datum_ende']. '" /> Enddatum (optional; falls ganztägig: Uhrzeit 00:00:00)<br />';
	echo '<input size="60" type="text" name="titel" value="' .$varray['titel']. '" /> Titel<br />';
	echo '<input size="60" type="text" name="spruch" value="' .$varray['spruch']. '" /> Spruch<br />';
	echo 'Beschreibung<br /><textarea name="beschreibung" cols="70" rows="10">' . $varray['beschreibung'] .'</textarea><br />';
	echo '<input size="2" type="text" name="status" value="' .$varray['status']. '" /> Status (Maximal 2 Buchstaben, z. B. ho oder o)<br />';
	echo '<input size="30" type="text" name="ort" value="' .$varray['ort']. '" /> Ort<br />';
	echo '<input type="hidden" name="formkomplettdargestellt" value="1" />';
	echo '<input type="submit" value="Speichern" name="Save"><br />';
	echo "</form>";
}
?>