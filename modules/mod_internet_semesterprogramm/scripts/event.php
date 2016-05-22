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

require($libModuleHandler->getModuleDirectory()."scripts/lib/gallery.class.php");
$libGallery = new LibGallery($libDb);


$stmt = $libDb->prepare("SELECT * FROM base_veranstaltung WHERE id=:id");
$stmt->bindValue(':id', $id);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);


if($libAuth->isLoggedIn()){
	if(isset($_POST["changeanmeldenstate"]) && $_POST["changeanmeldenstate"] != ""){
		// event in future?
		if(@date("Y-m-d H:i:s") < $row["datum"]){
			if($_POST["changeanmeldenstate"] == "anmelden"){
				$stmt = $libDb->prepare("INSERT IGNORE INTO base_veranstaltung_teilnahme (veranstaltung, person) VALUES (:veranstaltung, :person)");
				$stmt->bindValue(':veranstaltung', $row['id'], PDO::PARAM_INT);
				$stmt->bindValue(':person', $libAuth->getId(), PDO::PARAM_INT);
				$stmt->execute();
			} else {
				$stmt = $libDb->prepare("DELETE FROM base_veranstaltung_teilnahme WHERE veranstaltung=:veranstaltung AND person=:person");
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
echo '<div class="col-sm-9">';

/*
* date and time
*/
$time = substr($row['datum'], 11, 5);

// no time
if($time == '00:00'){
	$time = '';
} elseif(substr($time, 3, 2) == 00){
	$time = substr($time, 0, 2). 'h s.t.';
} elseif(substr($time, 3, 2) == 15){
	$time = substr($time, 0, 2). 'h c.t.';
}

$datum = $libTime->formatDateTimeString($row['datum'], 2);

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
echo '<p>';
echo '<b>Am ' .$datum. '</b>';

if($time != ''){
	echo '<br /><b> um ' .$time. '</b>';
}

if (($row['ort']) != ''){
	echo '<br /><b>Ort: '.$row['ort'] .'</b>';
}

echo '</p>';

echo '<p><b>Status:</b> ' .$status. '</p>';

if(($row['beschreibung']) != ''){
	echo '<p><b>Beschreibung:</b><br />' .nl2br($row['beschreibung']). '</p>';
}

if(($row['spruch']) != ''){
	echo '<p><b>Spruch:</b><br />' .nl2br($row['spruch']). '</p>';
}

/*
* show registrations
*/
echo '<p><b>Anmeldungen:</b><br />';

if($libAuth->isLoggedin()){
	$stmt = $libDb->prepare("SELECT base_veranstaltung_teilnahme.person FROM base_veranstaltung_teilnahme, base_person WHERE base_veranstaltung_teilnahme.veranstaltung = :veranstaltung AND base_veranstaltung_teilnahme.person = base_person.id AND base_person.gruppe != 'T' ORDER BY base_person.name, base_person.vorname");
	$stmt->bindValue(':veranstaltung', $id, PDO::PARAM_INT);
	$stmt->execute();

	$anmeldungWritten = false;

	while($eventrow = $stmt->fetch(PDO::FETCH_ASSOC)){
		if($anmeldungWritten){
			echo ', ';
		}

		echo '<a href="index.php?pid=intranet_person_daten&personid=' .$eventrow['person']. '">' .$libMitglied->getMitgliedNameString($eventrow['person'], 0) .'</a>';
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

echo '</div>';

//-------------------------------------- interactive box start ------------------------------------------

echo '<div class="col-sm-3">';
echo '<p>';

$semester = $libTime->getSemesterEinesDatums($row['datum']);
$url = 'http://' .$libConfig->sitePath. '/index.php?pid=semesterprogramm_event&amp;eventid=' .$id. '&amp;semester=' .$semester;
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

echo '<hr />';

/**
* registration status
*/
if($libAuth->isLoggedin()){
	$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_veranstaltung_teilnahme WHERE person=:person AND veranstaltung=:veranstaltung");
	$stmt->bindValue(':person', $libAuth->getId(), PDO::PARAM_INT);
	$stmt->bindValue(':veranstaltung', $row["id"], PDO::PARAM_INT);
	$stmt->execute();
	$stmt->bindColumn('number', $anzahl);
	$stmt->fetch();

    echo '<p>';

	if($anzahl > 0){
		echo '<img src="styles/icons/calendar/angemeldet.svg" alt="angemeldet" class="icon_small" /> angemeldet';
	} else {
		echo '<img src="styles/icons/calendar/abgemeldet.svg" alt="abgemeldet" class="icon_small" /> nicht angemeldet';
	}

	echo '</p>';

	//event in future?
	if(@date('Y-m-d H:i:s') < $row['datum']){
		echo '<form action="index.php?pid=semesterprogramm_event&amp;eventid=' .$row['id']. '" method="post" style="margin-top:5px;" >';

		if($anzahl > 0){
			echo '<input type="submit" value="Abmelden" name="Button" class="eventbutton" /><input type="hidden" name="changeanmeldenstate" value="abmelden" />';
		} else {
			echo '<input type="submit" value="Anmelden" name="Button" class="eventbutton" /><input type="hidden" name="changeanmeldenstate" value="anmelden" />';
		}

		echo '</form>';
	}
} else {
	echo '<p>Für den Anmeldestatus bitte <a href="index.php?pid=login_login">im Intranet anmelden</a>.</p>';
}

echo '</div>';

//-------------------------------------- interactive box end ------------------------------------------


echo '</div>';


$level = 0;
if($libAuth->isLoggedin()){
	$level = 1;
}

if($libGallery->hasPictures($row['id'], $level)){
	echo '<div class="row gallery">';

	$pictures = $libGallery->getPictures($row['id'], $level);

	foreach($pictures as $key => $value){
		echo '<div class="col-lg-3 col-md-4 col-xs-6">';
		echo '<div class="thumbBox">';

		echo '<a href="inc.php?iid=semesterprogramm_picture&amp;eventid=' .$row['id']. '&amp;pictureid='. $key .'">';

		$visibilityClass = '';

		if($libGallery->getPublicityLevel($value) == 1){
			$visibilityClass = "internal";
		}

		echo '<img src="inc.php?iid=semesterprogramm_picture&amp;eventid=' .$row['id']. '&amp;pictureid=' .$key. '&amp;thumb=1" alt="" class="thumbnail img-responsive center-block thumb ' .$visibilityClass. '" />';
		echo '</a>';
		echo '</div>';
		echo '</div>';
	}

	echo '</div>';
}
?>