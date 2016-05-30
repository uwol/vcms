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


if(!$libGenericStorage->attributeExistsInCurrentModule('fb:admins')){
	$libGenericStorage->saveValueInCurrentModule('fb:admins', 0);
}

if(!$libGenericStorage->attributeExistsInCurrentModule('fb_likebutton_url')){
	$libGenericStorage->saveValueInCurrentModule('fb_likebutton_url', '');
}


function printVeranstaltungTitle($row){
	echo '<a href="index.php?pid=semesterprogramm_event&amp;eventid=' .$row['id']. '">' .$row['titel']. '</a>';
	echo '<br />';
}

function printVeranstaltungTime($row){
	global $libTime;

	$date = substr($row['datum'], 0, 10);
	$datearray = explode('-', $date);
	$dateString = $datearray[2]. '.' .$datearray[1]. '.';
	$timeString = substr($row['datum'], 11, 5);

	echo $libTime->wochentag($row['datum']). ', ' .$dateString;
	echo '<br />';

	if($timeString != '00:00'){
		echo $timeString;
		echo '<br />';
	}
}


echo '<h1>Willkommen</h1>';

include("elements/announcements.php");
echo '<hr />';
include("elements/thumbnails.php");
echo '<hr />';
include("elements/nextevents.php");