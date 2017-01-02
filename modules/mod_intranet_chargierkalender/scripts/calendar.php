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


echo '<h1>Chargierkalender ' .$libTime->getSemesterString($libGlobal->semester). '</h1>';

/*
* actions
*/
if(isset($_POST["changeanmeldenstate"]) && $_POST["changeanmeldenstate"] != ""){
	if($_POST["changeanmeldenstate"] == "anmelden"){
  		$stmt = $libDb->prepare("INSERT INTO mod_chargierkalender_teilnahme (chargierveranstaltung, mitglied) VALUES (:chargierveranstaltung, :mitglied)");
		$stmt->bindValue(':chargierveranstaltung', $_POST['eventid'], PDO::PARAM_INT);
		$stmt->bindValue(':mitglied', $libAuth->getId(), PDO::PARAM_INT);
		$stmt->execute();
	} else {
		$stmt = $libDb->prepare("DELETE FROM mod_chargierkalender_teilnahme WHERE chargierveranstaltung=:chargierveranstaltung AND mitglied=:mitglied");
		$stmt->bindValue(':chargierveranstaltung', $_POST['eventid'], PDO::PARAM_INT);
		$stmt->bindValue(':mitglied', $libAuth->getId(), PDO::PARAM_INT);
		$stmt->execute();
	}
}

/*
* output
*/

//semester
$stmt = $libDb->prepare("SELECT DATE_FORMAT(datum,'%Y-%m-01') AS datum FROM mod_chargierkalender_veranstaltung GROUP BY datum ORDER BY datum DESC");
$stmt->execute();

$daten = array();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$daten[] = $row['datum'];
}

echo $libTime->getSemesterMenu($libTime->getSemestersFromDates($daten), $libGlobal->semester);

$zeitraum = $libTime->getZeitraum($libGlobal->semester);
$calendar = new \vcms\calendar\LibCalendar($zeitraum[0], $zeitraum[1]);

$stmt = $libDb->prepare("SELECT * FROM mod_chargierkalender_veranstaltung WHERE DATEDIFF(datum, :semester_start) > 0 AND DATEDIFF(datum, :semester_ende) < 0 ORDER BY datum");
$stmt->bindValue(':semester_start', $zeitraum[0]);
$stmt->bindValue(':semester_ende', $zeitraum[1]);
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	//build event
	$event = new LibChargierKalenderEvent($row['datum']);
	$event->setId($row['id']);

	if(is_numeric($row['verein'])){
		$event->setLinkUrl('index.php?pid=verein&amp;id=' .$row['verein']);
		$event->setSummary($libAssociation->getVereinNameString($row['verein']));
	}

	if(substr($row['datum'], 11, 8) == "00:00:00"){
		$event->isAllDay(true);
	}

	$event->setDescription($row['beschreibung']);

	//registered members
	$stmt2 = $libDb->prepare("SELECT mitglied FROM mod_chargierkalender_teilnahme WHERE chargierveranstaltung=:chargierveranstaltung");
	$stmt2->bindValue(':chargierveranstaltung', $row['id'], PDO::PARAM_INT);
	$stmt2->execute();

    $mitglieder = array();

	while($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
		$mitglieder[$row2['mitglied']] = $libPerson->getNameString($row2['mitglied'], 8);
	}

	$event->setAngemeldeteMitglieder($mitglieder);

	//registration button
	if($row['datum'] > @date("Y-m-d h:i:s")){
		$event->enableAnmeldeButton();

		$stmt3 = $libDb->prepare("SELECT COUNT(*) AS number FROM mod_chargierkalender_teilnahme WHERE mitglied=:mitglied AND chargierveranstaltung=:chargierveranstaltung");
		$stmt3->bindValue(':mitglied', $libAuth->getId(), PDO::PARAM_INT);
		$stmt3->bindValue(':chargierveranstaltung', $row['id'], PDO::PARAM_INT);
		$stmt3->execute();
		$stmt3->bindColumn('number', $anzahlAnmeldungen);
		$stmt3->fetch();

    	if($anzahlAnmeldungen > 0){
			$event->setAngemeldet(true);
		} else {
			$event->setAngemeldet(false);
		}
	}

	$calendar->addEvent($event);
}

echo $calendar->toString();


class LibChargierKalenderEvent{
	//time infos
	var $startDateTime; //2008-12-24 20:15:00
	var $allDay;

	//event infos
	var $id;
	var $summary;
	var $description;
	var $location;
	var $linkUrl;

	var $angemeldet;
	var $anmeldeButtonEnabled;
	var $angemeldeteMitglieder = array();

	function __construct($startDateTime){
		$this->startDateTime = $startDateTime;
	}

	function isAllDay($allDay){
		$this->allDay = $allDay;
	}

	function setId($id){
		$this->id = $id;
	}

	function setSummary($summary){
		$this->summary = $summary;
	}

	function setDescription($description){
		$this->description = $description;
	}

	function setLocation($location){
		$this->location = $location;
	}

	function setLinkUrl($linkUrl){
		$this->linkUrl = $linkUrl;
	}

	function setAngemeldet($boolean){
		$this->angemeldet = $boolean;
	}

	function enableAnmeldeButton(){
		$this->anmeldeButtonEnabled = true;
	}

	function setAngemeldeteMitglieder($mitglieder){
		$this->angemeldeteMitglieder = $mitglieder;
	}

	function getStartDateTime(){
		return $this->startDateTime;
	}

	function getStartDate(){
		return $this->getDateOfDateTime($this->startDateTime);
	}

	function getEndDateTime(){
		return '';
	}

	function getEndDate(){
		return '';
	}

	function getDateOfDateTime($dateTime){
		return substr($dateTime, 0, 10);
	}

	function toString($forDate = ''){
		global $libString, $libTime, $libGlobal;

		$retstr = '';
		$timeString = '';

		if(!$this->allDay){ //event with timeinfo?
			$timeString = $libTime->formatTimeString($this->startDateTime);
		}

		/*
		* print event
		*/
		//header
		$retstr .= '<div id="t' .$this->id. '" class="calendarEvent">';
		$retstr .= '<div><time datetime="' .$libTime->formatUtcString($this->startDateTime). '">' .$timeString. '</time></div>';

		//link
		if($this->linkUrl != ''){
			$retstr .= '<a href="' .$this->linkUrl. '">';
		}

		//summary
		if($this->summary != ''){
			$retstr .= '<div>';
			$retstr .= $this->summary;
			$retstr .= '</div>';
		}

		if($this->linkUrl != ''){
			$retstr .= '</a>';
		}

		//description
		if($this->description != ''){
			$retstr .= '<div>';
			$retstr .= $this->description;
			$retstr .= '</div>';
		}

		//location
		$retstr .= '<address>';

		if($this->location != ''){
			$retstr .= '<span>' .$this->location. '</span>';
		}

		$retstr .= '</address>';

		if(count($this->angemeldeteMitglieder) > 0){
			$mitgliederLinks = array();

			foreach($this->angemeldeteMitglieder as $key => $value){
				$mitgliederLinks[] = '<a href="index.php?pid=intranet_person&amp;id=' .$key. '">' .$value. '</a>';
			}

			$retstr .= '<div>';
			$retstr .= implode(', ', $mitgliederLinks);
			$retstr .= '</div>';
		}

		if($this->anmeldeButtonEnabled){
			$retstr .= '<form action="index.php?pid=intranet_chargierkalender" method="post" class="form-horizontal">';
			$retstr .= '<input type="hidden" name="eventid" value="' .$this->id. '" />';
			$retstr .= '<input type="hidden" name="semester" value="' .$libGlobal->semester. '" />';

    		if($this->angemeldet){
    			$retstr .= '<input type="hidden" name="changeanmeldenstate" value="abmelden" />';
					$retstr .= '<button type="submit" class="btn btn-default btn-xs">';
					$retstr .= '<i class="fa fa-check-square-o" aria-hidden="true"></i> Abmelden';
					$retstr .= '</button>';
   			} else {
    			$retstr .= '<input type="hidden" name="changeanmeldenstate" value="anmelden" />';
					$retstr .= '<button type="submit" class="btn btn-default btn-xs">';
					$retstr .= '<i class="fa fa-square-o" aria-hidden="true"></i> Anmelden';
					$retstr .= '</button>';
    		}

    		$retstr .= '</form>';
		}

		$retstr .= '</div>';
		return $retstr;
	}
}
