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
    $id = '';
    if (isset($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
    }

    $action = '';
    if (isset($_REQUEST['action'])) {
        $action = $_REQUEST['action'];
    }

    $array = [];
    // Specify the fields of the table -> metadata
    $fields = ['praefix', 'name', 'suffix', 'vorname', 'anrede', 'titel', 'rang', 'zusatz1', 'strasse1', 'plz1', 'ort1', 'land1', 'telefon1', 'status', 'grund', 'bemerkung'];

    /**
    *
    * Perform different actions on the database, depending on the context
    * defined by action
    *
    */

    // New record, empty dataset
    if ($action == 'blank') {
        foreach ($fields as $field) {
            $array[$field] = '';
        }

        $array['id'] = '';
        $array['name'] = 'Namen angeben!';
        $array['datum_adresse1_stand'] = '';
    }
    // Data was entered with blank, now being saved
    elseif ($action == 'insert') {
        if (!isset($_POST['form_complete']) || !$_POST['form_complete']) {
            die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
        }

        $array = $libDb->insertRow($fields, $_REQUEST, 'base_vip', ['id' => '']);
        updateAddressAsOf('base_vip', 'datum_adresse1_stand', $array['id']);
    }
    // Existing data is being modified
    elseif ($action == 'update') {
        if (!isset($_POST['form_complete']) || !$_POST['form_complete']) {
            die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
        }

        $stmt = $libDb->prepare('SELECT * FROM base_vip WHERE id=:id');
        $stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
        $stmt->execute();
        $array = $stmt->fetch(PDO::FETCH_ASSOC);

        // Detect address changes and record them in the as-of field
        if ($_REQUEST['strasse1'] != $array['strasse1'] || $_REQUEST['ort1'] != $array['ort1'] || $_REQUEST['plz1'] != $array['plz1']) {
            updateAddressAsOf('base_vip', 'datum_adresse1_stand', $array['id']);
        }

        $array = $libDb->updateRow($fields, $_REQUEST, 'base_vip', ['id' => $id]);
    } else {
        $stmt = $libDb->prepare('SELECT * FROM base_vip WHERE id=:id');
        $stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
        $stmt->execute();
        $array = $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /**
    *
    * Introductory text
    *
    */
    echo '<h1>Vip</h1>';

    echo $libString->getErrorBoxText();
    echo $libString->getNotificationBoxText();

    /**
    *
    * Deletion option
    *
    */
    if ($array['id'] != '') {
        echo '<form class="mb-4" method="post" action="index.php?pid=intranet_admin_vips" onsubmit="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">';
        echo '<input type="hidden" name="action" value="delete" />';
        echo '<input type="hidden" name="id" value="' .$array['id']. '" />';
        echo '<button type="submit" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer"><i class="fa fa-trash" aria-hidden="true"></i> Datensatz löschen</button>';
        echo '</form>';
    }

    /**
    *
    * Start form output
    *
    */
    if ($action == 'blank') {
        $extraActionParam = '&amp;action=insert';
    } else {
        $extraActionParam = '&amp;action=update';
    }

    echo '<div class="panel panel-default">';
    echo '<div class="panel-body">';
    echo '<form action="index.php?pid=intranet_admin_vip' .$extraActionParam. '" method="post" class="form-horizontal">';
    echo '<fieldset>';
    echo '<input type="hidden" name="formType" value="vipData" />';
    echo '<input type="hidden" name="id" value="' .$array['id']. '" />';

    $libForm->printTextInput('id', 'Id', $array['id'], 'text', true);
    $libForm->printTextInput('praefix', 'Präfix', $array['praefix']);
    $libForm->printTextInput('name', 'Name', $array['name']);
    $libForm->printTextInput('suffix', 'Suffix', $array['suffix']);
    $libForm->printTextInput('vorname', 'Vorname', $array['vorname']);
    $libForm->printTextInput('anrede', 'Anrede', $array['anrede']);
    $libForm->printTextInput('titel', 'Titel', $array['titel']);
    $libForm->printTextInput('rang', 'Rang', $array['rang']);
    $libForm->printTextInput('zusatz1', 'Zusatz 1', $array['zusatz1']);
    $libForm->printTextInput('strasse1', 'Strasse 1', $array['strasse1']);
    $libForm->printTextInput('plz1', 'Plz 1', $array['plz1']);
    $libForm->printTextInput('ort1', 'Ort 1', $array['ort1']);
    $libForm->printTextInput('land1', 'Land 1', $array['land1']);
    $libForm->printDateInput('datum_adresse1_stand', 'Stand 1', $array['datum_adresse1_stand'], true);
    $libForm->printTextInput('telefon1', 'Telefon 1', $array['telefon1'], 'tel');
    $libForm->printTextInput('status', 'Status', $array['status']);
    $libForm->printTextInput('grund', 'Grund', $array['grund']);
    $libForm->printTextInput('bemerkung', 'Bemerkung', $array['bemerkung']);

    echo '<input type="hidden" name="form_complete" value="1" />';

    $libForm->printSubmitButton('Speichern');

    echo '</fieldset>';
    echo '</form>';
    echo '</div>';
    echo '</div>';
}

function updateAddressAsOf($table, $field, $id)
{
    global $libDb;

    $stmt = $libDb->prepare('UPDATE ' .$table. ' SET ' .$field. '=NOW() WHERE id=:id');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}
