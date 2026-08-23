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

    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        if (isset($_POST['id']) && $_POST['id'] != '') {
            //Verwendung der Veranstaltung in anderen Tabellen prüfen
            //diese Einträge vorher löschen

            //Veranstaltungsteilnahmen löschen
            $stmt = $libDb->prepare('DELETE FROM base_veranstaltung_teilnahme WHERE veranstaltung=:veranstaltung');
            $stmt->bindValue(':veranstaltung', $_POST['id'], PDO::PARAM_INT);
            $stmt->execute();

            //Veranstaltung aus Datenbank löschen
            $stmt = $libDb->prepare('DELETE FROM base_veranstaltung WHERE id=:id');
            $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
            $stmt->execute();

            $libGlobal->notificationTexts[] = 'Datensatz gelöscht.';
        }
    }

    echo '<h1>Veranstaltungen</h1>';

    echo $libString->getErrorBoxText();
    echo $libString->getNotificationBoxText();


    echo '<div class="panel panel-default">';
    echo '<div class="panel-body">';
    echo '<div class="btn-toolbar">';
    echo '<a href="index.php?pid=intranet_admin_event&amp;action=blank" class="btn btn-default">Eine neue Veranstaltung anlegen</a>';
    echo '</div>';
    echo '</div>';
    echo '</div>';


    //Semesterauswahl
    $stmt = $libDb->prepare("SELECT DATE_FORMAT(datum,'%Y-%m-01') AS datum FROM base_veranstaltung WHERE datum IS NOT NULL GROUP BY datum ORDER BY datum DESC");
    $stmt->execute();

    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $row['datum'];
    }

    echo $libTime->getSemesterMenu($libTime->getSemestersFromDates($data), $libGlobal->semester);


    echo '<div class="panel panel-default">';
    echo '<div class="panel-body">';

    echo '<table class="table table-condensed table-striped table-hover">';
    echo '<thead>';
    echo '<tr><th>Id</th><th>Datum</th><th>Titel</th><th>Status</th><th>Intern</th><th></th></tr>';
    echo '</thead>';

    $period = $libTime->getPeriod($libGlobal->semester);

    $stmt = $libDb->prepare('SELECT * FROM base_veranstaltung WHERE datum IS NULL OR datum = :datum OR (DATEDIFF(datum, :semester_start) >= 0 AND DATEDIFF(datum, :semester_ende) <= 0) ORDER BY datum DESC');
    $stmt->bindValue(':datum', $period[0]);
    $stmt->bindValue(':semester_start', $period[0]);
    $stmt->bindValue(':semester_ende', $period[1]);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr>';
        echo '<td>' .$row['id']. '</td>';
        echo '<td>' .$row['datum']. '</td>';
        echo '<td>' .$libString->protectXSS($row['titel']). '</td>';
        echo '<td>' .$libString->protectXSS((string) $row['status']). '</td>';
        echo '<td class="tool-column">';

        if ($row['intern']) {
            echo '<i aria-hidden="true" class="fa fa-check-square-o"></i>';
        }

        echo '</td>';
        echo '<td class="tool-column">';
        echo '<a href="index.php?pid=intranet_admin_event&amp;id=' .$row['id']. '">';
        echo '<i class="fa fa-cog" aria-hidden="true"></i>';
        echo '</a>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</table>';

    echo '</div>';
    echo '</div>';
}
