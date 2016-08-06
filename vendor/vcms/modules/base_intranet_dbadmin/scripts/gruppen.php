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
	if(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == 'create'){
		if(isset($_REQUEST['bezeichnung']) && $_REQUEST['bezeichnung'] != ''){
			$stmt = $libDb->prepare('INSERT INTO base_gruppe (bezeichnung, beschreibung) VALUES (:bezeichnung, :beschreibung)');
			$stmt->bindValue(':bezeichnung', $libString->protectXss($_REQUEST['bezeichnung']));
			$stmt->bindValue(':beschreibung', $libString->protectXss($_REQUEST['beschreibung']));
			$stmt->execute();
		} else {
			$libGlobal->errorTexts[] = 'Keine Gruppe angegeben.';
		}
	} elseif(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == 'delete'){
		if($_REQUEST['bezeichnung'] != '' && $_REQUEST['bezeichnung'] != 'F' && $_REQUEST['bezeichnung'] != 'B' && $_REQUEST['bezeichnung'] != 'P' && $_REQUEST['bezeichnung'] != 'C' && $_REQUEST['bezeichnung'] != 'X' && $_REQUEST['bezeichnung'] != 'T' && $_REQUEST['bezeichnung'] != 'G' && $_REQUEST['bezeichnung'] != 'W' && $_REQUEST['bezeichnung'] != 'V' && $_REQUEST['bezeichnung'] != 'Y'){
			$stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM base_person WHERE gruppe = :gruppe');
			$stmt->bindValue(':gruppe', $libString->protectXss($_REQUEST['bezeichnung']));
			$stmt->execute();
			$stmt->bindColumn('number', $anzahl);
			$stmt->fetch();

			//wird diese Gruppe noch in base_person benutzt?
			if($anzahl > 0){
				$libGlobal->errorTexts[] = 'Diese Gruppe wird von Mitgliedern verwendet.';
			} else {
				$stmt = $libDb->prepare('DELETE FROM base_gruppe WHERE bezeichnung = :bezeichnung');
				$stmt->bindValue(':bezeichnung', $_REQUEST['bezeichnung']);
				$stmt->execute();

				$libGlobal->notificationTexts[] = 'Gruppe gelöscht.';
			}
		} else {
			$libGlobal->errorTexts[] = 'Keine Gruppe angegeben.';
		}
	}

	echo '<h1>Gruppen</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo '<table class="table table-condensed table-striped table-hover">';
	echo '<thead>';
	echo '<tr><th>Bezeichnung</th><th>Beschreibung</th><th></th></tr>';
	echo '</thead>';

	$stmt = $libDb->prepare('SELECT * FROM base_gruppe');
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		echo '<tr>';
		echo '<td>' .$row['bezeichnung']. '</td>';
		echo '<td>' .$row['beschreibung']. '</td>';
		echo '<td class="toolColumn">';

		if($row['bezeichnung'] != 'F' && $row['bezeichnung'] != 'B' && $row['bezeichnung'] != 'P' && $row['bezeichnung'] != 'X' && $row['bezeichnung'] != 'T' && $row['bezeichnung'] != 'C' && $row['bezeichnung'] != 'G' && $row['bezeichnung'] != 'W' && $row['bezeichnung'] != 'V' && $row['bezeichnung'] != 'Y'){
			echo '<a href="index.php?pid=intranet_admin_db_gruppen&amp;aktion=delete&amp;bezeichnung=' .$row['bezeichnung']. '" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">';
			echo '<i class="fa fa-trash" aria-hidden="true"></i>';
			echo '</a>';
		}

		echo '</td>';
		echo '</tr>';
	}

	echo '</table>';

	echo '<h2>Neue Gruppe anlegen</h2>';

	echo '<form action="index.php?pid=intranet_admin_db_gruppen" method="post" class="form-horizontal">';
	echo '<fieldset>';
	echo '<input type="hidden" name="aktion" value="create" />';

	$libForm->printTextInput('bezeichnung', 'Bezeichnung (nur 1 Buchstabe)', '');
	$libForm->printTextInput('beschreibung', 'Beschreibung', '');
	$libForm->printSubmitButton('Anlegen');

	echo '</fieldset>';
	echo '</form>';
}
?>