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
			// aus Datenbank löschen
			$stmt = $libDb->prepare("DELETE FROM base_vip WHERE id=:id");
			$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
			$stmt->execute();

			$libGlobal->notificationTexts[] = "Datensatz gelöscht.";
		}
	}

	echo "<h1>Vips</h1>";

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo '<p><a href="index.php?pid=intranet_admin_db_vip&amp;aktion=blank">Einen neuen Vip anlegen</a></p>';

	echo '<table class="table table-condensed">';
	echo '<tr><th>Id</th><th>Praefix</th><th>Name</th><th>Suffix</th><th>Vorname</th><th></th></tr>';

	$stmt = $libDb->prepare("SELECT * FROM base_vip ORDER BY name");
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		echo '<tr>';
		echo '<td>' .$row['id']. '</td>';
		echo '<td>' .$row['praefix']. '</td>';
		echo '<td>' .$row['name']. '</td>';
		echo '<td>' .$row['suffix']. '</td>';
		echo '<td>' .$row['vorname']. '</td>';
		echo '<td class="toolColumn">';
		echo '<a href="index.php?pid=intranet_admin_db_vip&amp;id=' .$row['id']. '">';
		echo '<img src="styles/icons/basic/edit.svg" alt="edit" class="icon_small" />';
		echo '</a>';
		echo '</td>';
		echo '</tr>';
	}

	echo '</table>';
}
?>