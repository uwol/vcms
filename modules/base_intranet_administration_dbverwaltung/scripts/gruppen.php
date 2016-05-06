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
	if(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == "create"){
		if(isset($_REQUEST['bezeichnung']) && $_REQUEST['bezeichnung'] != ""){
			$stmt = $libDb->prepare("INSERT INTO base_gruppe (bezeichnung, beschreibung) VALUES (:bezeichnung, :beschreibung)");
			$stmt->bindValue(':bezeichnung', $libString->protectXss($_REQUEST['bezeichnung']));
			$stmt->bindValue(':beschreibung', $libString->protectXss($_REQUEST['beschreibung']));
			$stmt->execute();
		} else {
			$libGlobal->errorTexts[] = "Keine Gruppe angegeben.";
		}
	} elseif(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == "delete"){
		if($_REQUEST['bezeichnung'] != "" && $_REQUEST['bezeichnung'] != "F" && $_REQUEST['bezeichnung'] != "B" && $_REQUEST['bezeichnung'] != "P" && $_REQUEST['bezeichnung'] != "C" && $_REQUEST['bezeichnung'] != "X" && $_REQUEST['bezeichnung'] != "T" && $_REQUEST['bezeichnung'] != "G" && $_REQUEST['bezeichnung'] != "W" && $_REQUEST['bezeichnung'] != "V" && $_REQUEST['bezeichnung'] != "Y"){
			$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person WHERE gruppe = :gruppe");
			$stmt->bindValue(':gruppe', $libString->protectXss($_REQUEST['bezeichnung']));
			$stmt->execute();
			$stmt->bindColumn('number', $anzahl);
			$stmt->fetch();

			//wird diese Gruppe noch in base_person benutzt?
			if($anzahl > 0){
				$libGlobal->errorTexts[] = "Diese Gruppe wird von Mitgliedern verwendet.";
			} else {
				$stmt = $libDb->prepare("DELETE FROM base_gruppe WHERE bezeichnung = :bezeichnung");
				$stmt->bindValue(':bezeichnung', $_REQUEST['bezeichnung']);
				$stmt->execute();

				$libGlobal->notificationTexts[] = 'Gruppe gelöscht.';
			}
		} else {
			$libGlobal->errorTexts[] = "Keine Gruppe angegeben.";
		}
	}

	echo '<h1>Gruppen</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo '<p>Die folgenden Gruppen dienen der Einteilung der Personen in Vereinsgruppen wie Füchse und Burschen. Jeder Person kann eine Gruppe auf der Personenverwaltungsseite eingetragen werden. Dabei ist zu beachten, dass die Gruppeneinträge der Personen als Zugangskritrium zu den Intranetseiten etc. genutzt werden. Falls also z. B. eine Seite nur für die Gruppe B (Burschen) freigegeben ist, und die Burschen des Vereins nicht in der Gruppe B sind, so wird der Zugang verwehrt. Deshalb sollten auch Damenverbindungen den Eintrag auf B lassen. In den seltensten Fällen sind zusätzliche Gruppen sinnvoll, sie verkomplizieren nur die Zugangskontrolle und die Konfiguration von Modulen.</p>';

	echo '<p>Eine Einteilung der Mitglieder in Vorstandsmitglieder, Warte etc. wird nicht über Gruppen, sondern über die Semestertabelle vorgenommen.</p>';

	echo '<table>';
	echo '<tr><th style="width:10%">Bezeichnung</th><th style="width:80%">Beschreibung</th><th style="width:10%">Aktion</th></tr>';

	$stmt = $libDb->prepare("SELECT * FROM base_gruppe");
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		echo '<tr>';
		echo '<td>' .$row['bezeichnung']. '</td>';
		echo '<td>' .$row['beschreibung']. '</td>';

		if($row['bezeichnung'] != "F" && $row['bezeichnung'] != "B" && $row['bezeichnung'] != "P" && $row['bezeichnung'] != "X" && $row['bezeichnung'] != "T" && $row['bezeichnung'] != "C" && $row['bezeichnung'] != "G" && $row['bezeichnung'] != "W" && $row['bezeichnung'] != "V" && $row['bezeichnung'] != "Y"){
			echo '<td><a href="index.php?pid=intranet_admin_db_gruppen&amp;aktion=delete&amp;bezeichnung=' .$row['bezeichnung']. '" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">Löschen</a></td>';
		}

		echo "</tr>";
	}

	echo "</table>";

	echo '<h2>Neue Gruppe anlegen</h2>';
	echo '<form action="index.php?pid=intranet_admin_db_gruppen" method="post">';
	echo '<input type="hidden" name="aktion" value="create" />';
	echo '<input type="text" name="bezeichnung" size="1" /> Bezeichnung (nur 1 Buchstabe)<br />';
	echo '<input type="text" name="beschreibung" size="30" /> Beschreibung<br />';
	echo '<input type="submit" value="Anlegen" />';
	echo '</form>';
}
?>