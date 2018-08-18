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
	$libGlobal->notificationTexts[] = 'Aktualisiere Tabelle: mod_rundbrief_brief';

	$sql = "DROP TABLE mod_rundbrief_brief";
	$libDb->query($sql);
}


/**
* Tabelle mod_rundbrief_empfaenger aktualisieren
*/
$fieldExistsEmpfaenger = false;
$fieldExistsInteressiert = false;
$fieldExistsSollEmpfangen = false;
$fieldExistsSollEmpfangenInteressierteAhAh = false;

$stmt = $libDb->prepare('SHOW COLUMNS FROM mod_rundbrief_empfaenger');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	if($row['Field'] == 'sollempfangen'){
		$fieldExistsSollEmpfangen = true;
	} elseif($row['Field'] == 'empfaenger'){
		$fieldExistsEmpfaenger = true;
	} elseif($row['Field'] == 'sollempfangen_interessierteahah'){
		$fieldExistsSollEmpfangenInteressierteAhAh = true;
	} elseif($row['Field'] == 'interessiert'){
		$fieldExistsInteressiert = true;
	}
}

if($fieldExistsSollEmpfangen){
	$libGlobal->notificationTexts[] = 'Aktualisiere Tabelle: mod_rundbrief_empfaenger, benenne Spalte um';

	$sql = "ALTER TABLE mod_rundbrief_empfaenger CHANGE sollempfangen empfaenger tinyint(1) NOT NULL default '1'";
	$libDb->query($sql);
} elseif(!$fieldExistsEmpfaenger){
	$libGlobal->notificationTexts[] = 'Aktualisiere Tabelle: mod_rundbrief_empfaenger, füge Spalte hinzu';

	$sql = "ALTER TABLE mod_rundbrief_empfaenger ADD empfaenger tinyint(1) NOT NULL default '1'";
	$libDb->query($sql);
}

if($fieldExistsSollEmpfangenInteressierteAhAh){
	$libGlobal->notificationTexts[] = 'Aktualisiere Tabelle: mod_rundbrief_empfaenger, benenne Spalte um';

	$sql = "ALTER TABLE mod_rundbrief_empfaenger CHANGE sollempfangen_interessierteahah interessiert tinyint(1) NOT NULL default '0'";
	$libDb->query($sql);
} elseif(!$fieldExistsInteressiert){
	$libGlobal->notificationTexts[] = 'Aktualisiere Tabelle: mod_rundbrief_empfaenger, füge Spalte hinzu';

	$sql = "ALTER TABLE mod_rundbrief_empfaenger ADD interessiert tinyint(1) NOT NULL default '0'";
	$libDb->query($sql);
}
