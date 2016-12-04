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


echo '<h1>Semesterprogramm ' .$libTime->getSemesterString($libGlobal->semester). '</h1>';

$stmt = $libDb->prepare("SELECT DATE_FORMAT(datum,'%Y-%m-01') AS datum FROM base_veranstaltung GROUP BY datum ORDER BY datum DESC");
$stmt->execute();

$daten = array();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$daten[] = $row['datum'];
}

echo $libTime->getSemesterMenu($libTime->getSemestersFromDates($daten), $libGlobal->semester);

echo '<p>Das Semesterprogramm kann per <a href="webcal://' .$libGlobal->getSiteUrlAuthority(). '/api.php?iid=semesterprogramm_icalendar"><i class="fa fa-calendar" aria-hidden="true"></i> iCalendar</a> z. B. in iCloud oder Google Calendar abonniert werden.</p>';
echo '<div class="vcalendar">';

$zeitraum = $libTime->getZeitraum($libGlobal->semester);
$calendar = new \vcms\calendar\LibCalendar($zeitraum[0], $zeitraum[1]);
$intern = $libAuth->isLoggedin() ? 1 : 0;

$stmt = $libDb->prepare("SELECT * FROM base_veranstaltung WHERE intern <= :intern AND ((DATEDIFF(datum, :startdatum1) >= 0 AND DATEDIFF(datum, :startdatum2) <= 0) OR (DATEDIFF(datum_ende, :enddatum1) >= 0 AND DATEDIFF(datum_ende, :enddatum2) <= 0)) ORDER BY datum");
$stmt->bindValue(':startdatum1', $zeitraum[0]);
$stmt->bindValue(':startdatum2', $zeitraum[1]);
$stmt->bindValue(':enddatum1', $zeitraum[0]);
$stmt->bindValue(':enddatum2', $zeitraum[1]);
$stmt->bindValue(':intern', $intern);
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$level = $libAuth->isLoggedin() ? 1 : 0;

	//build event
	$event = new \vcms\calendar\LibCalendarEvent($row['datum']);
	$event->setId($row['id']);
	$event->setLocation($row['ort']);
	$event->setSummary($row['titel']);
	$event->setLinkUrl('index.php?pid=event&amp;id=' .$row['id']);
	$event->setStatus($row['status']);

	if(substr($row['datum'], 11, 8) == "00:00:00"){
		$event->isAllDay(true);
	}

	if($row['datum_ende'] != '' && $row['datum_ende'] != '0000-00-00 00:00:00'){
		$event->setEndDateTime($row['datum_ende']);
	}

	$description = "";
	$pictureId = $libGallery->getMainPictureId($row['id']);

	if($pictureId > -1){
		$event->setImageUrl('api.php?iid=event_picture&amp;eventid=' .$row['id']. '&amp;id=' .$pictureId);
	}

	$stmt2 = $libDb->prepare("SELECT COUNT(*) AS number FROM base_veranstaltung_teilnahme WHERE person=:person AND veranstaltung=:veranstaltung");
	$stmt2->bindValue(':person', $libAuth->getId(), PDO::PARAM_INT);
	$stmt2->bindValue(':veranstaltung', $row['id'], PDO::PARAM_INT);
	$stmt2->execute();
	$stmt2->bindColumn('number', $anzahl);
	$stmt2->fetch();

	if($libAuth->isloggedin() == true && $anzahl > 0){
		$event->isAttended(true);
		$event->setAttendedIcon('<i class="fa fa-check-square-o" aria-hidden="true"></i>');
	}

	$event->setDescription($description);

	$calendar->addEvent($event);
}

echo $calendar->toString();
echo '</div>';
