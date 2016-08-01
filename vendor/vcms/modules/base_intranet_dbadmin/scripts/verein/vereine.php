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
	/**
	* Löschvorgang durchführen
	*/
	if(isset($_GET['aktion']) && $_GET['aktion'] == 'delete'){
		if(isset($_GET['id']) && $_GET['id'] != ''){
			// Verwendung der Veranstaltung in anderen Tabellen prüfen
			// diese Einträge vorher löschen, da kein InnoDB und somit kein CASCADE ALL
			// verwendet wird.

			// Vereinsmitgliedschaften löschen
			$stmt = $libDb->prepare('DELETE FROM base_verein_mitgliedschaft WHERE verein=:verein');
			$stmt->bindValue(':verein', $_REQUEST['id'], PDO::PARAM_INT);
			$stmt->execute();

			// falls der Verein ein Mutterverein oder Fusionsverein ist, die darauf verweisenden auf null setzen
			$stmt = $libDb->prepare('UPDATE base_verein SET mutterverein = NULL WHERE mutterverein=:mutterverein');
			$stmt->bindValue(':mutterverein', $_REQUEST['id'], PDO::PARAM_INT);
			$stmt->execute();

			$stmt = $libDb->prepare('UPDATE base_verein SET fusioniertin = NULL WHERE fusioniertin=:fusioniertin');
			$stmt->bindValue(':fusioniertin', $_REQUEST['id'], PDO::PARAM_INT);
			$stmt->execute();

			// Verein aus Datenbank löschen
			$stmt = $libDb->prepare('DELETE FROM base_verein WHERE id=:id');
			$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
			$stmt->execute();

			$libGlobal->notificationTexts[] = 'Datensatz gelöscht';
		}
	}

	echo '<h1>Vereine</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo '<p><a href="index.php?pid=intranet_admin_db_verein&amp;aktion=blank">Einen neuen Verein anlegen</a></p>';

	echo '<table class="table table-condensed table-striped table-hover">';
	echo '<thead>';
	echo '<tr><th>Id</th><th>Name</th><th>Dachverband</th><th>Ort</th><th></th></tr>';
	echo '</thead>';

	$stmt = $libDb->prepare('SELECT * FROM base_verein ORDER BY name');
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		echo '<tr>';
		echo '<td>' .$row['id']. '</td>';
		echo '<td>' .$row['name']. '</td>';
		echo '<td>' .$row['dachverband']. '</td>';
		echo '<td>' .$row['ort1']. '</td>';
		echo '<td class="toolColumn">';
		echo '<a href="index.php?pid=intranet_admin_db_verein&amp;id=' .$row['id']. '">';
		echo '<i class="fa fa-cog" aria-hidden="true"></i>';
		echo '</a>';
		echo '</td>';
		echo '</tr>';
	}

	echo '</table>';
}
?>