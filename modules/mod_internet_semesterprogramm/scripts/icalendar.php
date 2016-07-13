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

$calendarId = $libConfig->sitePath. '_semesterprogramm_';

$calendar = new vcms\LibICalendar();

$stmt = $libDb->prepare('SELECT id, datum, datum_ende, titel, beschreibung, status, ort FROM base_veranstaltung WHERE datum >= CURDATE() ORDER BY datum DESC');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$e = new vcms\LibICalendarEvent();
	$e->summary = $row['titel'];
	$e->setStartAndEndDateTime($row['datum'], $row['datum_ende']);
 	$e->description = $row['beschreibung'];
	$e->location = $row['ort'];
	$e->url = 'http://' .$libConfig->sitePath. '/index.php?pid=semesterprogramm_event&eventid='. $row['id'];
	$e->uid = $calendarId.$row['id'];
	$calendar->addEvent($e);
}

/*
$e = new LibICalendarEvent();
$e->summary = 'Der User-Agent ist: '.$_SERVER['HTTP_USER_AGENT'];
$e->setStartDateTime(date('Y-m-d'));
$e->description = 'Der User-Agent ist: '.$_SERVER['HTTP_USER_AGENT'];
$calendar->addEvent($e);
*/

$calendar->printCalendar();
?>