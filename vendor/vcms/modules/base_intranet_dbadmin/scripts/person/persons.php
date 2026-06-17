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
	$orderby = 0;

	if(isset($_POST['orderby'])){
		$orderby = $_POST['orderby'];
		echo '<script>if (window.history.replaceState) { window.history.replaceState(null, null, window.location.href); }</script>';
	}

	if(isset($_GET['aktion']) && $_GET['aktion'] == 'delete'){
		if(isset($_GET['id']) && $_GET['id'] != ''){
			//Ist der Bearbeiter kein Internetwart?
			if(!in_array('internetwart', $libAuth->getAemter()) && !in_array('datenpflegewart', $libAuth->getAemter())){
				die('Diese Aktion darf nur von einem Internetwart ausgeführt werden.');
			}

			//Problemfall Internetwart: Dieser darf nie gelöscht werden, um immer einen Admin im System zu haben
			$stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM base_semester WHERE internetwart=:internetwart');
			$stmt->bindValue(':internetwart', $_REQUEST['id'], PDO::PARAM_INT);
			$stmt->execute();
			$stmt->bindColumn('number', $anzahl);
			$stmt->fetch();

			if($anzahl > 0){
				$libGlobal->errorTexts[] = 'Die Person kann nicht gelöscht werden, weil sie ein Internetwart in mindestens einem Semester ist. Internetwarte können nicht gelöscht werden, weil sie die Administratoren sind und im Extremfall somit kein Administrator im System existiert. Falls diese Person gelöscht werden soll, so muss sie erst manuell von einem Internetwart in allen Semestern aus den Internetwartsposten entfernt werden.';
			} else {
				//Verwendung der Person in anderen Tabellen prüfen
				//diese Einträge vorher löschen oder vom Mitglied befreien

				//Veranstaltungsteilnahmen löschen
				$stmt = $libDb->prepare('DELETE FROM base_veranstaltung_teilnahme WHERE person=:id');
				$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
				$stmt->execute();

				//Vereinsmitgliedschaften löschen
				$stmt = $libDb->prepare('DELETE FROM base_verein_mitgliedschaft WHERE mitglied=:id');
				$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
				$stmt->execute();

				//Semesterämter löschen
				foreach($libSecurityManager->getPossibleAemter() as $amt){
					$stmt = $libDb->prepare('UPDATE base_semester SET '.$amt.' = NULL WHERE '.$amt.'=:id');
					$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
					$stmt->execute();
				}

				//Leibvaterangaben entfernen
				$stmt = $libDb->prepare('UPDATE base_person SET leibmitglied = NULL WHERE leibmitglied=:id');
				$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
				$stmt->execute();

				//Ehepartnerangaben entfernen
				$stmt = $libDb->prepare('UPDATE base_person SET heirat_partner = NULL WHERE heirat_partner=:id');
				$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
				$stmt->execute();

				//Mitglied aus Datenbank löschen
				$stmt = $libDb->prepare('DELETE FROM base_person WHERE id=:id');
				$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
				$stmt->execute();

				$libGlobal->notificationTexts[] = 'Datensatz gelöscht';

				//Fotodatei löschen
				$libImage->deletePersonFoto($_REQUEST['id']);
			}
		}
	}

	switch($orderby){
		case 0:
			$order = 'SUBSTRING(semester_reception, 3) DESC';
			break;
		case 1:
			$order = 'name, vorname, datum_geburtstag ASC';
			break;
		case 2:
			$order = 'gruppe, name, vorname ASC';
			break;
		case 3:
			$order = 'id ASC';
			break;
		default:
			$order = 'SUBSTRING(semester_reception, 3) DESC';
	}

	echo '<h1>Personen</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo '<div class="card">';
	echo '<div class="card-body">';
	echo '<form action="index.php?pid=intranet_admin_persons" method="post" class="form-inline">';
	echo '<fieldset>';
	echo '<div class="mb-3 row">';

	echo '<label class="visually-hidden" for="sortierung">Sortierung</label>';
	echo '<select id="orderby" name="orderby" class="form-select" onchange="this.form.submit()">';
	echo '<option value="0" ';

	if (isset($_POST['orderby']) && $_POST['orderby'] == 0){
		echo 'selected="selected"';
	}

	echo '>Receptionssemester</option>';
	echo '<option value="1" ';

	if (isset($_POST['orderby']) && $_POST['orderby'] == 1){
		echo 'selected="selected"';
	}

	echo '>Name</option>';
	echo '<option value="2" ';

	if (isset($_POST['orderby']) && $_POST['orderby'] == 2){
		echo 'selected="selected"';
	}

	echo '>Gruppe</option>';
	echo '<option value="3" ';

	if (isset($_POST['orderby']) && $_POST['orderby'] == 3){
		echo 'selected="selected"';
	}

	echo '>Id</option>';
	echo '</select> ';

	echo '</div>';
	echo '</fieldset>';
	echo '</form>';
	echo '</div>';
	echo '</div>';


	echo '<div class="card">';
	echo '<div class="card-body">';
	echo '<div class="table-responsive-sm d-none d-lg-block">';

	echo '<table class="table table table-striped table-hover">';
	echo '<thead>';
	echo '<tr><th>Id</th><th>Präfix</th><th>Name</th><th>Suffix</th><th>Vorname</th><th>Gruppe</th><th>Status</th><th>Reception</th><th>Bearbeiten</th></tr>';
	echo '</thead>';

	$stmt = $libDb->prepare('SELECT * FROM base_person ORDER BY ' .$order);
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
		echo '<td class="tool-column">';
		echo '<a href="index.php?pid=intranet_admin_person&amp;id=' .$row['id']. '">';
		echo '<i class="fa fa-cog" aria-hidden="true"></i>';
		echo '</a>';
		echo '</td>';
		echo '</tr>';
	}
	echo '</table>';

	echo '</div>';

	echo '<div class="d-lg-none container-fluid px-0 pb-5" id="contactList">';

	$stmt = $libDb->prepare('SELECT * FROM base_person ORDER BY ' .$order);
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		echo '<div class="contact-card card mx-3 mb-2 border shadow-sm">';
		echo '<div class="card-body py-2 px-3 d-flex align-items-center gap-3">';
			echo '<div class="avatar rounded-circle d-flex align-items-center justify-content-center>">'.$row['gruppe'].'</div>';
			echo '<div class="flex-grow-1 overflow-hidden">';
			echo '<div class="fw-semibold text-truncate">'.$row['name'].', '.$row['praefix'].' '.$row['vorname'].' '.$row['suffix'].'</div>';
			echo '<div class="text-muted small text-truncate">';
				echo '<i class="bi me-1 text-success"></i>Gruppe: '.$row['gruppe'];
			echo '</div>';
			echo '<div class="text-muted small text-truncate">';
				echo '<i class="bi me-1 text-success"></i>Status: '.$row['status'];
			echo '</div>';
			echo '<div class="text-muted small text-truncate">';
				echo '<i class="bi me-1 text-success"></i>Reception: '.$row['semester_reception'];
			echo '</div>';
			echo '</div>';
			echo '<div class="d-flex flex-column align-items-end gap-2">';
			echo '<span><a href="index.php?pid=intranet_admin_person&amp;id=' .$row['id']. '" class="btn"><i class="fa fa-cog"></i></a></span>';
			echo '<i class="bi bi-chevron-right"></i>';
			echo '</div>';
		echo '</div>';
		echo '</div>';
	}

	echo '</div>';
	echo '</div>';
	echo '</div>';

	if(in_array('internetwart', $libAuth->getAemter()) || in_array('datenpflegewart', $libAuth->getAemter())){
		echo '<a href="index.php?pid=intranet_admin_person&amp;aktion=blank"><button class="btn btn-success rounded-circle shadow position-fixed bottom-0 end-0 m-4 d-flex align-items-center justify-content-center" style="width:52px;height:52px;font-size:1.4rem" title="Eine neue Person anlegen">';
			echo '<i class="bi bi-plus-lg"></i>';
			echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>';
		echo '</button></a>';
	}
}
