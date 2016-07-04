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
		if(isset($_GET['verein']) && $_GET['verein'] != "" && isset($_GET['mitglied']) && $_GET['mitglied'] != ""){
			// Veranstaltung aus Datenbank löschen
			$stmt = $libDb->prepare("DELETE FROM base_verein_mitgliedschaft WHERE verein=:verein AND mitglied=:mitglied");
			$stmt->bindValue(':verein', $_REQUEST['verein'], PDO::PARAM_INT);
			$stmt->bindValue(':mitglied', $_REQUEST['mitglied'], PDO::PARAM_INT);
			$stmt->execute();

			$libGlobal->notificationTexts[] = "Datensatz gelöscht.";
		}
	}

	echo "<h1>Vereinsmitgliedschaften</h1>";

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo '<p><a href="index.php?pid=intranet_admin_db_vereinsmitgliedschaft&amp;aktion=blank">Eine neue Vereinsmitgliedschaft anlegen</a></p>';

	echo '<table class="table table-condensed">';
	echo '<tr><th>Verein</th><th>Mitglied</th><th></th></tr>';

	$stmt = $libDb->prepare("SELECT * FROM base_verein_mitgliedschaft,base_verein WHERE base_verein_mitgliedschaft.verein = base_verein.id ORDER BY base_verein.titel ASC");
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		echo '<tr>';
		echo '<td>' .$row['titel']. ' ' .$row['name']. '</td>';
		echo '<td>' .$libMitglied->getMitgliedNameString($row['mitglied'],7). '</td>';
		echo '<td class="toolColumn">';
		echo '<a href="index.php?pid=intranet_admin_db_vereinsmitgliedschaft&amp;verein=' .$row['verein']. '&amp;mitglied=' .$row['mitglied']. '">';
		echo '<i class="fa fa-cog" aria-hidden="true"></i>';
		echo '</a>';
		echo '</td>';
		echo '</tr>';
	}

	echo '</table>';
}
?>