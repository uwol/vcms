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
	$orderby = '';
	if(isset($_GET['orderby'])){
		$orderby = $_GET['orderby'];
	}


	/**
	* Löschvorgang durchführen
	*/
	if(isset($_GET['aktion']) && $_GET['aktion'] == "delete"){
		if(isset($_GET['id']) && $_GET['id'] != ""){
			//Ist der Bearbeiter kein Internetwart?
			if(!in_array("internetwart",$libAuth->getAemter())){
				die("Fehler: Diese Aktion darf nur von einem Internetwart ausgeführt werden.");
			}

			//Problemfall Internetwart: Dieser darf nie gelöscht werden, um immer einen Admin im System zu haben
			$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_semester WHERE internetwart=:internetwart");
			$stmt->bindValue(':internetwart', $_REQUEST['id'], PDO::PARAM_INT);
			$stmt->execute();
			$stmt->bindColumn('number', $anzahl);
			$stmt->fetch();

			if($anzahl > 0){
				$libGlobal->errorTexts[] = "Die Person kann nicht gelöscht werden, weil sie ein Internetwart in mindestens einem Semester ist. Internetwarte können nicht gelöscht werden, weil sie die Administratoren sind und im Extremfall somit kein Administrator im System existiert. Falls diese Person gelöscht werden soll, so muss sie erst manuell von einem Internetwart in allen Semestern aus den Internetwartsposten entfernt werden.";
			} else {
				//Verwendung der Person in anderen Tabellen prüfen
				//diese Einträge vorher löschen oder vom Mitglied befreien

				//Veranstaltungsteilnahmen löschen
				$stmt = $libDb->prepare("DELETE FROM base_veranstaltung_teilnahme WHERE person=:id");
				$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
				$stmt->execute();

				//Vereinsmitgliedschaften löschen
				$stmt = $libDb->prepare("DELETE FROM base_verein_mitgliedschaft WHERE mitglied=:id");
				$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
				$stmt->execute();

				//Semesterämter löschen
				foreach($libSecurityManager->getPossibleAemter() as $amt){
					$stmt = $libDb->prepare('UPDATE base_semester SET '.$amt.' = NULL WHERE '.$amt.'=:id');
					$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
					$stmt->execute();
				}

				//Leibvaterangaben entfernen
				$stmt = $libDb->prepare("UPDATE base_person SET leibmitglied = NULL WHERE leibmitglied=:id");
				$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
				$stmt->execute();

				//Ehepartnerangaben entfernen
				$stmt = $libDb->prepare("UPDATE base_person SET heirat_partner = NULL WHERE heirat_partner=:id");
				$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
				$stmt->execute();

				//Vitaautorangaben entfernen
				$stmt = $libDb->prepare("UPDATE base_person SET vita_letzterautor = NULL WHERE vita_letzterautor=:id");
				$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
				$stmt->execute();

				//Mitglied aus Datenbank löschen
				$stmt = $libDb->prepare("DELETE FROM base_person WHERE id=:id");
				$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
				$stmt->execute();

				$libGlobal->notificationTexts[] = "Datensatz gelöscht";

				//Fotodatei löschen
				$libImage = new LibImage($libTime, $libGenericStorage);
				$libImage->deletePersonFoto($_REQUEST['id']);
			}
		}
	}

	switch($orderby){
		case "name":
			$order = "name, vorname, datum_geburtstag ASC";
			$orderid = 1;
			break;
		case "reception":
			$order = "SUBSTRING(semester_reception,3) DESC";
			$orderid = 2;
			break;
		case "gruppe":
			$order = "gruppe, name, vorname ASC";
			$orderid = 3;
			break;
		case "id":
			$order = "id ASC";
			$orderid = 4;
			break;
		default:
			$order = "SUBSTRING(semester_reception,3) DESC";
			$orderid = 2;
	}

	echo "<h1>Personen</h1>";

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo "<p>Das Anlegen und Löschen von Personendatensätzen kann nur von einem Internetwart vorgenommen werden.</p>";

	//Ist der Bearbeiter ein Internetwart?
	if(in_array("internetwart",$libAuth->getAemter())){
		//Link zum Anlegen von Personen anzeigen
		echo '<p><a href="index.php?pid=intranet_admin_db_person&amp;aktion=blank">Eine neue Person anlegen</a></p>';
	}

 	echo '<p>Anordnen nach: ';

	if($orderid == 1){
		echo "<b>";
	}

	echo '<a href="index.php?pid=intranet_admin_db_personenliste&amp;orderby=name">Name</a>';

	if($orderid == 1){
		echo "</b>";
	}

	echo ' - ';

	if($orderid == 2){
		echo "<b>";
	}

	echo '<a href="index.php?pid=intranet_admin_db_personenliste&amp;orderby=reception">Receptionssemester</a>';

	if($orderid == 2){
		echo "</b>";
	}

	echo ' - ';

	if($orderid == 3){
		echo "<b>";
	}

	echo '<a href="index.php?pid=intranet_admin_db_personenliste&amp;orderby=gruppe">Gruppe</a>';

	if($orderid == 3){
		echo "</b>";
	}

	echo ' - ';

	if($orderid == 4){
		echo "<b>";
	}

	echo '<a href="index.php?pid=intranet_admin_db_personenliste&amp;orderby=id">Id</a>';

	if($orderid == 4){
		echo "</b>";
	}

	echo '</p>';

	echo '<table class="table table-condensed">';
	echo '<tr><th>Id</th><th>Präfix</th><th>Name</th><th>Suffix</th><th>Vorname</th><th>Gruppe</th><th>Status</th><th>Reception</th><th></th></tr>';

	$stmt = $libDb->prepare("SELECT * FROM base_person ORDER BY ".$order);
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		echo '<tr>';
		echo '<td>' .$row['id']. '</td>';
		echo '<td>' .$row['praefix']. '</td>';
		echo '<td>' .$row['name']. '</td>';
		echo '<td>' .$row['suffix']. '</td>';
		echo '<td>' .$row['vorname']. '</td>';
		echo '<td>' .$row['gruppe']. '</td>';
		echo '<td>' .$row['status']. '</td>';
		echo '<td>' .$row['semester_reception']. '</td>';
		echo '<td class="toolColumn">';
		echo '<a href="index.php?pid=intranet_admin_db_person&amp;id=' .$row['id']. '">';
		echo '<i class="fa fa-cog" aria-hidden="true"></i>';
		echo '</a>';
		echo '</td>';
		echo '</tr>';
	}

	echo '</table>';
}
?>