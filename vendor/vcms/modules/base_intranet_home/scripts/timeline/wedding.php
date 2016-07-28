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


class LibWeddingTimelineEvent extends LibTimelineEvent{
	function getBadgeClass(){
		return 'wedding';
	}

	function getBadgeIcon(){
		return '<i class="fa fa-circle-o" aria-hidden="true" style="margin-right:-0.2em"></i><i class="fa fa-circle-o" aria-hidden="true" style="margin-left:-0.2em"></i>';
	}
}


$stmt = $libDb->prepare("SELECT id, heirat_datum, heirat_partner FROM base_person WHERE (gruppe='P' OR gruppe='B' OR gruppe='F') AND heirat_datum != '' AND heirat_datum != '0000-00-00' AND DATEDIFF(heirat_datum, :semesterstart) >= 0 AND DATEDIFF(heirat_datum, :semesterende) <= 0 ORDER BY heirat_datum");
$stmt->bindValue(':semesterstart', $zeitraum[0]);
$stmt->bindValue(':semesterende', $zeitraum[1]);
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$title = 'Trauung von ' .$libMitglied->getMitgliedNameString($row['id'], 0);

	$description = $libMitglied->getMitgliedNameString($row['id'], 0);
	$description .= ' ';
	$description .= '<i class="fa fa-circle-o" aria-hidden="true" style="margin-right:-0.2em"></i>';
	$description .= '<i class="fa fa-circle-o" aria-hidden="true" style="margin-left:-0.2em"></i>';

	if($row['heirat_partner'] != '' && is_numeric($row['heirat_partner'])){
		$urlPartner = 'index.php?pid=intranet_person_daten&amp;personid=' .$row['heirat_partner'];

		$description .= ' ';
		$description .= '<a href="' .$urlPartner. '">';
		$description .= $libMitglied->getMitgliedNameString($row['heirat_partner'], 0);
		$description .= '</a>';
	}

	$url = 'index.php?pid=intranet_person_daten&amp;personid=' .$row['id'];

	$timelineEvent = new LibWeddingTimelineEvent();

	$timelineEvent->setTitle($title);
	$timelineEvent->setDatetime($row['heirat_datum']);
	$timelineEvent->setUrl($url);
	$timelineEvent->setDescription($description);
	$timelineEvent->setReferencedPersonId($row['id']);

	$timelineEventSet->addEvent($timelineEvent);
}
?>