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

if(!is_object($libGlobal))
	exit();


if(!$libGenericStorage->attributeExistsInCurrentModule('fbAccessToken')){
	$libGenericStorage->saveValueInCurrentModule('fbAccessToken', '');
}

/*
* actions
*/

$id = '';
if(isset($_REQUEST['eventid'])){
	$id = $_REQUEST['eventid'];
}

if($id == ''){
	exit;
}

require($libModuleHandler->getModuleDirectory(). '/scripts/lib/gallery.class.php');
$libGallery = new LibGallery($libDb);


$stmt = $libDb->prepare('SELECT * FROM base_veranstaltung WHERE id=:id');
$stmt->bindValue(':id', $id);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);


if($libAuth->isLoggedIn()){
	if(isset($_POST['changeanmeldenstate']) && $_POST['changeanmeldenstate'] != ''){
		// event in future?
		if(date('Y-m-d H:i:s') < $row['datum']){
			if($_POST['changeanmeldenstate'] == 'anmelden'){
				$stmt = $libDb->prepare('INSERT IGNORE INTO base_veranstaltung_teilnahme (veranstaltung, person) VALUES (:veranstaltung, :person)');
				$stmt->bindValue(':veranstaltung', $row['id'], PDO::PARAM_INT);
				$stmt->bindValue(':person', $libAuth->getId(), PDO::PARAM_INT);
				$stmt->execute();
			} else {
				$stmt = $libDb->prepare('DELETE FROM base_veranstaltung_teilnahme WHERE veranstaltung=:veranstaltung AND person=:person');
				$stmt->bindValue(':veranstaltung', $row['id'], PDO::PARAM_INT);
				$stmt->bindValue(':person', $libAuth->getId(), PDO::PARAM_INT);
				$stmt->execute();
			}
		}
	}
}



/*
* output
*/
echo '<h1>' .$row['titel']. '</h1>';

echo '<div class="row">';

// Caption-Box
echo '<div class="col-sm-3">';
echo '<div class="thumbnail">';
echo '<div class="thumbnailOverflow">';
printSemesterCover($row);
echo '</div>';
echo '<div class="caption">';
printAnmeldeStatus($row);
echo '<hr />';
printSocialButtons($row);
echo '</div>';
echo '</div>';
echo '</div>';


// Haupttext
$col = 9;
if(isFacebookEvent($row)){
	$col = $col - 3;
}

echo '<div class="col-sm-' .$col. '">';
printEventDetails($row);
printAnmeldungen($row);
echo '</div>';


// Facebook
if(isFacebookEvent($row)){
	echo '<div class="col-sm-3">';
	printFacebookEvent($row);
	echo '</div>';
}


echo '</div>';

printGallery($row);


// -----------------------------------------------------------

function printEventDetails($row){
	global $libTime;

	/*
	* date and time
	*/
	$date = $libTime->formatDateTimeString($row['datum'], 2);
	$time = $libTime->formatDateTimeString($row['datum'], 3);

	/*
	* status
	*/
	if ($row['status'] == 'o'){
		$status = 'offiziell';
	} elseif ($row['status'] == 'ho'){
		$status = 'hochoffiziell';
	} elseif($row['status'] == ''){
		$status = 'inoffiziell';
	} else {
		$status = $row['status'];
	}

	/*
	* general infos
	*/
	echo '<time>';
	echo 'Am ' .$date;

	if($time != ''){
		echo ' um ' .$time;
	}

	echo '</time>';

	if ($row['ort'] != ''){
		echo '<address>Ort: ' .$row['ort']. '</address>';
	}

	echo '<p>Status: ' .$status. '</p>';

	if($row['beschreibung'] != ''){
		echo '<h4>Beschreibung</h4>';
		echo '<p>' .nl2br($row['beschreibung']). '</p>';
	}

	if($row['spruch'] != ''){
		echo '<h4>Spruch</h4>';
		echo '<p>' .nl2br($row['spruch']). '</p>';
	}
}

function printAnmeldungen($row){
	global $libAuth, $libDb, $libGallery, $libMitglied;

	echo '<h4>Anmeldungen</h4>';
	echo '<p>';

	if($libAuth->isLoggedin()){
		$stmt = $libDb->prepare("SELECT base_veranstaltung_teilnahme.person FROM base_veranstaltung_teilnahme, base_person WHERE base_veranstaltung_teilnahme.veranstaltung = :veranstaltung AND base_veranstaltung_teilnahme.person = base_person.id AND base_person.gruppe != 'T' ORDER BY base_person.name, base_person.vorname");
		$stmt->bindValue(':veranstaltung', $row['id'], PDO::PARAM_INT);
		$stmt->execute();

		$anmeldungWritten = false;

		while($eventrow = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($anmeldungWritten){
				echo ', ';
			}

			echo '<a href="index.php?pid=intranet_person_daten&personid=' .$eventrow['person']. '">' .$libMitglied->getMitgliedNameString($eventrow['person'], 0). '</a>';
			$anmeldungWritten = true;
		}
	} else {
		echo 'Für eine Liste der angemeldeten Bundesbrüder bitte <a href="index.php?pid=login_login">im Intranet anmelden</a>.';
	}

	echo '</p>';

	/*
	* gallery
	*/
	if(!$libAuth->isLoggedin()){
		// are there images?
		if(count($libGallery->getPictures($row['id'], 1)) > count($libGallery->getPictures($row['id'], 0))){
			echo '<p>Teile der Galerie sind ebenfalls erst nach einer Anmeldung im Intranet sichtbar.</p>';
		}
	}
}

function printSemesterCover($row){
	global $libTime;

	$semester = $libTime->getSemesterNameAtDate($row['datum']);
	$semesterCoverString = $libTime->getSemesterCoverString($semester);

	if($semesterCoverString != ''){
		echo '<div class="semestercoverBox center-block">';
		echo '<a href="index.php?pid=semesterprogramm_calendar&amp;semester=' .$semester. '">';
		echo $semesterCoverString;
		echo '</a>';
		echo '</div>';
	}
}

function printSocialButtons($row){
	global $libConfig, $libTime;

	echo '<p>';

	$semester = $libTime->getSemesterNameAtDate($row['datum']);
	$url = 'http://' .$libConfig->sitePath. '/index.php?pid=semesterprogramm_event&amp;eventid=' .$row['id']. '&amp;semester=' .$semester;
	$title = $libConfig->verbindungName. ' - ' .$row['titel']. ' am ' .$libTime->formatDateTimeString($row['datum'], 2);

	//facebook
	echo '<a href="http://www.facebook.com/share.php?u=' .urlencode($url). '&amp;t=' .urlencode($title). '" rel="nofollow">';
	echo '<img src="styles/icons/social/facebook.svg" alt="FB" class="icon" />';
	echo '</a> ';

	//twitter
	echo '<a href="http://twitter.com/share?url=' .urlencode($url). '&amp;text=' .urlencode($title). '" rel="nofollow">';
	echo '<img src="styles/icons/social/twitter.svg" alt="T" class="icon" />';
	echo '</a> ';

	echo '</p>';
}

function printAnmeldeStatus($row){
	global $libAuth, $libDb, $libForm;

	if($libAuth->isLoggedin()){
		$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_veranstaltung_teilnahme WHERE person=:person AND veranstaltung=:veranstaltung");
		$stmt->bindValue(':person', $libAuth->getId(), PDO::PARAM_INT);
		$stmt->bindValue(':veranstaltung', $row["id"], PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('number', $angemeldet);
		$stmt->fetch();

		echo '<p>';

		if($angemeldet){
			echo '<img src="styles/icons/calendar/attending.svg" alt="angemeldet" class="icon_small" /> angemeldet';
		} else {
			echo '<img src="styles/icons/calendar/notattending.svg" alt="abgemeldet" class="icon_small" /> nicht angemeldet';
		}

		echo '</p>';

		if(date('Y-m-d H:i:s') < $row['datum']){
			echo '<form action="index.php?pid=semesterprogramm_event&amp;eventid=' .$row['id']. '" method="post" class="form-inline">';

			if($angemeldet){
				echo '<input type="hidden" name="changeanmeldenstate" value="abmelden" />';
				$libForm->printSubmitButtonInline('Abmelden');
			} else {
				echo '<input type="hidden" name="changeanmeldenstate" value="anmelden" />';
				$libForm->printSubmitButtonInline('Anmelden');
			}

			echo '</form>';
		}
	} else {
		echo '<p>Für den Anmeldestatus bitte <a href="index.php?pid=login_login">im Intranet anmelden</a>.</p>';
	}
}

function printGallery($row){
	global $libAuth, $libGallery;

	$level = 0;
	if($libAuth->isLoggedin()){
		$level = 1;
	}

	if($libGallery->hasPictures($row['id'], $level)){
		echo '<hr />';
		echo '<div class="row gallery">';

		$pictures = $libGallery->getPictures($row['id'], $level);

		foreach($pictures as $key => $value){
			echo '<div class="col-sm-6 col-md-4 col-lg-3">';
			echo '<div class="thumbnail">';
			echo '<div class="thumbnailOverflow">';
			echo '<a href="inc.php?iid=semesterprogramm_picture&amp;eventid=' .$row['id']. '&amp;pictureid='. $key .'">';
			echo '<img src="inc.php?iid=semesterprogramm_picture&amp;eventid=' .$row['id']. '&amp;pictureid=' .$key. '" alt="" class="img-responsive center-block" />';
			echo '</a>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}

		echo '</div>';
	}
}

function printFacebookEvent($row){
	global $libGenericStorage, $libString, $libTime;

	if(isFacebookEvent($row)){
		$fbAccessToken = $libGenericStorage->loadValueInCurrentModule('fbAccessToken');
		$fbEventId = $row['fb_eventid'];

		$fbUrl = 'https://www.facebook.com';
		$fbGraphUrl = 'https://graph.facebook.com';
		$fbAccessTokenQuery = '?access_token=' .$fbAccessToken;
		
		$fbEventEndpoint = $fbGraphUrl. '/' .$fbEventId.$fbAccessTokenQuery;
		$fbEventPhotosEndpoint = $fbGraphUrl. '/' .$fbEventId. '/photos' .$fbAccessTokenQuery;
		$fbEventInterestedEndpoint = $fbGraphUrl. '/' .$fbEventId. '/interested' .$fbAccessTokenQuery. '&summary=count';
		$fbEventAttendingEndpoint = $fbGraphUrl. '/' .$fbEventId. '/attending' .$fbAccessTokenQuery. '&summary=count';

		$eventLink = $fbUrl. '/events/' .$fbEventId;

		$eventJson = file_get_contents($fbEventEndpoint);
		$eventObject = json_decode($eventJson, true);
		$eventObjectName = $eventObject['name'];
		$eventObjectDescription = $eventObject['description'];
		$eventObjectDescriptionTruncated = $libString->truncate($eventObjectDescription, 100);

		$eventPhotosJson = file_get_contents($fbEventPhotosEndpoint);
		$eventPhotosObject = json_decode($eventPhotosJson, true);
		$eventPhotoSource = $eventPhotosObject['data'][0]['source'];

		$eventInterestedJson = file_get_contents($fbEventInterestedEndpoint);
		$eventInterestedObject = json_decode($eventInterestedJson, true);
		$eventInterestedCount = $eventInterestedObject['summary']['count'];

		$eventAttendingJson = file_get_contents($fbEventAttendingEndpoint);
		$eventAttendingObject = json_decode($eventAttendingJson, true);
		$eventAttendingCount = $eventAttendingObject['summary']['count'];

		echo '<div class="thumbnail">';

		echo '<div class="thumbnailOverflow">';
		echo '<a href="' .$eventLink. '">';
		echo '<img src="' .$eventPhotoSource. '" alt="" class="img-responsive center-block" />';
		echo '</a>';
		echo '</div>';

		echo '<div class="caption">';
		echo '<div class="media">';

		echo '<div class="media-left" style="text-align:center">';
		echo '<span style="font-size:32px;line-height:32px">' .substr($row['datum'], 8, 2). '</span><br />';
		$monatName = $libTime->getMonth((int) substr($row['datum'], 5, 2));
		$monatNameSubstr = substr($monatName, 0, 3);
		$monatNameUpper = strtoupper($monatNameSubstr);
		echo '<span style="font-size:12px;line-height:12px;color:#e34e60">' .$monatNameUpper. '</span>';
		echo '</div>';

		echo '<div class="media-body">';

		echo '<h4 style="font-weight:bold;margin-top:0;margin-bottom:0;font-size:14px">';
		echo '<a href="' .$eventLink. '" style="color:black">';
		echo $libString->protectXss($eventObjectName);
		echo '</a>';
		echo '</h4>';

		echo '<p style="color:#90949c;margin-top:0;margin-bottom:0;font-size:12px">';
		echo $eventInterestedCount. ' Personen sind interessiert';
		echo ' · ';
		echo $eventAttendingCount. ' Personen nehmen teil';
		echo '</p>';

		echo '</div>';

		echo '</div>';
		echo '</div>';
		echo '</div>';
	}
}

function isFacebookEvent($row){
	global $libGenericStorage;

	$fbAccessToken = $libGenericStorage->loadValueInCurrentModule('fbAccessToken');
	$result = isset($row['fb_eventid']) && is_numeric($row['fb_eventid'])
		&& ini_get('allow_url_fopen') && $fbAccessToken != '';
	return $result;
}
?>