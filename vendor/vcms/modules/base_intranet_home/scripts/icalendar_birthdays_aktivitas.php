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


$calendarId = $libGlobal->getSiteUrlAuthority().'_geburtstage_aktivitas_';

if(isset($_GET['user']) &&
		$_GET['user'] == $libGenericStorage->loadValueInCurrentModule('userNameICalendar') &&
		isset($_GET['pass']) &&
		$_GET['pass'] == $libGenericStorage->loadValueInCurrentModule('passwordICalendar') &&
		$libGenericStorage->loadValueInCurrentModule('userNameICalendar') != '' &&
		$libGenericStorage->loadValueInCurrentModule('passwordICalendar') != ''){

	$calendar = new vcms\LibICalendar();

	$stmt = $libDb->prepare("SELECT id,datum_geburtstag FROM base_person WHERE gruppe = 'F' OR gruppe ='B' AND datum_geburtstag != '' AND datum_geburtstag != '0000-00-00' AND datum_geburtstag IS NOT NULL");
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		$name = $libPerson->getMitgliedNameString($row['id'], 0);

		$e = new vcms\LibICalendarEvent();
		$e->summary = $name;
		$e->setStartAndEndDateTime($row['datum_geburtstag'], '');
	 	$e->description = $name. ' - ' .$row['datum_geburtstag'];
		$e->uid = $calendarId.$row['id'];
		$e->rrule = 'FREQ=YEARLY';
		$calendar->addEvent($e);
	}

	$calendar->printCalendar();
}
?>