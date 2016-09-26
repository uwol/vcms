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


function getColumns($table){
	global $libDb;

	$stmt = $libDb->prepare('SHOW COLUMNS FROM ' .$table);
	$stmt->execute();

	$result = array();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		$result[] = $row['Field'];
	}

	return $result;
}

function getIndexes($table){
	global $libDb;

	$stmt = $libDb->prepare('SHOW INDEX FROM ' .$table);
	$stmt->execute();

	$result = array();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		$result[] = $row['Key_name'];
	}

	return $result;
}


/**
* Tabelle base_veranstaltung aktualisieren
*/
$columnsBaseVeranstaltung = getColumns('base_veranstaltung');

if(!in_array('datum_ende', $columnsBaseVeranstaltung)){
	echo 'Aktualisiere Tabelle base_veranstaltung<br />';
	$libDb->query('ALTER TABLE base_veranstaltung ADD datum_ende DATETIME NULL AFTER datum');
}

if(!in_array('fb_eventid', $columnsBaseVeranstaltung)){
	echo 'Aktualisiere Tabelle base_veranstaltung<br />';
	$libDb->query('ALTER TABLE base_veranstaltung ADD fb_eventid VARCHAR(255) NULL');
}

if(!in_array('intern', $columnsBaseVeranstaltung)){
	echo 'Aktualisiere Tabelle base_veranstaltung<br />';
	$libDb->query('ALTER TABLE base_veranstaltung ADD intern tinyint(1) NOT NULL default 0');
}


/**
* Tabelle base_person aktualisieren
*/
$columnsBasePerson = getColumns('base_person');
$indexesBasePerson = getIndexes('base_person');

if(in_array('username', $indexesBasePerson)){
	echo 'Aktualisiere Index auf Tabelle base_person<br />';
	$libDb->query('DROP INDEX username ON base_person');
}

if(in_array('austritt_grund', $columnsBasePerson)){
	echo 'Aktualisiere Tabelle base_person<br />';
	$libDb->query('ALTER TABLE base_person DROP austritt_grund');
}

if(in_array('password_salt', $columnsBasePerson)){
	echo 'Aktualisiere Tabelle base_person<br />';
	$libDb->query('ALTER TABLE base_person DROP password_salt');
}

if(in_array('icq', $columnsBasePerson)){
	echo 'Aktualisiere Tabelle base_person<br />';
	$libDb->query('ALTER TABLE base_person DROP icq');
}

if(in_array('msn', $columnsBasePerson)){
	echo 'Aktualisiere Tabelle base_person<br />';
	$libDb->query('ALTER TABLE base_person DROP msn');
}

if(in_array('vita_letzterautor', $columnsBasePerson)){
	echo 'Aktualisiere Tabelle base_person<br />';
	$libDb->query('ALTER TABLE base_person DROP vita_letzterautor');
}

if(in_array('username', $columnsBasePerson)){
	echo 'Aktualisiere Tabelle base_person<br />';
	$libDb->query('ALTER TABLE base_person DROP username');
}

if(!in_array('email', $indexesBasePerson)){
	echo 'Aktualisiere Index auf Tabelle base_person<br />';
	$libDb->query('ALTER TABLE base_person ADD UNIQUE email (email)');
}


/**
* Tabelle base_semester aktualisieren
*/
$columnsBaseSemester = getColumns('base_semester');

if(!in_array('vop', $columnsBaseSemester)){
	echo 'Aktualisiere Tabelle base_semester<br />';
	$libDb->query('ALTER TABLE base_semester ADD vop int(11) default NULL');
}

if(!in_array('vvop', $columnsBaseSemester)){
	echo 'Aktualisiere Tabelle base_semester<br />';
	$libDb->query('ALTER TABLE base_semester ADD vvop int(11) default NULL');
}

if(!in_array('vopxx', $columnsBaseSemester)){
	echo 'Aktualisiere Tabelle base_semester<br />';
	$libDb->query('ALTER TABLE base_semester ADD vopxx int(11) default NULL');
}

if(!in_array('vopxxx', $columnsBaseSemester)){
	echo 'Aktualisiere Tabelle base_semester<br />';
	$libDb->query('ALTER TABLE base_semester ADD vopxxx int(11) default NULL');
}

if(!in_array('vopxxxx', $columnsBaseSemester)){
	echo 'Aktualisiere Tabelle base_semester<br />';
	$libDb->query('ALTER TABLE base_semester ADD vopxxxx int(11) default NULL');
}


die('</div><a href="index.php?pid=modules">Klicke hier</a>, um die Modulliste anzuzeigen.');
