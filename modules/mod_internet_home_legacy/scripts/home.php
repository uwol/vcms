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


if(!$libGenericStorage->attributeExists('mod_internet_home', 'fb_url')){
	$libGenericStorage->saveValue('mod_internet_home', 'fb_url', '');
}

if(!$libGenericStorage->attributeExists('mod_internet_home', 'wp_url')){
	$libGenericStorage->saveValue('mod_internet_home', 'wp_url', '');
}

if(!$libGenericStorage->attributeExists('mod_internet_home', 'showFbPagePlugin')){
	$libGenericStorage->saveValue('mod_internet_home', 'showFbPagePlugin', 1);
}


function printVeranstaltungTitle($row){
	echo '<h3><a href="index.php?pid=semesterprogramm_event&amp;eventid=' .$row['id']. '">' .$row['titel']. '</a></h3>';
}

function printVeranstaltungDateTime($row){
	global $libTime;

	echo '<time datetime="' .$libTime->formatUtcString($row['datum']). '">';
	echo $libTime->formatDateTimeString($row['datum']);
	echo '</time>';
}


echo '<h1>Willkommen</h1>';

include("elements/announcements.php");
include("elements/pastevents.php");
include("elements/nextevents.php");