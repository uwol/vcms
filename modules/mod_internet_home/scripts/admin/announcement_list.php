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

/*
* actions
*/
if(isset($_GET['aktion']) && $_GET['aktion'] == "delete"){
	if(isset($_GET['id']) && $_GET['id'] != ""){
		$stmt = $libDb->prepare("DELETE FROM mod_internethome_nachricht WHERE id=:id");
		$stmt->bindValue(':id', $_GET['id'], PDO::PARAM_INT);
		$stmt->execute();

		$libGlobal->notificationTexts[] = 'Ankündigung gelöscht.';
		$libImage = new LibImage($libTime, $libGenericStorage);
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

echo '<p>Die drei aktuellsten und nicht verfallenen Nachrichten werden auf der Startseite angezeigt. Wenn sie verfallen oder neuere Nachrichten existieren, werden sie automatisch archiviert.</p>';

echo '<p><a href="index.php?pid=intranet_internethome_nachricht_adminankuendigung&amp;aktion=blank">Eine neue Ankündigung anlegen</a></p>';

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

echo '<br />';

echo '<table>';
echo '<tr><th>Bild</th><th>Zeitraum</th><th>Text</th><th></th></tr>';

$zeitraum = $libTime->getZeitraum($libGlobal->semester);

$stmt = $libDb->prepare("SELECT * FROM mod_internethome_nachricht WHERE startdatum = :startdatum_equal OR (DATEDIFF(startdatum, :startdatum) >= 0 AND DATEDIFF(startdatum , :enddatum) < 0) ORDER BY startdatum DESC");
$stmt->bindValue(':startdatum_equal', $zeitraum[0]);
$stmt->bindValue(':startdatum', $zeitraum[0]);
$stmt->bindValue(':enddatum', $zeitraum[1]);
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<tr>';
	echo '<td class="imgColumn">';
	$posssibleImage = $libModuleHandler->getModuleDirectory(). '/custom/bilder/' .$row['id']. '.jpg';

 	if(is_file($posssibleImage)){
 		echo '<img src="'.$posssibleImage.'" class="img-responsive center-block" alt="" />';
 	}

 	echo '</td>';
	echo '<td>' .$row['startdatum']. '<br />bis<br /> ' .$row['verfallsdatum']. '<br /><br /></td>';
	echo '<td>'.$libString->deleteBBCode($row['text']). '<br /><br /></td>';
	echo '<td class="toolColumn">';
	echo '<a href="index.php?pid=intranet_internethome_nachricht_adminankuendigung&amp;id=' .$row['id']. '">';
	echo '<img src="styles/icons/basic/edit.svg" alt="edit" class="icon_small" />';
	echo '</a>';
	echo '</td>';
	echo "</tr>";
}

echo "</table>";
?>