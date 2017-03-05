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
	$eventSchema = $libEvent->getEventSchema($row);

	echo '<script type="application/ld+json">';
	echo json_encode($eventSchema);
	echo '</script>';


	echo '<h1>' .$row['titel']. '</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo '<div class="row">';

	echo '<div class="col-sm-6 col-md-4 col-lg-3">';
	echo '<div class="panel panel-default">';
	echo '<div class="panel-body">';
	printEventDateTime($row);

	echo '<hr />';

	if ($row['ort'] != ''){
		echo '<address>';
		echo 'Ort: ' .$row['ort'];
		echo '</address>';
	}

	$status = $libEvent->getStatusString($row['status']);
	echo '<p>' .$status. '</p>';

	printAnmeldeStatus($row);
	printSocialButtons($row);
	echo '</div>';
	echo '</div>';
	echo '</div>';

	if($libEvent->isFacebookEvent($row)){
		printFacebookEvent($row);
	} else {
		printSemesterCover($row);
	}

	echo '</div>';

	printDescription($row);
	printSpruch($row);
	printAnmeldungen($row);
	printGallery($row);
}

// -----------------------------------------------------------

function printEventDateTime($row){
	global $libTime, $libEvent;

	/*
	* date and time
	*/
	echo '<div class="text-center">';
	echo '<h3 class="text-muted">';

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
	echo '<p>';

	if(!$libEvent->isFacebookEvent($row)){
		$libEvent->printFacebookShareButton($row['id']);
	}

	$libEvent->printTwitterShareButton($row['id']);

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
				$libForm->printSubmitButtonInline('<i class="fa fa-check-square-o" aria-hidden="true"></i> Abmelden');
			} else {
				echo '<input type="hidden" name="changeanmeldenstate" value="anmelden" />';
				$libForm->printSubmitButtonInline('<i class="fa fa-square-o" aria-hidden="true"></i> Anmelden');
			}

			echo '</form>';
		} else {
			if($angemeldet){
				echo '<i class="fa fa-check-square-o" aria-hidden="true"></i> angemeldet';
			} else {
				echo '<i class="fa fa-square-o" aria-hidden="true"></i> nicht angemeldet';
			}
		}
	}
}

function printSemesterCover($row){
	global $libTime, $libModuleHandler;

	$semester = $libTime->getSemesterNameAtDate($row['datum']);
	$semesterCoverString = $libTime->getSemesterCoverString($semester);

	if($semesterCoverString != ''){
		echo '<div class="col-sm-6 col-md-8 col-lg-offset-3 col-lg-6">';
		echo '<div class="semestercover-box center-block">';

		if($libModuleHandler->moduleIsAvailable('mod_internet_semesterprogramm')){
			echo '<a href="index.php?pid=semesterprogramm&amp;semester=' .$semester. '">';
			echo $semesterCoverString;
			echo '</a>';
		} else {
			echo $semesterCoverString;
		}

		echo '</div>';
		echo '</div>';
	}
}

function printFacebookEvent($row){
	echo '<div class="col-sm-6 col-md-4 col-lg-3">';
	echo '<div class="facebookEventPlugin" data-eventid="' .$row['id']. '"></div>';
	echo '</div>';
}

function printDescription($row){
	if($row['beschreibung'] != ''){
		echo '<hr />';
		echo '<p>' .nl2br($row['beschreibung']). '</p>';
	}
}

function printSpruch($row){
	if($row['spruch'] != ''){
		echo '<p>' .nl2br($row['spruch']). '</p>';
	}
}

function printAnmeldungen($row){
	global $libAuth, $libDb, $libGallery, $libPerson;

	if($libAuth->isLoggedin()){
		echo '<hr />';

		$stmt = $libDb->prepare("SELECT base_veranstaltung_teilnahme.person FROM base_veranstaltung_teilnahme, base_person WHERE base_veranstaltung_teilnahme.veranstaltung = :veranstaltung AND base_veranstaltung_teilnahme.person = base_person.id AND base_person.gruppe != 'T' ORDER BY base_person.name, base_person.vorname");
		$stmt->bindValue(':veranstaltung', $row['id'], PDO::PARAM_INT);
		$stmt->execute();

		$anmeldungWritten = false;

		echo '<p>';

		while($eventrow = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($anmeldungWritten){
				echo ', ';
			}

			echo '<span><a href="index.php?pid=intranet_person&id=' .$eventrow['person']. '">' .$libPerson->getNameString($eventrow['person'], 0). '</a></span>';
			$anmeldungWritten = true;
		}

		echo '</p>';
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
			echo '<div class="img-frame">';
			echo '<a href="api.php?iid=event_picture&amp;eventid=' .$row['id']. '&amp;id=' .$key. '">';
			echo '<img data-object-fit="cover" src="api.php?iid=event_picture&amp;eventid=' .$row['id']. '&amp;id=' .$key. '" alt="" />';
			echo '</a>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}

		echo '</div>';
	}
}
