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
	$felder = array("name","kuerzel","aktivitas","ahahschaft","titel","rang","dachverband","dachverbandnr","zusatz1","strasse1","ort1","plz1","land1","telefon1","anschreiben_zusenden","mutterverein","fusioniertin","datum_gruendung","webseite","wahlspruch","farbenstrophe","farbenstrophe_inoffiziell","fuchsenstrophe","bundeslied","farbe1","farbe2","farbe3","farbe4","beschreibung");

	/**
	*
	* Verschiedene Aktionen auf der Datenbank durchführen, je nach Kontext
	* der durch aktion definiert wird
	*
	*/

	//neuer Verein, leerer Datensatz
	if($aktion == "blank"){
		foreach($felder as $feld){
			$array[$feld] = '';
		}

		$array['id'] = '';
		$array['name'] = "Namen angeben!";
		$array['aktivitas'] = 1;
		$array['ahahschaft'] = 1;
		$array['anschreiben_zusenden'] = 1;
		$array['datum_adresse1_stand'] = '';
	}
	//Daten wurden mit blank eingegeben, werden nun gespeichert
	elseif($aktion == "insert"){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt'])
			die("Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.");

		$valueArray = $_REQUEST;
		$valueArray['datum_gruendung'] = $libTime->assureMysqlDate($valueArray['datum_gruendung']);
		$array = $libDb->insertRow($felder, $valueArray, "base_verein", array("id"=>''));
		updateAdresseStand("base_verein", "datum_adresse1_stand", $array['id']);
	}
	//bestehende Daten werden modifiziert
	elseif($aktion == "update"){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
			die("Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.");
		}

		$stmt = $libDb->prepare("SELECT * FROM base_verein WHERE id=:id");
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$array = $stmt->fetch(PDO::FETCH_ASSOC);

		//Adressänderungen prüfen und vermerken im Stand
		if($_REQUEST['strasse1'] != $array['strasse1'] || $_REQUEST['ort1'] != $array['ort1'] || $_REQUEST['plz1'] != $array['plz1']){
			updateAdresseStand("base_verein", "datum_adresse1_stand", $array['id']);
		}

		$valueArray = $_REQUEST;
		$valueArray['datum_gruendung'] = $libTime->assureMysqlDate($valueArray['datum_gruendung']);
		$array = $libDb->updateRow($felder, $valueArray, "base_verein", array("id" => $id));
	} else {
		$stmt = $libDb->prepare("SELECT * FROM base_verein WHERE id=:id");
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
		echo '<p><a href="index.php?pid=intranet_admin_db_vereinsliste&amp;aktion=delete&amp;id='.$array['id'].'" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">Datensatz löschen</a></p>';
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

	echo '<form action="index.php?pid=intranet_admin_db_verein' .$extraActionParam. '" method="post">';
	echo '<input type="submit" value="Speichern" name="Save"><br />';
	echo '<input type="hidden" name="formtyp" value="vereinsdaten" />';
	echo '<input type="hidden" name="id" value="' .$array['id']. '" />';
	echo '<input size="20" type="text" name="id" value="' .$array['id']. '" disabled /> Id<br />';
	echo '<input size="20" type="text" name="name" value="' .$array['name']. '" /> Name<br />';
	echo '<input size="60" type="text" name="kuerzel" value="' .$array['kuerzel']. '" /> Kürzel<br />';

	echo $libForm->getBoolSelectBox("aktivitas","Aktivitas",$array['aktivitas']);
	echo $libForm->getBoolSelectBox("ahahschaft","Altherrenschaft",$array['ahahschaft']);

	echo '<input size="60" type="text" name="titel" value="' .$array['titel']. '" /> Titel<br />';

	echo '<input size="60" type="text" name="rang" value="' .$array['rang']. '" /> Rang<br />';
	echo '<input size="30" type="text" name="dachverband" value="' .$array['dachverband']. '" /> Dachverband<br />';
	echo '<input size="11" type="text" name="dachverbandnr" value="' .$array['dachverbandnr']. '" /> Dachverbandnummer<br />';
	echo '<input size="30" type="text" name="zusatz1" value="' .$array['zusatz1']. '" /> Zusatz<br />';
	echo '<input size="30" type="text" name="strasse1" value="' .$array['strasse1']. '" /> Strasse<br />';
	echo '<input size="30" type="text" name="ort1" value="' .$array['ort1']. '" /> Ort<br />';
	echo '<input size="30" type="text" name="plz1" value="' .$array['plz1']. '" /> Plz<br />';
	echo '<input size="30" type="text" name="land1" value="' .$array['land1']. '" /> Land<br />';
	echo '<input size="30" type="text" name="datum_adresse1_stand" value="' .$array['datum_adresse1_stand']. '" disabled /> Stand<br />';
	echo '<input size="30" type="text" name="telefon1" value="' .$array['telefon1']. '" /> Telefon1<br />';

	echo $libForm->getBoolSelectBox("anschreiben_zusenden","Anschreiben zusenden",$array['anschreiben_zusenden']);
	echo $libForm->getVereineDropDownBox("mutterverein", "Mutterverein", $array['mutterverein']);
	echo $libForm->getVereineDropDownBox("fusioniertin", "Fusioniert in", $array['fusioniertin']);

	echo '<input size="30" type="text" name="datum_gruendung" value="' .$array['datum_gruendung']. '" /> Gründungsdatum<br />';
	echo '<input size="30" type="text" name="webseite" value="' .$array['webseite']. '" /> Webseite<br />';
	echo '<input size="30" type="text" name="wahlspruch" value="' .$array['wahlspruch']. '" /> Wahlspruch<br />';
	echo 'Farbenstrophe<br /><textarea name="farbenstrophe" cols="70" rows="7">' . $array['farbenstrophe'] .'</textarea><br />';
	echo 'inoffizielle Farbenstrophe<br /><textarea name="farbenstrophe_inoffiziell" cols="70" rows="7">' . $array['farbenstrophe_inoffiziell'] .'</textarea><br />';
	echo 'Fuchsenstrophe<br /><textarea name="fuchsenstrophe" cols="70" rows="7">' . $array['fuchsenstrophe'] .'</textarea><br />';
	echo 'Bundeslied<br /><textarea name="bundeslied" cols="70" rows="7">' . $array['bundeslied'] .'</textarea><br />';

	echo '<input size="30" type="text" name="farbe1" value="' .$array['farbe1']. '" /> Farbe 1<br />';
	echo '<input size="30" type="text" name="farbe2" value="' .$array['farbe2']. '" /> Farbe 2<br />';
	echo '<input size="30" type="text" name="farbe3" value="' .$array['farbe3']. '" /> Farbe 3<br />';
	echo '<input size="30" type="text" name="farbe4" value="' .$array['farbe4']. '" /> Farbe 4<br />';
	echo 'Beschreibung<br /><textarea name="beschreibung" cols="70" rows="7">' . $array['beschreibung'] .'</textarea><br />';
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