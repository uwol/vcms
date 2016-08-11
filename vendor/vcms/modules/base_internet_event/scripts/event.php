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

if(isset($_REQUEST['eventid'])){
	$id = $_REQUEST['eventid'];
}

if($id == ''){
	exit;
}


$stmt = $libDb->prepare('SELECT * FROM base_veranstaltung WHERE id=:id');
$stmt->bindValue(':id', $id);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);


if($row['intern'] && !$libAuth->isLoggedIn()){
	echo '<p>Für diese Veranstaltung ist eine <a href="index.php?pid=login_login">Anmeldung im Intranet</a> nötig.</p>';
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
	echo '<div class="h-event">';
	echo '<h1 class="p-name">' .$row['titel']. '</h1>';
	echo '<div class="row">';

	// Caption-Box
	echo '<div class="col-sm-6 col-md-4 col-lg-3">';
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
	$colLg = 9;
	$colMd = 8;

	if($libEvent->isFacebookEvent($row)){
		$colLg = $colLg - 3;
		$colMd = $colMd - 4;
	}

	echo '<div class="col-sm-6 col-md-' .$colMd. ' col-lg-' .$colLg. '">';
	printEventDetails($row);
	printAnmeldungen($row);
	echo '</div>';


	// Facebook
	if($libEvent->isFacebookEvent($row)){
		echo '<div class="col-sm-12 col-md-4 col-lg-3">';
		echo '<div class="facebookEventPlugin" data-eventid="' .$row['id']. '"></div>';
		echo '</div>';
	}

	echo '</div>';

	printGallery($row);

	echo '</div>';

	// -----------------------------------------------------------

	function printEventDetails($row){
		global $libTime, $libEvent;

		/*
		* date and time
		*/
		echo '<time class="dt-start" datetime="' .$libTime->formatUtcString($row['datum']). '">';
		echo 'Am ' .$libTime->formatDateString($row['datum']);

		$timeString = $libTime->formatTimeString($row['datum']);

		if($timeString != ''){
			echo ' um ' .$timeString;
		}

		echo '</time>';

		/*
		* location
		*/
		if ($row['ort'] != ''){
			echo '<address>Ort: <span class="p-location">' .$row['ort']. '</span></address>';
		}

		/*
		* status
		*/
		$status = $libEvent->getStatusString($row['status']);
		echo '<p>Status: <span class="p-category">' .$status. '</span></p>';

		/*
		* description
		*/
		if($row['beschreibung'] != ''){
			echo '<h3>Beschreibung</h3>';
			echo '<p class="p-description">' .nl2br($row['beschreibung']). '</p>';
		}

		if($row['spruch'] != ''){
			echo '<h3>Spruch</h3>';
			echo '<p>' .nl2br($row['spruch']). '</p>';
		}
	}

	function printAnmeldungen($row){
		global $libAuth, $libDb, $libGallery, $libPerson;

		echo '<h3>Anmeldungen</h3>';

		if($libAuth->isLoggedin()){
			$stmt = $libDb->prepare("SELECT base_veranstaltung_teilnahme.person FROM base_veranstaltung_teilnahme, base_person WHERE base_veranstaltung_teilnahme.veranstaltung = :veranstaltung AND base_veranstaltung_teilnahme.person = base_person.id AND base_person.gruppe != 'T' ORDER BY base_person.name, base_person.vorname");
			$stmt->bindValue(':veranstaltung', $row['id'], PDO::PARAM_INT);
			$stmt->execute();

			$anmeldungWritten = false;

			echo '<p>';

			while($eventrow = $stmt->fetch(PDO::FETCH_ASSOC)){
				if($anmeldungWritten){
					echo ', ';
				}

				echo '<span class="p-attendee"><a href="index.php?pid=intranet_person_daten&personid=' .$eventrow['person']. '">' .$libPerson->getMitgliedNameString($eventrow['person'], 0). '</a></span>';
				$anmeldungWritten = true;
			}

			echo '</p>';
		} else {
			echo '<p>Für eine Liste der angemeldeten Bundesbrüder bitte <a href="index.php?pid=login_login">im Intranet anmelden</a>.</p>';
		}

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
		global $libEvent;

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
				echo '<form action="index.php?pid=semesterprogramm_event&amp;eventid=' .$row['id']. '" method="post" class="form-inline">';

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
}
?>