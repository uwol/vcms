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

function renameStorageKey($moduleId, $oldArrayName, $newArrayName){
	global $libDb;

	$stmt = $libDb->prepare('UPDATE sys_genericstorage SET array_name=:new_array_name WHERE moduleid=:moduleid AND array_name=:old_array_name');
	$stmt->bindValue(':new_array_name', $newArrayName);
	$stmt->bindValue(':moduleid', $moduleId);
	$stmt->bindValue(':old_array_name', $oldArrayName);
	$stmt->execute();
}


/*
* Update base_veranstaltung
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


/*
* Update base_person
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


/*
* Update base_semester
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


/*
* Update base_semester
*/
echo 'Aktualisiere Tabelle sys_genericstorage<br />';

renameStorageKey('base_core', 'brandXs', 'brand_xs');
renameStorageKey('base_core', 'deleteAusgetretene', 'delete_ausgetretene');
renameStorageKey('base_core', 'eventBannedTitles', 'event_banned_titles');
renameStorageKey('base_core', 'eventGalleryMaxPublicSemesters', 'event_public_gallery_semesters');
renameStorageKey('base_core', 'eventPreselectIntern', 'event_preselect_intern');
renameStorageKey('base_core', 'fbAppId', 'facebook_appid');
renameStorageKey('base_core', 'fbSecretKey', 'facebook_secret_key');
renameStorageKey('base_core', 'imagemanipulator', 'image_lib');
renameStorageKey('base_core', 'siteUrl', 'site_url');
renameStorageKey('base_core', 'smtpEnable', 'smtp_enable');
renameStorageKey('base_core', 'smtpHost', 'smtp_host');
renameStorageKey('base_core', 'smtpPassword', 'smtp_password');
renameStorageKey('base_core', 'smtpUsername', 'smtp_username');

renameStorageKey('base_internet_login', 'sslProxyUrl', 'ssl_proxy_url');

renameStorageKey('base_intranet_home', 'checkFilePermissions', 'check_file_permissions');
renameStorageKey('base_intranet_home', 'passwordICalendar', 'icalendar_password');
renameStorageKey('base_intranet_home', 'userNameICalendar', 'icalendar_username');
renameStorageKey('base_intranet_home', 'showReservations', 'show_reservations');

renameStorageKey('base_intranet_personen', 'showGroupY', 'show_group_y');

renameStorageKey('mod_internet_home', 'fb_url', 'facebook_url');
renameStorageKey('mod_internet_home', 'wp_url', 'wikipedia_url');
renameStorageKey('mod_internet_home', 'showFbPagePlugin', 'show_facebook_plugin');

renameStorageKey('mod_internet_kontakt', 'showHaftungshinweis', 'show_haftungshinweis');
renameStorageKey('mod_internet_kontakt', 'showSenior', 'show_senior');
renameStorageKey('mod_internet_kontakt', 'showJubelsenior', 'show_jubelsenior');
renameStorageKey('mod_internet_kontakt', 'showConsenior', 'show_consenior');
renameStorageKey('mod_internet_kontakt', 'showFuchsmajor', 'show_fuchsmajor');
renameStorageKey('mod_internet_kontakt', 'showFuchsmajor2', 'show_fuchsmajor2');
renameStorageKey('mod_internet_kontakt', 'showQuaestor', 'show_quaestor');
renameStorageKey('mod_internet_kontakt', 'showScriptor', 'show_scriptor');

renameStorageKey('mod_intranet_download', 'rightsPreselection', 'preselect_rights');

renameStorageKey('mod_intranet_rundbrief', 'preselectInteressierteAHAH', 'preselect_int_ahah');


die('</div><a href="index.php?pid=modules">Klicke hier</a>, um die Modulliste anzuzeigen.');
