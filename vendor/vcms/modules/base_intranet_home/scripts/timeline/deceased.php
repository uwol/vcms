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

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();


class LibDeceasedTimelineEvent extends LibTimelineEvent{
	function getBadgeClass(){
		return 'deceased';
	}

	function getBadgeIcon(){
		return '&dagger;';
	}
}


$stmt = $libDb->prepare("SELECT id, tod_datum, datum_geburtstag FROM base_person WHERE tod_datum != '' AND tod_datum != '0000-00-00' AND DATEDIFF(tod_datum, :semesterstart) >= 0 AND DATEDIFF(tod_datum, :semesterende) <= 0 ORDER BY tod_datum");
$stmt->bindValue(':semesterstart', $zeitraum[0]);
$stmt->bindValue(':semesterende', $zeitraum[1]);
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$title = 'Todesfall ' .$libMitglied->getMitgliedNameString($row['id'], 0);

	$age = false;
	$description = '';

	if($row['datum_geburtstag'] != '' && $row['datum_geburtstag'] != '0000-00-00'){
		$dateObjectBirthday = new DateTime($row['datum_geburtstag']);
		$dateObjectDeceased = new DateTime($row['tod_datum']);
		$diff = $dateObjectDeceased->diff($dateObjectBirthday);
		$age = $diff->y;
	}

	if($age){
		$description = 'Verstorben mit ' .$age. ' Jahren ';
	}

	$description .= '<a href="webcal://' .$libConfig->sitePath. '/inc.php?iid=intranet_kalender_todestage&amp;user=' .$libGenericStorage->loadValueInCurrentModule('userNameICalendar'). '&amp;pass=' .$libGenericStorage->loadValueInCurrentModule('passwordICalendar'). '">';
	$description .= '<i class="fa fa-calendar" aria-hidden="true"></i>';
	$description .= '</a>';

	$url = 'index.php?pid=intranet_person_daten&amp;personid=' .$row['id'];

	$timelineEvent = new LibDeceasedTimelineEvent();

	$timelineEvent->setTitle($title);
	$timelineEvent->setDatetime($row['tod_datum']);
	$timelineEvent->setUrl($url);
	$timelineEvent->setDescription($description);
	$timelineEvent->setReferencedPersonId($row['id']);

	$timelineEventSet->addEvent($timelineEvent);
}
?>