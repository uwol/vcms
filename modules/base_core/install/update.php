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

if(!is_object($libGlobal))
	exit();


/**
* Datenbankstrukturen aktualisieren
*/

/**
* Tabelle base_veranstaltung aktualisieren
*/
$fieldExists_datumEnde = false;
$fieldExists_fbEventId = false;

$stmt = $libDb->prepare('SHOW COLUMNS FROM base_veranstaltung');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	if($row['Field'] == 'datum_ende'){
		$fieldExists_datumEnde = true;
	}

	if($row['Field'] == 'fb_eventid'){
		$fieldExists_fbEventId = true;
	}
}

if(!$fieldExists_datumEnde){
	echo 'Aktualisiere Tabelle: base_veranstaltung<br />';

	$sql = "ALTER TABLE base_veranstaltung ADD datum_ende DATETIME NULL AFTER datum";
	$libDb->query($sql);
}

if(!$fieldExists_fbEventId){
	echo 'Aktualisiere Tabelle: base_veranstaltung<br />';

	$sql = "ALTER TABLE base_veranstaltung ADD fb_eventid VARCHAR(255) NULL";
	$libDb->query($sql);
}


/**
* Tabelle base_person aktualisieren
*/
$fieldExists_austritt_grund = false;
$fieldExists_password_salt = false;
$fieldExists_icq = false;
$fieldExists_msn = false;

$stmt = $libDb->prepare('SHOW COLUMNS FROM base_person');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	if($row['Field'] == 'austritt_grund'){
		$fieldExists_austritt_grund = true;
	}

	if($row['Field'] == 'password_salt'){
		$fieldExists_password_salt = true;
	}

	if($row['Field'] == 'icq'){
		$fieldExists_icq = true;
	}

	if($row['Field'] == 'msn'){
		$fieldExists_msn = true;
	}
}

if($fieldExists_austritt_grund){
	echo 'Aktualisiere Tabelle: base_person<br />';

	$libDb->query("ALTER TABLE base_person DROP austritt_grund");
}

if($fieldExists_password_salt){
	echo 'Aktualisiere Tabelle: base_person<br />';

	$libDb->query("ALTER TABLE base_person DROP password_salt");
}

if($fieldExists_icq){
	echo 'Aktualisiere Tabelle: base_person<br />';

	$libDb->query("ALTER TABLE base_person DROP icq");
}

if($fieldExists_msn){
	echo 'Aktualisiere Tabelle: base_person<br />';

	$libDb->query("ALTER TABLE base_person DROP msn");
}


/**
* Tabelle base_semester aktualisieren
*/
$fieldExists_vop = false;
$fieldExists_vvop = false;
$fieldExists_vopxx = false;
$fieldExists_vopxxx = false;
$fieldExists_vopxxxx = false;

$stmt = $libDb->prepare('SHOW COLUMNS FROM base_semester');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	if($row['Field'] == 'vop'){
		$fieldExists_vop = true;
	}

	if($row['Field'] == 'vvop'){
		$fieldExists_vvop = true;
	}

	if($row['Field'] == 'vopxx'){
		$fieldExists_vopxx = true;
	}

	if($row['Field'] == 'vopxxx'){
		$fieldExists_vopxxx = true;
	}

	if($row['Field'] == 'vopxxxx'){
		$fieldExists_vopxxxx = true;
	}
}

if(!$fieldExists_vop){
	echo 'Aktualisiere Tabelle: base_semester<br />';

	$sql = "ALTER TABLE base_semester ADD vop int(11) default NULL";
	$libDb->query($sql);
}

if(!$fieldExists_vvop){
	echo 'Aktualisiere Tabelle: base_semester<br />';

	$sql = "ALTER TABLE base_semester ADD vvop int(11) default NULL";
	$libDb->query($sql);
}

if(!$fieldExists_vopxx){
	echo 'Aktualisiere Tabelle: base_semester<br />';

	$sql = "ALTER TABLE base_semester ADD vopxx int(11) default NULL";
	$libDb->query($sql);
}

if(!$fieldExists_vopxxx){
	echo 'Aktualisiere Tabelle: base_semester<br />';

	$sql = "ALTER TABLE base_semester ADD vopxxx int(11) default NULL";
	$libDb->query($sql);
}

if(!$fieldExists_vopxxxx){
	echo 'Aktualisiere Tabelle: base_semester<br />';

	$sql = "ALTER TABLE base_semester ADD vopxxxx int(11) default NULL";
	$libDb->query($sql);
}
?>