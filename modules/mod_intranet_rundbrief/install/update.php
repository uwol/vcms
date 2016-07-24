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

if(!is_object($libGlobal))
	exit();


/**
* Datenbankstrukturen aktualisieren
*/

/**
* Tabelle mod_rundbrief_brief aktualisieren
*/
$tableExists = false;

$stmt = $libDb->prepare('SHOW TABLES');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_NUM)){
	if($row[0] == 'mod_rundbrief_brief'){
		$tableExists = true;
	}
}

if($tableExists){
	echo 'Aktualisiere Tabelle: mod_rundbrief_brief<br />';

	$sql = "DROP TABLE mod_rundbrief_brief";
	$libDb->query($sql);
}


/**
* Tabelle mod_rundbrief_empfaenger aktualisieren
*/
$fieldExists_sollEmpfangen = false;
$fieldExists_empfaenger = false;
$fieldExists_sollEmpfangen_interessierteahah = false;
$fieldExists_interessiert = false;

$stmt = $libDb->prepare('SHOW COLUMNS FROM mod_rundbrief_empfaenger');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	if($row['Field'] == 'sollempfangen'){
		$fieldExists_sollEmpfangen = true;
	} elseif($row['Field'] == 'empfaenger'){
		$fieldExists_empfaenger = true;
	} elseif($row['Field'] == 'sollempfangen_interessierteahah'){
		$fieldExists_sollEmpfangen_interessierteahah = true;
	} elseif($row['Field'] == 'interessiert'){
		$fieldExists_interessiert = true;
	}
}

if($fieldExists_sollEmpfangen){
	echo 'Aktualisiere Tabelle: mod_rundbrief_empfaenger, benenne Spalte um<br />';

	$sql = "ALTER TABLE mod_rundbrief_empfaenger CHANGE sollempfangen empfaenger tinyint(1) NOT NULL default '1'";
	$libDb->query($sql);
} elseif(!$fieldExists_empfaenger){
	echo 'Aktualisiere Tabelle: mod_rundbrief_empfaenger, füge Spalte hinzu<br />';

	$sql = "ALTER TABLE mod_rundbrief_empfaenger ADD empfaenger tinyint(1) NOT NULL default '1'";
	$libDb->query($sql);
}

if($fieldExists_sollEmpfangen_interessierteahah){
	echo 'Aktualisiere Tabelle: mod_rundbrief_empfaenger, benenne Spalte um<br />';

	$sql = "ALTER TABLE mod_rundbrief_empfaenger CHANGE sollempfangen_interessierteahah interessiert tinyint(1) NOT NULL default '0'";
	$libDb->query($sql);
} elseif(!$fieldExists_interessiert){
	echo 'Aktualisiere Tabelle: mod_rundbrief_empfaenger, füge Spalte hinzu<br />';

	$sql = "ALTER TABLE mod_rundbrief_empfaenger ADD interessiert tinyint(1) NOT NULL default '0'";
	$libDb->query($sql);
}
?>