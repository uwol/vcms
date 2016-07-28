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
	if(isset($_POST['aktion']) && $_POST['aktion'] == 'create'){
		if($_POST['bezeichnung'] != ''){
			$stmt = $libDb->prepare('INSERT INTO base_region (bezeichnung) VALUES (:bezeichnung)');
			$stmt->bindValue(':bezeichnung', $libString->protectXss($_POST['bezeichnung']));
			$stmt->execute();
		} else {
			$libGlobal->errorTexts[] = 'Keine Bezeichnung angegeben.';
		}
	} elseif(isset($_GET['aktion']) && $_GET['aktion'] == 'delete'){
		if(isset($_GET['id']) && $_GET['id'] != ''){
			$stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM base_person WHERE region1 = :region OR region2 = :region');
			$stmt->bindValue(':region', $_GET['id'], PDO::PARAM_INT);
			$stmt->execute();
			$stmt->bindColumn('number', $anzahl);
			$stmt->fetch();

			//wird diese Region noch in base_person benutzt?
			if($anzahl > 0){
				$libGlobal->errorTexts[] = 'Diese Region ist bei Personen angegeben.';
			} else {
				$stmt = $libDb->prepare('DELETE FROM base_region WHERE id = :id');
				$stmt->bindValue(':id', $_GET['id'], PDO::PARAM_INT);
				$stmt->execute();

				$libGlobal->notificationTexts[] = 'Region gelöscht.';
			}
		} else {
			$libGlobal->errorTexts[] = 'Keine Region angegeben.';
		}
	}

	echo '<h1>Region</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo '<p>Die folgenden Regionen dienen der Einteilung der Mitglieder in Zirkel.</p>';

	echo '<table class="table table-condensed">';
	echo '<tr><th>Region</th><th>Anzahl Personen</th><th></th></tr>';

	$stmt = $libDb->prepare('SELECT bezeichnung,id FROM base_region');
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		$stmt2 = $libDb->prepare('SELECT COUNT(*) AS number FROM base_person WHERE region1 = :region OR region2 = :region');
		$stmt2->bindValue(':region', $row['id'], PDO::PARAM_INT);
		$stmt2->execute();
		$stmt2->bindColumn('number', $anzahl);
		$stmt2->fetch();

		echo '<tr>';
		echo '<td>' .$row['bezeichnung']. '</td>';
		echo '<td>' .$anzahl. ' Personen</td>';
		echo '<td class="toolColumn">';
		echo '<a href="index.php?pid=intranet_admin_db_region&amp;aktion=delete&amp;id=' .$row['id']. '" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">';
		echo '<i class="fa fa-trash" aria-hidden="true"></i>';
		echo '</a>';
		echo '</td>';
		echo '</tr>';
	}

	echo '</table>';

	echo '<h2>Neue Region anlegen</h2>';

	echo '<form action="index.php?pid=intranet_admin_db_region" method="post" class="form-horizontal">';
	echo '<fieldset>';
	echo '<input type="hidden" name="aktion" value="create" />';

	$libForm->printTextInput('bezeichnung', 'Bezeichnung', '');
	$libForm->printSubmitButton('Anlegen');

	echo '</fieldset>';
	echo '</form>';
}
?>