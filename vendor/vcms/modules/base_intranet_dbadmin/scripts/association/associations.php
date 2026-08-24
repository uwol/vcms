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
            // Check for usage of the association in other tables.
            // Delete those entries first, since InnoDB is not used and therefore no CASCADE ALL applies.

            // Delete association memberships
            $stmt = $libDb->prepare('DELETE FROM base_verein_mitgliedschaft WHERE verein=:verein');
            $stmt->bindValue(':verein', $_POST['id'], PDO::PARAM_INT);
            $stmt->execute();

            // If the association is a parent or merger association, set the referencing entries to null
            $stmt = $libDb->prepare('UPDATE base_verein SET mutterverein = NULL WHERE mutterverein=:mutterverein');
            $stmt->bindValue(':mutterverein', $_POST['id'], PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $libDb->prepare('UPDATE base_verein SET fusioniertin = NULL WHERE fusioniertin=:fusioniertin');
            $stmt->bindValue(':fusioniertin', $_POST['id'], PDO::PARAM_INT);
            $stmt->execute();

            // Delete the association from the database
            $stmt = $libDb->prepare('DELETE FROM base_verein WHERE id=:id');
            $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
            $stmt->execute();

            $libGlobal->notificationTexts[] = 'Datensatz gelöscht';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'import') {
        $libAssociation->importAssociations();
    }

    echo '<h1>Vereine</h1>';

    echo $libString->getErrorBoxText();
    echo $libString->getNotificationBoxText();


    echo '<div class="card">';
    echo '<div class="card-body">';
    echo '<div class="btn-toolbar">';
    echo '<form method="post" action="index.php?pid=intranet_admin_associations" class="float-start ms-1" onsubmit="return confirm(\'Willst den Import wirklich durchführen?\')">';
    echo '<input type="hidden" name="action" value="import" />';
    echo '<button type="submit" class="btn btn-outline-secondary"><i class="fa fa-cloud-download" aria-hidden="true"></i> KV-Vereine von ' .$libGlobal->mkHostname. ' importieren</button>';
    echo '</form>';
    echo '<a href="index.php?pid=intranet_admin_association&amp;action=blank" class="btn btn-outline-secondary">Einen neuen Verein anlegen</a>';
    echo '</div>';
    echo '</div>';
    echo '</div>';


    echo '<div class="card">';
    echo '<div class="card-body">';

    echo '<table class="table table-sm table-striped table-hover">';
    echo '<thead>';
    echo '<tr><th>Id</th><th>Name</th><th>Dachverband</th><th>Ort</th><th></th></tr>';
    echo '</thead>';

    $stmt = $libDb->prepare('SELECT * FROM base_verein ORDER BY name');
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr>';
        echo '<td>' .$row['id']. '</td>';
        echo '<td>' .$libString->protectXSS($row['name']). '</td>';
        echo '<td>' .$libString->protectXSS($row['dachverband']). '</td>';
        echo '<td>' .$libString->protectXSS($row['ort1']). '</td>';
        echo '<td class="tool-column">';
        echo '<a href="index.php?pid=intranet_admin_association&amp;id=' .$row['id']. '">';
        echo '<i class="fa fa-cog" aria-hidden="true"></i>';
        echo '</a>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</table>';

    echo '</div>';
    echo '</div>';
}
