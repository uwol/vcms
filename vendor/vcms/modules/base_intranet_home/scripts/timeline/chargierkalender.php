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

if(isset($_REQUEST["chargierkalenderchangeanmeldenstate"]) && $_REQUEST["chargierkalenderchangeanmeldenstate"] != "" && isset($_REQUEST['chargierveranstaltungid']) && $_REQUEST['chargierveranstaltungid'] != ""){
	$stmt = $libDb->prepare("SELECT * FROM mod_chargierkalender_veranstaltung WHERE id=:id");
	$stmt->bindValue(':id', $_REQUEST['chargierveranstaltungid'], PDO::PARAM_INT);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	// event in future?
	if(@date("Y-m-d H:i:s") <= $row["datum"]){
		if($_REQUEST["chargierkalenderchangeanmeldenstate"] == "anmelden"){
			$stmt = $libDb->prepare("INSERT IGNORE INTO mod_chargierkalender_teilnahme (chargierveranstaltung, mitglied) VALUES (:chargierveranstaltung, :mitglied)");
			$stmt->bindValue(':chargierveranstaltung', $_REQUEST['chargierveranstaltungid'], PDO::PARAM_INT);
			$stmt->bindValue(':mitglied', $libAuth->getId(), PDO::PARAM_INT);
			$stmt->execute();
		} else {
			$stmt = $libDb->prepare("DELETE FROM mod_chargierkalender_teilnahme WHERE chargierveranstaltung=:chargierveranstaltung AND mitglied=:mitglied");
			$stmt->bindValue(':chargierveranstaltung', $_REQUEST['chargierveranstaltungid'], PDO::PARAM_INT);
			$stmt->bindValue(':mitglied', $libAuth->getId(), PDO::PARAM_INT);
			$stmt->execute();
		}
	}
}


/*
* output
*/

class LibChargiereventTimelineEvent extends \vcms\timeline\LibTimelineEvent{
	function getBadgeClass(){
		return 'chargierevent';
	}

	function getBadgeIcon(){
		return '<i class="fa fa-flag" aria-hidden="true"></i>';
	}
}


$stmt = $libDb->prepare('SELECT id, datum, verein, beschreibung FROM mod_chargierkalender_veranstaltung WHERE DATEDIFF(datum, :semesterstart) >= 0 AND DATEDIFF(datum, :semesterende) <= 0 AND DATEDIFF(datum, NOW()) >= 0 AND DATEDIFF(datum, NOW()) <= :zeitraumlimit ORDER BY datum');
$stmt->bindValue(':semesterstart', $zeitraum[0]);
$stmt->bindValue(':semesterende', $zeitraum[1]);
$stmt->bindValue(':zeitraumlimit', $zeitraumLimit, PDO::PARAM_INT);
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$stmt2 = $libDb->prepare("SELECT COUNT(*) AS number FROM mod_chargierkalender_teilnahme WHERE mitglied=:mitglied AND chargierveranstaltung=:chargierveranstaltung");
	$stmt2->bindValue(':mitglied', $libAuth->getId(), PDO::PARAM_INT);
	$stmt2->bindValue(':chargierveranstaltung', $row['id'], PDO::PARAM_INT);
	$stmt2->execute();
	$stmt2->bindColumn('number', $angemeldet);
	$stmt2->fetch();

	$title = 'Chargieren bei ';
	$url = 'index.php?pid=intranet_chargierkalender&amp;semester=' .$libTime->getSemesterNameAtDate($row['datum']). '#t' .$row['id'];
	$form = '';
	$description = '';

	if(isset($row['verein']) && is_numeric($row['verein'])){
		$title .= $libAssociation->getVereinNameString($row['verein']);
	} else {
		$title .= $row['beschreibung'];
	}

	if(date('Y-m-d H:i:s') < $row['datum']){
		$form .= '<form action="index.php?pid=intranet_home" method="post" class="form-horizontal">';
		$form .= '<input type="hidden" name="chargierveranstaltungid" value="' .$row['id']. '" />';

		if($angemeldet){
			$form .= '<input type="hidden" name="chargierkalenderchangeanmeldenstate" value="abmelden" />';
			$form .= '<button type="submit" class="btn btn-default btn-sm">';
			$form .= '<i class="fa fa-check-square-o" aria-hidden="true"></i> Abmelden';
			$form .= '</button>';
		} else {
			$form .= '<input type="hidden" name="chargierkalenderchangeanmeldenstate" value="anmelden" />';
			$form .= '<button type="submit" class="btn btn-default btn-sm">';
			$form .= '<i class="fa fa-square-o" aria-hidden="true"></i> Anmelden';
			$form .= '</button>';
		}

		$form .= '</form>';
	} else {
		if($angemeldet){
			$description .= '<i class="fa fa-check-square-o" aria-hidden="true"></i> angemeldet';
		}
	}

	/*
	* attendees
	*/
	$stmt3 = $libDb->prepare("SELECT mitglied FROM mod_chargierkalender_teilnahme WHERE chargierveranstaltung=:chargierveranstaltung");
	$stmt3->bindValue(':chargierveranstaltung', $row['id'], PDO::PARAM_INT);
	$stmt3->execute();

    $attendees = array();

	while($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)){
		$attendee = '<a href="index.php?pid=intranet_person&amp;id=' .$row3['mitglied']. '">';
		$attendee .= $libPerson->getNameString($row3['mitglied'], 8);
		$attendee .= '</a>';

		$attendees[] = $attendee;
	}

	$description .= '<p>' .implode(', ', $attendees). '</p>';

	$timelineEvent = new LibChargiereventTimelineEvent();
	$timelineEvent->setTitle($title);
	$timelineEvent->setDatetime($row['datum']);
	$timelineEvent->setUrl($url);
	$timelineEvent->setDescription($description);
	$timelineEvent->setForm($form);
	$timelineEventSet->addEvent($timelineEvent);
}
