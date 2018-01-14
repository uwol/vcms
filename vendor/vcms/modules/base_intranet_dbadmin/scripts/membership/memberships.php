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
		if(isset($_GET['verein']) && $_GET['verein'] != '' && isset($_GET['mitglied']) && $_GET['mitglied'] != ''){
			// Veranstaltung aus Datenbank löschen
			$stmt = $libDb->prepare('DELETE FROM base_verein_mitgliedschaft WHERE verein=:verein AND mitglied=:mitglied');
			$stmt->bindValue(':verein', $_REQUEST['verein'], PDO::PARAM_INT);
			$stmt->bindValue(':mitglied', $_REQUEST['mitglied'], PDO::PARAM_INT);
			$stmt->execute();

			$libGlobal->notificationTexts[] = 'Datensatz gelöscht.';
		}
	}

	echo '<h1>Vereinsmitgliedschaften</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();


	echo '<div class="panel panel-default">';
	echo '<div class="panel-body">';
	echo '<div class="btn-toolbar">';
	echo '<a href="index.php?pid=intranet_admin_membership&amp;aktion=blank" class="btn btn-default">Eine neue Vereinsmitgliedschaft anlegen</a>';
	echo '</div>';
	echo '</div>';
	echo '</div>';


	echo '<div class="panel panel-default">';
	echo '<div class="panel-body">';

	echo '<table class="table table-condensed table-striped table-hover">';
	echo '<thead>';
	echo '<tr><th>Verein</th><th>Mitglied</th><th></th></tr>';
	echo '</thead>';

	$stmt = $libDb->prepare('SELECT * FROM base_verein_mitgliedschaft,base_verein WHERE base_verein_mitgliedschaft.verein = base_verein.id ORDER BY base_verein.titel ASC');
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		echo '<tr>';
		echo '<td>' .$row['titel']. ' ' .$row['name']. '</td>';
		echo '<td>' .$libPerson->getNameString($row['mitglied'],7). '</td>';
		echo '<td class="tool-column">';
		echo '<a href="index.php?pid=intranet_admin_membership&amp;verein=' .$row['verein']. '&amp;mitglied=' .$row['mitglied']. '">';
		echo '<i class="fa fa-cog" aria-hidden="true"></i>';
		echo '</a>';
		echo '</td>';
		echo '</tr>';
	}

	echo '</table>';

	echo '</div>';
	echo '</div>';
}
