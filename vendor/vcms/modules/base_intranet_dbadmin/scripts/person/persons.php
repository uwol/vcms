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

if (!is_object($libGlobal) || !$libAuth->isLoggedin()) {
    exit();
}


if ($libAuth->isLoggedin()) {
    $orderby = 0;

    if (isset($_POST['orderby'])) {
        $orderby = $_POST['orderby'];
    }

    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        if (isset($_POST['id']) && $_POST['id'] != '') {
            // Is the editor not an Internetwart?
            if (!in_array('internetwart', $libAuth->getOffices()) && !in_array('datenpflegewart', $libAuth->getOffices())) {
                die('Diese Aktion darf nur von einem Internetwart ausgeführt werden.');
            }

            // Internetwart edge case: he may never be deleted so that there is always an admin in the system
            $stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM base_semester WHERE internetwart=:internetwart');
            $stmt->bindValue(':internetwart', $_POST['id'], PDO::PARAM_INT);
            $stmt->execute();
            $stmt->bindColumn('number', $count);
            $stmt->fetch();

            if ($count > 0) {
                $libGlobal->errorTexts[] = 'Die Person kann nicht gelöscht werden, weil sie ein Internetwart in mindestens einem Semester ist. Internetwarte können nicht gelöscht werden, weil sie die Administratoren sind und im Extremfall somit kein Administrator im System existiert. Falls diese Person gelöscht werden soll, so muss sie erst manuell von einem Internetwart in allen Semestern aus den Internetwartsposten entfernt werden.';
            } else {
                // Check for usage of the person in other tables
                // Delete those entries first or release them from the member

                // Delete event registrations
                $stmt = $libDb->prepare('DELETE FROM base_veranstaltung_teilnahme WHERE person=:id');
                $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
                $stmt->execute();

                // Delete association memberships
                $stmt = $libDb->prepare('DELETE FROM base_verein_mitgliedschaft WHERE mitglied=:id');
                $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
                $stmt->execute();

                // Delete semester offices
                foreach ($libSecurityManager->getPossibleOffices() as $office) {
                    $stmt = $libDb->prepare('UPDATE base_semester SET '.$office.' = NULL WHERE '.$office.'=:id');
                    $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
                    $stmt->execute();
                }

                // Remove leib father entries
                $stmt = $libDb->prepare('UPDATE base_person SET leibmitglied = NULL WHERE leibmitglied=:id');
                $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
                $stmt->execute();

                // Remove spouse entries
                $stmt = $libDb->prepare('UPDATE base_person SET heirat_partner = NULL WHERE heirat_partner=:id');
                $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
                $stmt->execute();

                // Delete the member from the database
                $stmt = $libDb->prepare('DELETE FROM base_person WHERE id=:id');
                $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
                $stmt->execute();

                $libGlobal->notificationTexts[] = 'Datensatz gelöscht';

                // Delete the photo file
                $libImage->deletePersonPhoto($_POST['id']);
            }
        }
    }

    switch ($orderby) {
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

    if (in_array('internetwart', $libAuth->getOffices()) || in_array('datenpflegewart', $libAuth->getOffices())) {
        echo '<div class="card">';
        echo '<div class="card-body">';
        echo '<div class="btn-toolbar">';
        echo '<a href="index.php?pid=intranet_admin_person&amp;action=blank" class="btn btn-outline-secondary">Eine neue Person anlegen</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    echo '<div class="card">';
    echo '<div class="card-body">';
    echo '<form action="index.php?pid=intranet_admin_persons" method="post">';
    echo '<fieldset class="d-flex flex-wrap align-items-center gap-2">';
    echo '<div>';

    echo '<label class="visually-hidden" for="orderby">Sortierung</label>';
    echo '<select id="orderby" name="orderby" class="form-select w-auto" onchange="this.form.submit()">';
    echo '<option value="0" ';

    if (isset($_POST['orderby']) && $_POST['orderby'] == 0) {
        echo 'selected="selected"';
    }

    echo '>Receptionssemester</option>';
    echo '<option value="1" ';

    if (isset($_POST['orderby']) && $_POST['orderby'] == 1) {
        echo 'selected="selected"';
    }

    echo '>Name</option>';
    echo '<option value="2" ';

    if (isset($_POST['orderby']) && $_POST['orderby'] == 2) {
        echo 'selected="selected"';
    }

    echo '>Gruppe</option>';
    echo '<option value="3" ';

    if (isset($_POST['orderby']) && $_POST['orderby'] == 3) {
        echo 'selected="selected"';
    }

    echo '>Id</option>';
    echo '</select> ';

    $libForm->printSubmitButtonInline('Sortieren');

    echo '</div>';
    echo '</fieldset>';
    echo '</form>';
    echo '</div>';
    echo '</div>';


    echo '<div class="card">';
    echo '<div class="card-body">';

    echo '<table class="table table-sm table-striped table-hover">';
    echo '<thead>';
    echo '<tr><th>Id</th><th>Präfix</th><th>Name</th><th>Suffix</th><th>Vorname</th><th>Gruppe</th><th>Status</th><th>Reception</th><th></th></tr>';
    echo '</thead>';

    $stmt = $libDb->prepare('SELECT * FROM base_person ORDER BY ' .$order);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr>';
        echo '<td>' .$row['id']. '</td>';
        echo '<td>' .$libString->protectXSS($row['praefix']). '</td>';
        echo '<td>' .$libString->protectXSS($row['name']). '</td>';
        echo '<td>' .$libString->protectXSS($row['suffix']). '</td>';
        echo '<td>' .$libString->protectXSS($row['vorname']). '</td>';
        echo '<td>' .$libString->protectXSS($row['gruppe']). '</td>';
        echo '<td>' .$libString->protectXSS($row['status']). '</td>';
        echo '<td>' .$libString->protectXSS((string) $row['semester_reception']). '</td>';
        echo '<td class="tool-column">';
        echo '<a href="index.php?pid=intranet_admin_person&amp;id=' .$row['id']. '">';
        echo '<i class="fa fa-cog" aria-hidden="true"></i>';
        echo '</a>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</table>';

    echo '</div>';
    echo '</div>';
}
