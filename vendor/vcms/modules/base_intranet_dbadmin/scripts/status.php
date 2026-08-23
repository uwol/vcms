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
    if (isset($_POST['action']) && $_POST['action'] == 'create') {
        if ($_POST['bezeichnung'] != '') {
            $stmt = $libDb->prepare('INSERT INTO base_status (bezeichnung, beschreibung) VALUES (:bezeichnung, :beschreibung)');
            $stmt->bindValue(':bezeichnung', $_POST['bezeichnung']);
            $stmt->bindValue(':beschreibung', $_POST['beschreibung']);
            $stmt->execute();
        } else {
            $libGlobal->errorTexts[] = 'Keine Bezeichnung angegeben.';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'delete') {
        if ($_POST['bezeichnung'] != '') {
            $stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM base_person WHERE status = :status');
            $stmt->bindValue(':status', $_POST['bezeichnung']);
            $stmt->execute();
            $stmt->bindColumn('number', $count);
            $stmt->fetch();

            // Is this status still used in base_person?
            if ($count > 0) {
                $libGlobal->errorTexts[] = 'Dieser Status wird von Mitgliedern verwendet.';
            } else {
                $stmt = $libDb->prepare('DELETE FROM base_status WHERE bezeichnung = :bezeichnung');
                $stmt->bindValue(':bezeichnung', $_POST['bezeichnung']);
                $stmt->execute();

                $libGlobal->notificationTexts[] = 'Status gelöscht.';
            }
        } else {
            $libGlobal->errorTexts[] = 'Kein Status angegeben.';
        }
    }

    echo '<h1>Status</h1>';

    echo $libString->getErrorBoxText();
    echo $libString->getNotificationBoxText();

    echo '<div class="card">';
    echo '<div class="card-body">';

    echo '<table class="table table-sm table-striped table-hover">';
    echo '<thead>';
    echo '<tr><th>Bezeichnung</th><th>Beschreibung</th><th></th></tr>';
    echo '</thead>';

    $stmt = $libDb->prepare('SELECT * FROM base_status');
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr>';
        echo '<td>' .$libString->protectXSS($row['bezeichnung']). '</td>';
        echo '<td>' .$libString->protectXSS((string) $row['beschreibung']). '</td>';
        echo '<td class="tool-column">';

        if ($row['bezeichnung'] != 'A-Phil' && $row['bezeichnung'] != 'B-Phil' && $row['bezeichnung'] != 'Ehrenmitglied' && $row['bezeichnung'] != 'ex loco' && $row['bezeichnung'] != 'HV-M' && $row['bezeichnung'] != 'Inaktiv ex loco' && $row['bezeichnung'] != 'Inaktiv' && $row['bezeichnung'] != 'VG') {
            echo '<form method="post" action="index.php?pid=intranet_admin_status" class="d-inline" onsubmit="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">';
            echo '<input type="hidden" name="action" value="delete" />';
            echo '<input type="hidden" name="bezeichnung" value="' .$libString->protectXSS($row['bezeichnung']). '" />';
            echo '<button type="submit" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer"><i class="fa fa-trash" aria-hidden="true"></i></button>';
            echo '</form>';
        }

        echo '</td>';
        echo '</tr>';
    }

    echo '</table>';

    echo '</div>';
    echo '</div>';


    echo '<h2>Neuen Status anlegen</h2>';

    echo '<div class="card">';
    echo '<div class="card-body">';
    echo '<form action="index.php?pid=intranet_admin_status" method="post">';
    echo '<fieldset>';
    echo '<input type="hidden" name="action" value="create" />';

    $libForm->printTextInput('bezeichnung', 'Bezeichnung (maximal 20 Buchstaben)', '');
    $libForm->printTextInput('beschreibung', 'Beschreibung', '');
    $libForm->printSubmitButton('Anlegen');

    echo '</fieldset>';
    echo '</form>';
    echo '</div>';
    echo '</div>';
}
