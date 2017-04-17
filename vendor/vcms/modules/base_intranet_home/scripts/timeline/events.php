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


/*
* actions
*/

if(isset($_REQUEST['veranstaltungenchangeanmeldenstate']) && $_REQUEST['veranstaltungenchangeanmeldenstate'] != '' && isset($_REQUEST['eventid']) && $_REQUEST['eventid'] != ''){
	$stmt = $libDb->prepare('SELECT * FROM base_veranstaltung WHERE id=:id');
	$stmt->bindValue(':id', $_REQUEST['eventid'], PDO::PARAM_INT);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	//event in future?
	if(date('Y-m-d H:i:s') < $row['datum']){
		if($_REQUEST['veranstaltungenchangeanmeldenstate'] == 'anmelden'){
			$stmt = $libDb->prepare('INSERT IGNORE INTO base_veranstaltung_teilnahme (veranstaltung, person) VALUES (:veranstaltung, :person)');
			$stmt->bindValue(':veranstaltung', $_REQUEST['eventid'], PDO::PARAM_INT);
			$stmt->bindValue(':person', $libAuth->getId(), PDO::PARAM_INT);
			$stmt->execute();
		} else {
			$stmt = $libDb->prepare('DELETE FROM base_veranstaltung_teilnahme WHERE veranstaltung=:veranstaltung AND person=:person');
			$stmt->bindValue(':veranstaltung', $_REQUEST['eventid'], PDO::PARAM_INT);
			$stmt->bindValue(':person', $libAuth->getId(), PDO::PARAM_INT);
			$stmt->execute();
		}
	}
}


/*
* output
*/

class LibEventTimelineEvent extends \vcms\timeline\LibTimelineEvent{
	function getBadgeClass(){
		return 'event';
	}

	function getBadgeIcon(){
		return '<i class="fa fa-calendar" aria-hidden="true"></i>';
	}
}


$stmt = $libDb->prepare('SELECT id, datum, titel FROM base_veranstaltung WHERE DATEDIFF(datum, NOW()) <= :zeitraumlimit AND DATEDIFF(datum, :semesterstart) >= 0 AND DATEDIFF(datum, :semesterende) <= 0 ORDER BY datum');
$stmt->bindValue(':semesterstart', $zeitraum[0]);
$stmt->bindValue(':semesterende', $zeitraum[1]);
$stmt->bindValue(':zeitraumlimit', $zeitraumLimit, PDO::PARAM_INT);
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$stmt2 = $libDb->prepare('SELECT COUNT(*) AS number FROM base_veranstaltung_teilnahme WHERE person=:person AND veranstaltung=:veranstaltung');
	$stmt2->bindValue(':person', $libAuth->getId(), PDO::PARAM_INT);
	$stmt2->bindValue(':veranstaltung', $row['id'], PDO::PARAM_INT);
	$stmt2->execute();
	$stmt2->bindColumn('number', $angemeldet);
	$stmt2->fetch();

	$title = $row['titel'];
	$url = 'index.php?pid=event&amp;id=' .$row['id'];
	$form = '';
	$description = '';

	/*
	* thumbnail
	*/
	if($libGallery->hasPictures($row['id'], 1)){
		$description .= '<div class="thumbnail">';
		$description .= '<div class="img-frame">';
		$description .= '<a href="index.php?pid=event&amp;id=' .$row['id']. '">';
		$description .= '<img data-object-fit="cover" src="api.php?iid=event_picture&amp;eventid=' .$row['id']. '&amp;id=' .$libGallery->getFirstVisiblePictureId($row['id'], 1). '" alt="" />';
		$description .= '</a>';
		$description .= '</div>';
		$description .= '</div>';

		$description .= '<hr />';
	}

	/*
	* attend
	*/
	if(date('Y-m-d H:i:s') < $row['datum']){
		$form .= '<form action="index.php?pid=intranet_home" method="post" class="form-horizontal">';
		$form .= '<input type="hidden" name="eventid" value="' .$row['id']. '" />';

		if($angemeldet){
			$form .= '<input type="hidden" name="veranstaltungenchangeanmeldenstate" value="abmelden" />';
			$form .= '<button type="submit" class="btn btn-default btn-sm">';
			$form .= '<i class="fa fa-check-square-o" aria-hidden="true"></i> Abmelden';
			$form .= '</button>';
		} else {
			$form .= '<input type="hidden" name="veranstaltungenchangeanmeldenstate" value="anmelden" />';
			$form .= '<button type="submit" class="btn btn-default btn-sm">';
			$form .= '<i class="fa fa-square-o" aria-hidden="true"></i> Anmelden';
			$form .= '</button>';
		}

		$form .= '</form>';
	} else {
		if($angemeldet){
			$description .= '<i class="fa fa-check-square-o" aria-hidden="true"></i> angemeldet';
		} else {
			$description .= '<i class="fa fa-square-o" aria-hidden="true"></i> nicht angemeldet';
		}
	}

	$timelineEvent = new LibEventTimelineEvent();
	$timelineEvent->setTitle($title);
	$timelineEvent->setDatetime($row['datum']);
	$timelineEvent->setUrl($url);
	$timelineEvent->setDescription($description);
	$timelineEvent->setForm($form);
	$timelineEventSet->addEvent($timelineEvent);
}
