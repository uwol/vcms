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

	$verein = '';
	if(isset($_REQUEST['verein'])){
		$verein = $_REQUEST['verein'];
	}

	$mitglied = '';
	if(isset($_REQUEST['mitglied'])){
		$mitglied = $_REQUEST['mitglied'];
	}

	$vmarray = array();
	//Felder in der Tabelle angeben -> Metadaten
	$felder = array("mitglied","verein","ehrenmitglied","semester_reception","semester_philistrierung");

	/**
	*
	* Verschiedene Aktionen auf der Datenbank durchführen, je nach Kontext
	* der durch aktion definiert wird
	*
	*/

	//neues Mitglied, leerer Datensatz
	if($aktion == "blank"){
		foreach($felder as $feld){
			$vmarray[$feld] = '';
		}
	}
	//Daten wurden mit blank eingegeben, werden nun gespeichert
	elseif($aktion == "insert"){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
			die("Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.");
		}

		$error = false;
	
		if($_REQUEST['semester_reception'] != "" && !$libTime->isValidSemesterString($_REQUEST['semester_reception'])){
			$libGlobal->errorTexts[] = "Das Receptionssemester ist falsch formatiert.";
			$error = true;
		}

		if($_REQUEST['semester_philistrierung'] != "" && !$libTime->isValidSemesterString($_REQUEST['semester_philistrierung'])){
			$libGlobal->errorTexts[] = "Das Philistrierungssemester ist falsch formatiert.";
			$error = true;
		}

		if($error){
			$vmarray = $_REQUEST;
		} else {
			$vmarray = $libDb->insertRow($felder,$_REQUEST, "base_verein_mitgliedschaft", array('verein' => $verein, 'mitglied' => $mitglied));
		}
	}
	//bestehende Daten werden modifiziert
	elseif($aktion == "update"){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
			die("Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.");
		}

		$error = false;

		if($_REQUEST['semester_reception'] != "" && !$libTime->isValidSemesterString($_REQUEST['semester_reception'])){
			$libGlobal->errorTexts[] = "Das Receptionssemester ist falsch formatiert.";
			$error = true;
		}

		if($_REQUEST['semester_philistrierung'] != "" && !$libTime->isValidSemesterString($_REQUEST['semester_philistrierung'])){
			$libGlobal->errorTexts[] = "Das Philistrierungssemester ist falsch formatiert.";
			$error = true;
		}

		if($error){
			$vmarray = $_REQUEST;
		} else {
			$vmarray = $libDb->updateRow($felder,$_REQUEST, "base_verein_mitgliedschaft", array('verein' => $verein, 'mitglied' => $mitglied));
		}
	} else {
		$stmt = $libDb->prepare("SELECT * FROM base_verein_mitgliedschaft WHERE verein=:verein AND mitglied=:mitglied");
		$stmt->bindValue(':verein', $verein, PDO::PARAM_INT);
		$stmt->bindValue(':mitglied', $mitglied, PDO::PARAM_INT);
		$stmt->execute();
		$vmarray = $stmt->fetch(PDO::FETCH_ASSOC);
	}

	/**
	*
	* Einleitender Text
	*
	*/

	echo '<h1>Vereinsmitgliedschaft</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	/**
	*
	* Löschoption
	*
	*/
	if($vmarray['mitglied'] != '' && $vmarray['verein'] != ''){
		echo '<p><a href="index.php?pid=intranet_admin_db_vereinsmitgliedschaftenliste&amp;aktion=delete&amp;mitglied='.$vmarray['mitglied'].'&amp;verein='.$vmarray['verein'].'" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">Datensatz löschen</a></p>';
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

	echo '<form action="index.php?pid=intranet_admin_db_vereinsmitgliedschaft' .$extraActionParam. '" method="post">';
	echo '<input type="submit" value="Speichern" name="Save"><br />';
	echo '<input type="hidden" name="verein" value="' .$vmarray['verein']. '" />';
	echo '<input type="hidden" name="mitglied" value="' .$vmarray['mitglied']. '" />';

	if($aktion == "blank"){
		echo $libForm->getMitgliederDropDownBox("mitglied","Mitglied",$vmarray['mitglied'],false,false);
		echo $libForm->getVereineDropDownBox("verein","Verein",$vmarray['verein'],false,false);
	} else {
		echo $libForm->getMitgliederDropDownBox("mitglied","Mitglied",$vmarray['mitglied'],false,true);
		echo $libForm->getVereineDropDownBox("verein","Verein",$vmarray['verein'],false,true);
	}

	echo $libForm->getBoolSelectBox("ehrenmitglied","Ehrenmitglied",$vmarray['ehrenmitglied']);

	echo '<input size="10" type="text" name="semester_reception" value="' .$vmarray['semester_reception']. '" /> Semester Reception<br />';
	echo '<input size="10" type="text" name="semester_philistrierung" value="' .$vmarray['semester_philistrierung']. '" /> Semester Philistrierung<br />';
	echo '<input type="hidden" name="formkomplettdargestellt" value="1" />';
	echo '<input type="submit" value="Speichern" name="Save"><br />';
	echo "</form>";
}
?>