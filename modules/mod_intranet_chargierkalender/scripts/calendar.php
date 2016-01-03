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

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();

?>
<h1>Chargierkalender <?php echo $libTime->getSemesterString($libGlobal->semester); ?></h1>
<p>Hier kannst Du zu Chargierveranstaltungen des Semesters eine feste Zusage geben.</p>
<?php
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

echo '<br />';

$zeitraum = $libTime->getZeitraum($libGlobal->semester);
$calendar = new LibCalendar($zeitraum[0], $zeitraum[1]);

$stmt = $libDb->prepare("SELECT * FROM mod_chargierkalender_veranstaltung WHERE DATEDIFF(datum, :semester_start) > 0 AND DATEDIFF(datum, :semester_ende) < 0 ORDER BY datum");
$stmt->bindValue(':semester_start', $zeitraum[0]);
$stmt->bindValue(':semester_ende', $zeitraum[1]);
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	//build event
	$event = new LibChargierKalenderEvent($row['datum']);
	$event->setId($row['id']);

	if(is_numeric($row['verein'])){
		$event->setLinkUrl('index.php?pid=dachverband_vereindetail&amp;verein='.$row['verein']);
		$event->setSummary($libVerein->getVereinNameString($row['verein']));
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
		$mitglieder[$row2['mitglied']] = $libMitglied->getMitgliedNameString($row2['mitglied'], 8);
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

echo $calendar->toString(true);

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
	var $timeStyle;

	var $angemeldet;
	var $anmeldeButtonEnabled;
	var $angemeldeteMitglieder;

	//-------------------------------------------------

	function LibChargierKalenderEvent($startDateTime){
		$this->startDateTime = $startDateTime;
		$this->angemeldeteMitglieder = array();
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
		return substr($dateTime,0,10);
	}

	function getTimeOfDateTime($dateTime){
		return substr($dateTime,11,5);
	}

	function toString(){
		global $libString, $libGlobal;
		$retstr = '';

		//format timeString
		$timeString = '';
		if(!$this->allDay){ //event with timeinfo?
			$timeString = $this->getTimeOfDateTime($this->startDateTime);

			if($this->timeStyle == 1){ //s.t./c.t. ?
				if(substr($timeString, 3, 2) == 00){
					$timeString = substr($timeString, 0, 2)."h s.t.";
				} elseif(substr($timeString, 3, 2) == 15){
					$timeString = substr($timeString, 0, 2)."h c.t.";
				}
			}
		}

		//format dateTime for microformats
		$dtstart = substr($this->startDateTime, 0, 4) . substr($this->startDateTime, 5, 2) . substr($this->startDateTime, 8, 2);

		if(!$this->allDay){
			$dtstart .= "T". substr($this->startDateTime, 11, 2) . substr($this->startDateTime, 14, 2) . substr($this->startDateTime, 17, 2);
		}

		/*
		* print event
		*/
		//header
		$retstr .= '<div class="vevent" style="text-align:center;">';
		$retstr .= '<abbr class="dtstart" title="' .$dtstart. '"><b>'.$timeString.'</b></abbr><br />';

		//link
		if($this->linkUrl != ''){
			$retstr .= '<a href="' .$this->linkUrl. '">';
		}

		//summary
		if($this->summary != ''){
			$retstr .= '<span class="summary">';
			$retstr .= $libString->silbentrennung($this->summary, 14);
			$retstr .= '</span>';
		}

		if($this->linkUrl != ''){
			$retstr .= '</a>';
		}

		if($this->summary != ''){
			$retstr .= '<br /><br />';
		}

		//description
		if($this->description != ''){
			$retstr .= $libString->silbentrennung($this->description, 14).'<br />';
		}

		//location
		if($this->location != ''){
			$retstr .= '<span class="location">' . $libString->silbentrennung($this->location, 14) . '</span><br />';
		}

		if(count($this->angemeldeteMitglieder) > 0){
			$retstr .= '<br />';
			$mitgliederLinks = array();

			foreach($this->angemeldeteMitglieder as $key => $value){
				$mitgliederLinks[] = '<a href="index.php?pid=intranet_person_daten&amp;personid='.$key.'">'.$libString->silbentrennung($value, 14).'</a>';
			}

			$retstr .= implode(', ', $mitgliederLinks);
			$retstr .= '<br />';
		}

		//registration
		if($this->anmeldeButtonEnabled){
			$retstr .= '<form action="index.php?pid=intranet_chargierkalender_kalender" method="post">';
			$retstr .= '<input type="hidden" name="eventid" value="' .$this->id. '" />';
			$retstr .= '<input type="hidden" name="semester" value="' .$libGlobal->semester. '" />';

    		if($this->angemeldet){
    			$retstr .= '<input type="hidden" name="changeanmeldenstate" value="abmelden" /><input type="submit" value="Abmelden" style="padding:0px;color:green;" />';
   			} else {
    			$retstr .= '<input type="hidden" name="changeanmeldenstate" value="anmelden" /><input type="submit" value="Anmelden" style="padding:0px;color:red;" />';
    		}

    		$retstr .= '</form>';
		}

		//footer
		$retstr .= "</div><br />";
		return $retstr;
	}
}
?>