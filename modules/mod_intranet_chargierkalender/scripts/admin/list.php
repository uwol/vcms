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
if(isset($_GET['aktion']) && $_GET['aktion'] == "delete"){
	if(isset($_GET['id']) && $_GET['id'] != ""){
		//CASCADE deletion

		//delete event registrations
		$stmt = $libDb->prepare("DELETE FROM mod_chargierkalender_teilnahme WHERE chargierveranstaltung=:chargierveranstaltung");
		$stmt->bindValue(':chargierveranstaltung', $_REQUEST['id'], PDO::PARAM_INT);
		$stmt->execute();

		//delete event
		$stmt = $libDb->prepare("DELETE FROM mod_chargierkalender_veranstaltung WHERE id=:id");
		$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
		$stmt->execute();

		$libGlobal->notificationTexts[] = "Die Chargierveranstaltung wurde gelöscht.";
	}
}

/*
* output
*/

echo "<h1>Chargierveranstaltungen</h1>";

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

//new event
echo '<p><a href="index.php?pid=intranet_chargierkalender_adminveranstaltung&amp;aktion=blank">Eine neue Chargierveranstaltung anlegen</a></p>';

$stmt = $libDb->prepare("SELECT DATE_FORMAT(datum,'%Y-%m-01') AS datum FROM mod_chargierkalender_veranstaltung GROUP BY datum ORDER BY datum DESC");
$stmt->execute();

$daten = array();
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$daten[] = $row['datum'];
}

echo $libTime->getSemesterMenu($libTime->getSemestersFromDates($daten), $libGlobal->semester);
echo '<br />';

echo '<table class="table table-condensed">';
echo '<tr><th>Id</th><th>Verein</th><th>Beschreibung</th><th>Datum</th><th></th></tr>';

$zeitraum = $libTime->getZeitraum($libGlobal->semester);

$stmt = $libDb->prepare("SELECT * FROM mod_chargierkalender_veranstaltung WHERE datum = :datum_equal OR (DATEDIFF(datum, :semester_start) > 0 AND DATEDIFF(datum, :semester_ende) < 0) ORDER BY datum DESC");
$stmt->bindValue(':datum_equal', $zeitraum[0]);
$stmt->bindValue(':semester_start', $zeitraum[0]);
$stmt->bindValue(':semester_ende', $zeitraum[1]);
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<tr>';
	echo '<td>' .$row['id']. '</td>';
	echo '<td>' .$libVerein->getVereinNameString($row['verein']). '</td>';
	echo '<td>' .$row['beschreibung']. '</td>';
	echo '<td>' .$row['datum']. '</td>';
	echo '<td class="toolColumn">';
	echo '<a href="index.php?pid=intranet_chargierkalender_adminveranstaltung&amp;id=' .$row['id']. '">';
	echo '<i class="fa fa-cog" aria-hidden="true"></i>';
	echo '</a>';
	echo '</td>';
	echo '</tr>';
}

echo "</table>";
?>