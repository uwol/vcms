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
	if(isset($_POST['aktion']) && $_POST['aktion'] == "create"){
		if($_POST['bezeichnung'] != ""){
			$stmt = $libDb->prepare("INSERT INTO base_status (bezeichnung, beschreibung) VALUES (:bezeichnung, :beschreibung)");
			$stmt->bindValue(':bezeichnung', $libString->protectXss($_POST['bezeichnung']));
			$stmt->bindValue(':beschreibung', $libString->protectXss($_POST['beschreibung']));
			$stmt->execute();
		} else {
			$libGlobal->errorTexts[] = "Keine Bezeichnung angegeben.";
		}
	} elseif(isset($_GET['aktion']) && $_GET['aktion'] == "delete"){
		if($_GET['bezeichnung'] != ""){
			$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person WHERE status = :status");
			$stmt->bindValue(':status', $_GET['bezeichnung']);
			$stmt->execute();
			$stmt->bindColumn('number', $anzahl);
			$stmt->fetch();

			//wird dieser Status noch in base_person benutzt?
			if($anzahl > 0){
				echo "Fehler: Dieser Status wird von Mitgliedern verwendet.";
			} else {
				$stmt = $libDb->prepare("DELETE FROM base_status WHERE bezeichnung = :bezeichnung");
				$stmt->bindValue(':bezeichnung', $_GET['bezeichnung']);
				$stmt->execute();

				$libGlobal->notificationTexts[] = 'Status gelöscht.';
			}
		} else {
			$libGlobal->errorTexts[] = "Kein Status angegeben.";
		}
	}

	echo '<h1>Status</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo '<p>Die folgenden Status dienen der weiteren Einteilung der Personen. Die Angabe eines Status bei einer Person hat keine Auswirkung auf die Zugangskontrolle für Seiten. Trotzdem sollten die standardmäßig vorhandenen Status nicht durch eigene ersetzt werden. Sinnvoll ist nur das Hinzufügen neuartiger Status.</p>';
	echo '<table>';
	echo '<tr><th>Bezeichnung</th><th>Beschreibung</th><th></th></tr>';

	$stmt = $libDb->prepare("SELECT * FROM base_status");
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		echo '<tr>';
		echo '<td>' .$row['bezeichnung']. '</td>';
		echo '<td>' .$row['beschreibung']. '</td>';

		if($row['bezeichnung'] != "A-Phil" && $row['bezeichnung'] != "B-Phil" && $row['bezeichnung'] != "Ehrenmitglied" && $row['bezeichnung'] != "ex loco" && $row['bezeichnung'] != "HV-M" && $row['bezeichnung'] != "Inaktiv ex loco" && $row['bezeichnung'] != "Inaktiv" && $row['bezeichnung'] != "VG"){
			echo '<td class="toolColumn">';
			echo '<a href="index.php?pid=intranet_admin_db_status&amp;aktion=delete&amp;bezeichnung=' .$row['bezeichnung']. '" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">';
			echo '<img src="styles/icons/basic/delete.svg" alt="delete" class="icon_small" />';
			echo '</a>';
			echo '</td>';
		}

		echo "</tr>";
	}

	echo "</table>";

	echo '<h2>Neuen Status anlegen</h2>';

	echo '<form action="index.php?pid=intranet_admin_db_status" method="post" class="form-horizontal">';
	echo '<fieldset>';
	echo '<input type="hidden" name="aktion" value="create" />';

	$libForm->printTextInput('bezeichnung', 'Bezeichnung (maximal 20 Buchstaben)', '');
	$libForm->printTextInput('beschreibung', 'Beschreibung', '');
	$libForm->printSubmitButton('Anlegen');

	echo '</fieldset>';
	echo '</form>';
}
?>