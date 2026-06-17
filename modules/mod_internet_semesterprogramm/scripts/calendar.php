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

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

$semesterProgrammString = $libTime->getSemesterProgrammString($libGlobal->semester);
$semesterProgrammAvailable = $semesterProgrammString != '';

if($semesterProgrammAvailable){
	echo '<div class="row">';
	echo '<div class="card reveal">';
	echo '<div class="card-body">';
	echo '<div class="card">';

	echo '<div class="semestercover-box mx-auto" style="max-width: 350px;">';
	echo $semesterProgrammString;
	echo '</div>';

	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
}


echo '<div class="row">';
echo '<div class="col-12 col-sm-6">';

$stmt = $libDb->prepare('SELECT * FROM base_semester ORDER BY SUBSTRING(semester,3) DESC');
$stmt->execute();
$daten = array();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$daten[] = $row['semester'];
}

echo $libTime->getSemesterMenu($daten, $libGlobal->semester);

echo '</div>';

echo '<div class="col-12 col-sm-6">';
echo '<div class="card">';
echo '<div class="card-body">';
echo '<div class="btn-toolbar">';

echo '<span><p> Semesterprogramm abonnieren mit: </p></span>';
echo '<div class="btn-group" role="group"><a href="https://calendar.google.com/calendar/r?cid=webcal://' .$libGlobal->getSiteUrlAuthority(). '/api.php?iid=semesterprogramm_icalendar" class="btn btn-secondary btn-sm"><i class="fa fa-calendar" aria-hidden="true"></i> Google Calendar</a>';
echo '<a href="https://outlook.live.com/calendar/0/addfromweb?url=webcal://' .$libGlobal->getSiteUrlAuthority(). '/api.php?iid=semesterprogramm_icalendar" class="btn btn-secondary btn-sm" target="_blank" rel="noopener noreferrer"><i class="fa fa-calendar" aria-hidden="true"></i> Outlook (privat)</a>';
echo '<a href="https://outlook.office.com/calendar/0/addfromweb?url=webcal://' .$libGlobal->getSiteUrlAuthority(). '/api.php?iid=semesterprogramm_icalendar" class="btn btn-secondary btn-sm" target="_blank" rel="noopener noreferrer"><i class="fa fa-calendar" aria-hidden="true"></i> Outlook (business)</a>';
echo '  ';
echo '<a href="webcal://' .$libGlobal->getSiteUrlAuthority(). '/api.php?iid=semesterprogramm_icalendar" class="btn btn-secondary btn-sm"><i class="fa fa-calendar" aria-hidden="true"></i> Sonstige (per webcal/ics)</a></div>';

echo '<p><select onchange="if (this.value) window.location.href=this.value" class="form-select mt-3">';
echo '<option value="">Anleitungen:</option>';
echo '<option value="https://translate.google.com/translate?js=n&sl=en&tl=de&u=https://icsx5.bitfire.at/usage/">Android mit ICSx⁵</option>';
echo '<option value="https://f-droid.org/de/packages/at.bitfire.icsdroid/">Android ICSx⁵ Download (kostenlos)</option>';
echo '<option value="https://play.google.com/store/apps/details?id=at.bitfire.icsdroid&hl=de">Android ICSx⁵ Download (kostenpflichtig)</option>';
echo '<option value="https://support.apple.com/de-de/guide/iphone/iph3d1110d4/ios">Apple iPhone</option>';
echo '<option value="https://support.apple.com/de-de/guide/calendar/icl1022/mac">Apple Mac</option>';
echo '<option value="https://support.google.com/calendar/answer/37100?hl=de&co=GENIE.Platform%3DDesktop&sjid=16499913509868842564-EU&oco=1">Google Calendar</option>';
echo '<option value="https://support.microsoft.com/de-de/office/importieren-oder-abonnieren-eines-kalenders-in-outlook-com-oder-outlook-im-web-cff1429c-5af6-41ec-a5b4-74f2c278e98c#ID0EDD=Personal_account">Microsoft Outlook</option>';
echo '<option value="https://support.mozilla.org/de/kb/neue-kalender-erstellen/">Thunderbird</option>';
echo '</select></p>';

echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>';


echo '<div>';

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

	if($row['datum_ende'] != '' && $row['datum_ende'] != '1970-01-01 00:00:00'){
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
