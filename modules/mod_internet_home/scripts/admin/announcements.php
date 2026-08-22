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
if(isset($_POST['action']) && $_POST['action'] == 'delete'){
	if(isset($_POST['id']) && $_POST['id'] != ''){
		$stmt = $libDb->prepare('DELETE FROM mod_internethome_nachricht WHERE id=:id');
		$stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
		$stmt->execute();

		$libGlobal->notificationTexts[] = 'Ankündigung gelöscht.';
		$libImage->deleteHomeImage($_POST['id']);
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
echo '<a href="index.php?pid=intranet_admin_announcement&amp;action=blank" class="btn btn-default">Eine neue Ankündigung anlegen</a>';
echo '</div>';
echo '</div>';
echo '</div>';


/*
* semester selection
*/
$stmt = $libDb->prepare("SELECT DATE_FORMAT(startdatum,'%Y-%m-01') AS datum FROM mod_internethome_nachricht WHERE startdatum IS NOT NULL GROUP BY startdatum ORDER BY startdatum DESC");
$stmt->execute();

$data = array();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$data[] = $row['datum'];
}

echo $libTime->getSemesterMenu($libTime->getSemestersFromDates($data), $libGlobal->semester);


echo '<table class="table table-condensed table-striped table-hover">';
echo '<thead>';
echo '<tr><th>Bild</th><th>Start</th><th>Text</th><th></th></tr>';
echo '</thead>';

$period = $libTime->getPeriod($libGlobal->semester);

$stmt = $libDb->prepare('SELECT * FROM mod_internethome_nachricht WHERE startdatum IS NULL OR startdatum = :startdatum_equal OR (DATEDIFF(startdatum, :startdatum) >= 0 AND DATEDIFF(startdatum , :enddatum) < 0) ORDER BY startdatum DESC');
$stmt->bindValue(':startdatum_equal', $period[0]);
$stmt->bindValue(':startdatum', $period[0]);
$stmt->bindValue(':enddatum', $period[1]);
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
	echo '<td>' .$row['text']. '</td>';
	echo '<td class="tool-column">';
	echo '<a href="index.php?pid=intranet_admin_announcement&amp;id=' .$row['id']. '">';
	echo '<i class="fa fa-cog" aria-hidden="true"></i>';
	echo '</a>';
	echo '</td>';
	echo '</tr>';
}

echo '</table>';
