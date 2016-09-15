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


if(!$libGenericStorage->attributeExistsInCurrentModule('fb_url')){
	$libGenericStorage->saveValueInCurrentModule('fb_url', '');
}

if(!$libGenericStorage->attributeExistsInCurrentModule('wp_url')){
	$libGenericStorage->saveValueInCurrentModule('wp_url', '');
}

if(!$libGenericStorage->attributeExistsInCurrentModule('showFbPagePlugin')){
	$libGenericStorage->saveValueInCurrentModule('showFbPagePlugin', 1);
}


function printVeranstaltungTitle($row){
	echo $row['titel'];
}

function printVeranstaltungDateTime($row){
	global $libTime;

	echo '<time datetime="' .$libTime->formatUtcString($row['datum']). '">';
	echo $libTime->formatDateTimeString($row['datum']);
	echo '</time>';
}

require_once('elements/header.php');
require_once('elements/announcements.php');
require_once('elements/pastevents.php');
require_once('elements/nextevents.php');
require_once('elements/contact.php');
require_once('elements/facebook.php');
