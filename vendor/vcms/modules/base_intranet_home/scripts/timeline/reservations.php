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


class LibReservationTimelineEvent extends LibTimelineEvent{
	function getBadgeClass(){
		return 'reservation';
	}

	function getBadgeIcon(){
		return '<i class="fa fa-map-marker" aria-hidden="true"></i>';
	}
}


$stmt = $libDb->prepare("SELECT id, person, datum, beschreibung FROM mod_reservierung_reservierung WHERE DATEDIFF(NOW(), datum) <= 0 AND DATEDIFF(datum, NOW()) <= :zeitraumlimit AND DATEDIFF(datum, :semesterstart) >= 0 AND DATEDIFF(datum, :semesterende) <= 0 ORDER BY datum");
$stmt->bindValue(':semesterstart', $zeitraum[0]);
$stmt->bindValue(':semesterende', $zeitraum[1]);
$stmt->bindValue(':zeitraumlimit', $zeitraumLimit, PDO::PARAM_INT);
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$title = 'Reservierung durch ' .$libPerson->getMitgliedNameString($row['person'], 0);
	$url = 'index.php?pid=intranet_reservierung_liste#' .$row['id'];

	$timelineEvent = new LibReservationTimelineEvent();

	$timelineEvent->setTitle($title);
	$timelineEvent->setDatetime($row['datum']);
	$timelineEvent->setDescription($row['beschreibung']);
	$timelineEvent->setAuthorId($row['person']);
	$timelineEvent->setUrl($url);

	$timelineEvent->hideAuthorSignature();

	$timelineEventSet->addEvent($timelineEvent);
}
?>