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

	$array = array();
	//Felder in der Tabelle angeben -> Metadaten
	$felder = array("praefix","name","suffix","vorname","anrede","titel","rang","zusatz1","strasse1","plz1","ort1","land1","telefon1","status","grund","bemerkung");

	/**
	*
	* Verschiedene Aktionen auf der Datenbank durchführen, je nach Kontext
	* der durch aktion definiert wird
	*
	*/

	//neue, leerer Datensatz
	if($aktion == "blank"){
		foreach($felder as $feld){
			$array[$feld] = '';
		}

		$array['id'] = '';
		$array['name'] = "Namen angeben!";
		$array['datum_adresse1_stand'] = '';
	}
	//Daten wurden mit blank eingegeben, werden nun gespeichert
	elseif($aktion == "insert"){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
			die("Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.");
		}

		$array = $libDb->insertRow($felder,$_REQUEST, "base_vip", array("id"=>''));
		updateAdresseStand("base_vip", "datum_adresse1_stand", $array['id']);
	}
	//bestehende Daten werden modifiziert
	elseif($aktion == "update"){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
			die("Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.");
		}

		$stmt = $libDb->prepare("SELECT * FROM base_vip WHERE id=:id");
		$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
		$stmt->execute();
		$array = $stmt->fetch(PDO::FETCH_ASSOC);

		//Adressänderungen prüfen und vermerken im Stand
		if($_REQUEST['strasse1'] != $array['strasse1'] || $_REQUEST['ort1'] != $array['ort1'] || $_REQUEST['plz1'] != $array['plz1']){
			updateAdresseStand("base_vip", "datum_adresse1_stand", $array['id']);
		}

		$array = $libDb->updateRow($felder,$_REQUEST, "base_vip", array("id" => $id));
	} else {
		$stmt = $libDb->prepare("SELECT * FROM base_vip WHERE id=:id");
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
		echo '<p><a href="index.php?pid=intranet_admin_db_vipliste&amp;aktion=delete&amp;id='.$array['id'].'" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">Datensatz löschen</a></p>';
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

	echo '<form action="index.php?pid=intranet_admin_db_vip' .$extraActionParam. '" method="post">';
	echo '<input type="submit" value="Speichern" name="Save"><br />';
	echo '<input type="hidden" name="formtyp" value="vipdaten" />';
	echo '<input type="hidden" name="id" value="' .$array['id']. '" />';
	echo '<input size="20" type="text" name="id" value="' .$array['id']. '" disabled /> Id<br />';
	echo '<input size="30" type="text" name="praefix" value="' .$array['praefix']. '" /> Präfix<br />';
	echo '<input size="30" type="text" name="name" value="' .$array['name']. '" /> Name<br />';
	echo '<input size="30" type="text" name="suffix" value="' .$array['suffix']. '" /> Suffix<br />';
	echo '<input size="30" type="text" name="vorname" value="' .$array['vorname']. '" /> Vorname<br />';
	echo '<input size="30" type="text" name="anrede" value="' .$array['anrede']. '" /> Anrede<br />';
	echo '<input size="30" type="text" name="titel" value="' .$array['titel']. '" /> Titel<br />';
	echo '<input size="30" type="text" name="rang" value="' .$array['rang']. '" /> Rang<br />';
	echo '<input size="30" type="text" name="zusatz1" value="' .$array['zusatz1']. '" /> Zusatz1<br />';
	echo '<input size="30" type="text" name="strasse1" value="' .$array['strasse1']. '" /> Strasse1<br />';
	echo '<input size="30" type="text" name="plz1" value="' .$array['plz1']. '" /> Plz1<br />';
	echo '<input size="30" type="text" name="ort1" value="' .$array['ort1']. '" /> Ort1<br />';
	echo '<input size="30" type="text" name="land1" value="' .$array['land1']. '" /> Land1<br />';
	echo '<input size="30" type="text" name="datum_adresse1_stand" value="' .$array['datum_adresse1_stand']. '" disabled /> Stand1<br />';
	echo '<input size="30" type="text" name="telefon1" value="' .$array['telefon1']. '" /> Telefon1<br />';
	echo '<input size="30" type="text" name="status" value="' .$array['status']. '" /> Status<br />';
	echo '<input size="30" type="text" name="grund" value="' .$array['grund']. '" /> Grund<br />';
	echo '<input size="30" type="text" name="bemerkung" value="' .$array['bemerkung']. '" /> Bemerkung<br />';
	echo '<input type="hidden" name="formkomplettdargestellt" value="1" />';
	echo '<input type="submit" value="Speichern" name="Save"><br />';
	echo "</form>";
}

function updateAdresseStand($table, $field, $id){
	global $libDb;

	$stmt = $libDb->prepare("UPDATE ".$table." SET " .$field. "=NOW() WHERE id=:id");
	$stmt->bindValue(':id', $id, PDO::PARAM_INT);
	$stmt->execute();
}
?>