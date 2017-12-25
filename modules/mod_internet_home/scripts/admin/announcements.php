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
if(isset($_GET['aktion']) && $_GET['aktion'] == 'delete'){
	if(isset($_GET['id']) && $_GET['id'] != ''){
		$stmt = $libDb->prepare('DELETE FROM mod_internethome_nachricht WHERE id=:id');
		$stmt->bindValue(':id', $_GET['id'], PDO::PARAM_INT);
		$stmt->execute();

		$libGlobal->notificationTexts[] = 'Ankündigung gelöscht.';
		$libImage->deleteStartseitenBild($_REQUEST['id']);
	} else {
		$libGlobal->errorTexts[] = 'Keine Ankündigung angegeben.';
	}
}


/*
* output
*/

echo '<h1>Ankündigungen auf der Startseite</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();


echo '<div class="panel panel-default">';
echo '<div class="panel-body">';
echo '<div class="btn-toolbar">';
echo '<a href="index.php?pid=intranet_admin_announcement&amp;aktion=blank" class="btn btn-default">Eine neue Ankündigung anlegen</a>';
echo '</div>';
echo '</div>';
echo '</div>';


/*
* semester selection
*/
$stmt = $libDb->prepare("SELECT DATE_FORMAT(startdatum,'%Y-%m-01') AS datum FROM mod_internethome_nachricht GROUP BY startdatum ORDER BY startdatum DESC");
$stmt->execute();

$daten = array();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$daten[] = $row['datum'];
}

echo $libTime->getSemesterMenu($libTime->getSemestersFromDates($daten), $libGlobal->semester);


echo '<table class="table table-condensed table-striped table-hover">';
echo '<thead>';
echo '<tr><th>Bild</th><th>Start</th><th>Text</th><th></th></tr>';
echo '</thead>';

$zeitraum = $libTime->getZeitraum($libGlobal->semester);

$stmt = $libDb->prepare('SELECT * FROM mod_internethome_nachricht WHERE startdatum = :startdatum_equal OR (DATEDIFF(startdatum, :startdatum) >= 0 AND DATEDIFF(startdatum , :enddatum) < 0) ORDER BY startdatum DESC');
$stmt->bindValue(':startdatum_equal', $zeitraum[0]);
$stmt->bindValue(':startdatum', $zeitraum[0]);
$stmt->bindValue(':enddatum', $zeitraum[1]);
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<tr>';
	echo '<td class="img-column">';

	$posssibleImage = $libModuleHandler->getModuleDirectory(). '/custom/img/' .$row['id']. '.jpg';

 	if(is_file($posssibleImage)){
		echo '<a href="index.php?pid=intranet_admin_announcement&amp;id=' .$row['id']. '">';
 		echo '<img src="'.$posssibleImage.'" class="img-responsive center-block" alt="" />';
		echo '</a>';
 	}

 	echo '</td>';
	echo '<td>' .$row['startdatum']. '</td>';
	echo '<td>' .$libString->deleteBBCode($row['text']). '</td>';
	echo '<td class="tool-column">';
	echo '<a href="index.php?pid=intranet_admin_announcement&amp;id=' .$row['id']. '">';
	echo '<i class="fa fa-cog" aria-hidden="true"></i>';
	echo '</a>';
	echo '</td>';
	echo '</tr>';
}

echo '</table>';
