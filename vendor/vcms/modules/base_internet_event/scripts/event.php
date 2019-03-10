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


/*
* actions
*/

$id = '';

if(isset($_REQUEST['id'])){
	$id = $_REQUEST['id'];
}

if($id == ''){
	exit;
}


$stmt = $libDb->prepare('SELECT * FROM base_veranstaltung WHERE id=:id');
$stmt->bindValue(':id', $id);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);


if($row['intern'] && !$libAuth->isLoggedIn()){
	echo '<p>Für diese Veranstaltung ist eine <a href="index.php?pid=login">Anmeldung im Intranet</a> nötig.</p>';
} else {
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
	$semester = $libTime->getSemesterNameAtDate($row['datum']);
	$pictures = getPictures($row['id']);
	$hasPictures = is_array($pictures);
	$eventSchema = $libEvent->getEventSchema($row);

	echo '<script type="application/ld+json">';
	echo json_encode($eventSchema);
	echo '</script>';

	echo '<h1>' .$row['titel']. '</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	$class = '';
	$style = '';

	if(!$hasPictures){
		$semesterCover = $libTime->determineSemesterCover($semester);

		if($semesterCover){
			$class = 'event-semestercover-box';
			$style = "background-image: url('custom/semestercover/" .$semesterCover. "')";
		}
	}

	echo '<div class="event">';
	echo '<div class="row ' .$class. '" style="' .$style. '">';

	// date and time panel
	echo '<div class="col-sm-4 col-lg-3">';
	echo '<div class="panel panel-default reveal">';
	echo '<div class="panel-body">';

	printEventDateTime($row);

	echo '<hr />';

	if($row['ort'] != ''){
		echo '<address>';
		echo '<i class="fa fa-fw fa-map-marker" aria-hidden="true"></i> ' .$row['ort'];
		echo '</address>';
	}

	$status = $libEvent->getStatusString($row['status']);

	if($status){
		echo '<div><i class="fa fa-fw fa-info" aria-hidden="true"></i> ' .$status. '</div>';
	}

	printAnmeldeStatus($row);
	printSocialButtons($row);

	echo '</div>';
	echo '</div>';
	echo '</div>';

	// description
	$descriptionText = printSpruch($row);
	$descriptionText .= printDescription($row);
	$descriptionText .= printAnmeldungen($row);

	if($hasPictures){
		if($descriptionText){
			echo '<div class="col-sm-8 col-lg-9">';
			echo '<div class="panel panel-default reveal">';
			echo '<div class="panel-body">';
			echo $descriptionText;
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}

		echo '<div class="col-sm-8 col-lg-9">';
		printGallery($row['id'], $pictures);
		echo '</div>';
	} else {
		if($libEvent->isFacebookEvent($row)){
			echo '<div class="col-sm-4 col-lg-3">';
			printFacebookEvent($row);
			echo '</div>';
		}

		if($descriptionText){
			if($libEvent->isFacebookEvent($row)){
				echo '<div class="col-sm-4 col-lg-6">';
			} else {
				echo '<div class="col-sm-8 col-lg-9">';
			}

			echo '<div class="panel panel-default reveal">';
			echo '<div class="panel-body">';
			echo $descriptionText;
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
	}

	echo '</div>';
	echo '</div>';
}

// -----------------------------------------------------------

function printEventDateTime($row){
	global $libTime, $libEvent;

	/*
	* date and time
	*/
	echo '<div class="text-center">';
	echo '<h3>';

	$monatName = $libTime->getMonth($libTime->formatMonthString($row['datum']));
	$monatNameSubstr = substr($monatName, 0, 3);

 	echo $monatNameSubstr;
	echo ' ';
	echo $libTime->formatYearString($row['datum']);
	echo '</h3>';

	echo '<h1><time datetime="' .$libTime->formatUtcString($row['datum']). '">' .$libTime->formatDayString($row['datum']). '.</time></h1>';

	$timeString = $libTime->formatTimeString($row['datum']);

	if($timeString != ''){
		echo '<h3>' .$timeString. '</h3>';
	}

	echo '</div>';
}

function printSocialButtons($row){
	global $libEvent;

	echo '<hr />';
	echo '<p class="social-buttons mb-0 mt-0">';

	if(!$libEvent->isFacebookEvent($row)){
		$libEvent->printFacebookShareButton($row['id']);
	}

	$libEvent->printTwitterShareButton($row['id']);
	$libEvent->printWhatsAppShareButton($row['id']);

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

		if(date('Y-m-d H:i:s') < $row['datum']){
			echo '<form action="index.php?pid=event&amp;id=' .$row['id']. '" method="post" class="form-inline">';

			if($angemeldet){
				echo '<input type="hidden" name="changeanmeldenstate" value="abmelden" />';
				$libForm->printSubmitButtonInline('<i class="fa fa-fw fa-check-square-o" aria-hidden="true"></i> Abmelden', array('btn-sm'));
			} else {
				echo '<input type="hidden" name="changeanmeldenstate" value="anmelden" />';
				$libForm->printSubmitButtonInline('<i class="fa fa-fw fa-square-o" aria-hidden="true"></i> Anmelden', array('btn-sm'));
			}

			echo '</form>';
		} else {
			if($angemeldet){
				echo '<i class="fa fa-fw fa-check-square-o" aria-hidden="true"></i> angemeldet';
			} else {
				echo '<i class="fa fa-fw fa-square-o" aria-hidden="true"></i> nicht angemeldet';
			}
		}
	}
}

function printFacebookEvent($row){
	echo '<div class="facebookEventPlugin" data-eventid="' .$row['id']. '"></div>';
}

function printDescription($row){
	if(trim($row['beschreibung'])){
		return '<p>' .nl2br($row['beschreibung']). '</p>';
	}
}

function printSpruch($row){
	if(trim($row['spruch'])){
		return '<p>' .nl2br($row['spruch']). '</p>';
	}
}

function printAnmeldungen($row){
	global $libAuth, $libDb, $libGallery, $libPerson;

	$retstr = '';

	if($libAuth->isLoggedin()){
		$stmt = $libDb->prepare("SELECT base_veranstaltung_teilnahme.person FROM base_veranstaltung_teilnahme, base_person WHERE base_veranstaltung_teilnahme.veranstaltung = :veranstaltung AND base_veranstaltung_teilnahme.person = base_person.id AND base_person.gruppe != 'T' ORDER BY base_person.name, base_person.vorname");
		$stmt->bindValue(':veranstaltung', $row['id'], PDO::PARAM_INT);
		$stmt->execute();

		$anmeldungWritten = false;

		$retstr .= '<p>';

		while($eventrow = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($anmeldungWritten){
				$retstr .= ', ';
			}

			$retstr .= '<span><a href="index.php?pid=intranet_person&id=' .$eventrow['person']. '">' .$libPerson->getNameString($eventrow['person'], 0). '</a></span>';
			$anmeldungWritten = true;
		}

		$retstr .= '</p>';
	}

	return $retstr;
}

function printGallery($id, $pictures){
	echo '<div class="row gallery">';

	foreach($pictures as $key => $value){
		echo '<div class="col-sm-6 col-lg-4">';
		echo '<div class="thumbnail reveal mb-2">';
		echo '<div class="img-frame">';
		echo '<a href="api.php?iid=event_picture&amp;eventid=' .$id. '&amp;id=' .$key. '">';
		echo '<img src="api.php?iid=event_picture&amp;eventid=' .$id. '&amp;id=' .$key. '" alt="" />';
		echo '</a>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}

	echo '</div>';
}

function getPictures($id){
	global $libAuth, $libGallery;

	$level = 0;

	if($libAuth->isLoggedin()){
		$level = 1;
	}

	if($libGallery->hasPictures($id, $level)){
		return $libGallery->getPictures($id, $level);
	}
}
