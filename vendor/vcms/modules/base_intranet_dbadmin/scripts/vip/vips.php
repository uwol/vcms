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


if($libAuth->isLoggedin()){
	if(isset($_GET['aktion']) && $_GET['aktion'] == 'delete'){
		if(isset($_GET['id']) && $_GET['id'] != ''){
			// aus Datenbank löschen
			$stmt = $libDb->prepare('DELETE FROM base_vip WHERE id=:id');
			$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
			$stmt->execute();

			$libGlobal->notificationTexts[] = 'Datensatz gelöscht.';
		}
	}

	echo '<h1>Vips</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();


	echo '<div class="panel panel-default">';
	echo '<div class="panel-body">';
	echo '<div class="btn-toolbar">';
	echo '<a href="index.php?pid=intranet_admin_vip&amp;aktion=blank" class="btn btn-default">Einen neuen Vip anlegen</a>';
	echo '</div>';
	echo '</div>';
	echo '</div>';


	echo '<table class="table table-condensed table-striped table-hover">';
	echo '<thead>';
	echo '<tr><th>Id</th><th>Praefix</th><th>Name</th><th>Suffix</th><th>Vorname</th><th></th></tr>';
	echo '</thead>';

	$stmt = $libDb->prepare('SELECT * FROM base_vip ORDER BY name');
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		echo '<tr>';
		echo '<td>' .$row['id']. '</td>';
		echo '<td>' .$row['praefix']. '</td>';
		echo '<td>' .$row['name']. '</td>';
		echo '<td>' .$row['suffix']. '</td>';
		echo '<td>' .$row['vorname']. '</td>';
		echo '<td class="tool-column">';
		echo '<a href="index.php?pid=intranet_admin_vip&amp;id=' .$row['id']. '">';
		echo '<i class="fa fa-cog" aria-hidden="true"></i>';
		echo '</a>';
		echo '</td>';
		echo '</tr>';
	}

	echo '</table>';
}
