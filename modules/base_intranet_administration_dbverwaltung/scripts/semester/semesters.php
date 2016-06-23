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
		if(isset($_GET['semester']) && $_GET['semester'] != ""){
			$stmt = $libDb->prepare('SELECT internetwart FROM base_semester WHERE semester=:semester');
			$stmt->bindValue(':semester', $_REQUEST['semester']);
			$stmt->execute();
			$stmt->bindColumn('internetwart', $internetwart);
			$stmt->fetch();

			//ist im zu löschenden Semester kein Internetwart angegeben?
			if($internetwart == "" || $internetwart == 0){
				//aus Datenbank löschen
				$stmt = $libDb->prepare("DELETE FROM base_semester WHERE semester=:semester");
				$stmt->bindValue(':semester', $_REQUEST['semester']);
				$stmt->execute();

				$libGlobal->notificationTexts[] = "Datensatz gelöscht";

				//Semestercover löschen
				$libImage = new LibImage($libTime, $libGenericStorage);
				$libImage->deleteSemesterCover($_REQUEST['semester']);
			} else {
				$libGlobal->errorTexts[] = 'Das Semester kann nicht gelöscht werden, da es einen Internetwarteintrag enthält. Um das Semester zu löschen, muss erst von einem Internetwart der Internetwarteintrag aus dem Semester ausgetragen werden.';
			}
		}
	}

	echo '<h1>Semester</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo '<p><a href="index.php?pid=intranet_admin_db_semester&amp;aktion=blank">Ein neues Semester anlegen</a></p>';

	echo '<table class="table table-condensed">';
	echo '<tr><th>Semester</th><th>Senior</th><th>Fuchsmajor</th><th>Internetwart</th><th></th></tr>';

	$stmt = $libDb->prepare("SELECT * FROM base_semester ORDER BY SUBSTRING(semester,3) DESC");
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		echo '<tr>';
		echo '<td>' .$row['semester']. '</td>';
		echo '<td>' .$libMitglied->getMitgliedNameString($row['senior'],5). '</td>';
		echo '<td>' .$libMitglied->getMitgliedNameString($row['fuchsmajor'],5). '</td>';
		echo '<td>' .$libMitglied->getMitgliedNameString($row['internetwart'],5). '</td>';
		echo '<td class="toolColumn">';
		echo '<a href="index.php?pid=intranet_admin_db_semester&amp;semester=' .$row['semester']. '">';
		echo '<img src="styles/icons/basic/edit.svg" alt="edit" class="icon_small" />';
		echo '</a>';
		echo '</td>';
		echo '</tr>';
	}

	echo "</table>";
}
?>