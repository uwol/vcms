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

if($libAuth->isLoggedin()){
	/**
	* Löschvorgang durchführen
	*/
	if(isset($_GET['aktion']) && $_GET['aktion'] == "delete"){
		if(isset($_GET['id']) && $_GET['id'] != ""){
			//Verwendung der Veranstaltung in anderen Tabellen prüfen
			//diese Einträge vorher löschen

			//Veranstaltungsteilnahmen löschen
			$stmt = $libDb->prepare("DELETE FROM base_veranstaltung_teilnahme WHERE veranstaltung=:veranstaltung");
			$stmt->bindValue(':veranstaltung', $_REQUEST['id'], PDO::PARAM_INT);
			$stmt->execute();

			//Veranstaltung aus Datenbank löschen
			$stmt = $libDb->prepare("DELETE FROM base_veranstaltung WHERE id=:id");
			$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
			$stmt->execute();

			$libGlobal->notificationTexts[] = "Datensatz gelöscht.";
		}
	}

	echo "<h1>Veranstaltungen</h1>";

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	//neue Veranstaltung
	echo '<p><a href="index.php?pid=intranet_admin_db_veranstaltung&amp;aktion=blank">Eine neue Veranstaltung anlegen</a></p>';

	//Semesterauswahl
	$stmt = $libDb->prepare("SELECT DATE_FORMAT(datum,'%Y-%m-01') AS datum FROM base_veranstaltung GROUP BY datum ORDER BY datum DESC");
	$stmt->execute();

	$daten = array();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		$daten[] = $row['datum'];
	}

	echo $libTime->getSemesterMenu($libTime->getSemestersFromDates($daten),$libGlobal->semester);
	echo '<br />';

	//Datenausgeben
	echo '<table class="table table-condensed">';
	echo '<tr><th>Id</th><th>Datum</th><th>Titel</th><th>Status</th><th></th></tr>';

	$zeitraum = $libTime->getZeitraum($libGlobal->semester);

	$stmt = $libDb->prepare("SELECT * FROM base_veranstaltung WHERE datum = :datum OR (DATEDIFF(datum, :semester_start) >= 0 AND DATEDIFF(datum, :semester_ende) <= 0) ORDER BY datum DESC");
	$stmt->bindValue(':datum', $zeitraum[0]);
	$stmt->bindValue(':semester_start', $zeitraum[0]);
	$stmt->bindValue(':semester_ende', $zeitraum[1]);
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		echo '<tr>';
		echo '<td>' .$row['id']. '</td>';
		echo '<td>' .$row['datum']. '</td>';
		echo '<td>' .$row['titel']. '</td>';
		echo '<td>' .$row['status']. '</td>';
		echo '<td class="toolColumn">';
		echo '<a href="index.php?pid=intranet_admin_db_veranstaltung&amp;id=' .$row['id']. '">';
		echo '<img src="styles/icons/basic/edit.svg" alt="edit" class="icon_small" />';
		echo '</a>';
		echo '</td>';
		echo '</tr>';
	}

	echo "</table>";
}
?>