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

    $eventRow = [];
    // Specify the fields of the table -> metadata
    $fields = ['titel', 'datum', 'datum_ende', 'spruch', 'beschreibung', 'status', 'ort', 'fb_eventid', 'intern'];

    /**
    *
    * Perform different actions on the database, depending on the context
    * defined by action
    *
    */

    // New event, empty record
    if ($action == 'blank') {
        $eventRow['id'] = '';
        $eventRow['datum'] = @date('Y-m-d H:i:s');
        $eventRow['datum_ende'] = '';
        $eventRow['titel'] = 'Titel angeben!';
        $eventRow['spruch'] = '';
        $eventRow['beschreibung'] = '';
        $eventRow['status'] = '';
        $eventRow['ort'] = '';
        $eventRow['fb_eventid'] = '';
        $eventRow['intern'] = $libGenericStorage->loadValue('base_core', 'event_preselect_intern');
    }
    // Data was entered with blank, now being saved
    elseif ($action == 'insert') {
        if (!isset($_POST['form_complete']) || !$_POST['form_complete']) {
            die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
        }

        $valueArray = $_REQUEST;
        $valueArray['datum'] = $libTime->assureMysqlDateTime($valueArray['datum']);
        $valueArray['datum_ende'] = $libTime->assureMysqlDateTime($valueArray['datum_ende']);

        if ($valueArray['datum_ende'] != '0000-00-00 00:00:00' &&
                $valueArray['datum_ende'] != '' &&
                $valueArray['datum_ende'] < $valueArray['datum']) {
            $valueArray['datum_ende'] = '';
            $libGlobal->errorTexts[] = 'Das Enddatum liegt vor dem Startdatum.';
        }

        $eventRow = $libDb->insertRow($fields, $valueArray, 'base_veranstaltung', ['id' => '']);
    }
    // Existing data is being modified
    elseif ($action == 'update') {
        if (!isset($_POST['form_complete']) || !$_POST['form_complete']) {
            die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
        }

        $valueArray = $_REQUEST;
        $valueArray['datum'] = $libTime->assureMysqlDateTime($valueArray['datum']);
        $valueArray['datum_ende'] = $libTime->assureMysqlDateTime($valueArray['datum_ende']);

        if ($valueArray['datum_ende'] != '0000-00-00 00:00:00' &&
                $valueArray['datum_ende'] != '' &&
                $valueArray['datum_ende'] < $valueArray['datum']) {
            $valueArray['datum_ende'] = '';
            $libGlobal->errorTexts[] = 'Das Enddatum liegt vor dem Startdatum.';
        }

        $eventRow = $libDb->updateRow($fields, $valueArray, 'base_veranstaltung', ['id' => $id]);
    } else {
        $stmt = $libDb->prepare('SELECT * FROM base_veranstaltung WHERE id=:id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $eventRow = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($libEvent->hasBannedTitle($id)) {
        $libGlobal->errorTexts[] = 'Der Veranstaltungstitel ist nicht optimal.';
    }

    /**
    *
    * Introductory text
    *
    */

    echo '<h1>Veranstaltung</h1>';

    echo $libString->getErrorBoxText();
    echo $libString->getNotificationBoxText();

    echo '<p class="mb-4">Hier können sämtliche Daten einer Veranstaltung bearbeitet werden.</p>';
    echo '<hr />';

    /**
    *
    * Deletion option
    *
    */
    if ($eventRow['id'] != '') {
        echo '<form class="mb-4" method="post" action="index.php?pid=intranet_admin_events" onsubmit="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">';
        echo '<input type="hidden" name="action" value="delete" />';
        echo '<input type="hidden" name="id" value="' .$eventRow['id']. '" />';
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
    echo '<form action="index.php?pid=intranet_admin_event' .$extraActionParam. '" method="post" class="form-horizontal">';
    echo '<fieldset>';
    echo '<input type="hidden" name="formType" value="eventData" />';
    echo '<input type="hidden" name="id" value="' .$eventRow['id']. '" />';

    $libForm->printTextInput('id', 'Id', $eventRow['id'], 'text', true);
    $libForm->printDateTimeInput('datum', 'Beginn (Uhrzeit 00:00 = ganztägig)', $eventRow['datum']);
    $libForm->printDateTimeInput('datum_ende', 'Ende (optional, Uhrzeit 00:00 = ganztägig)', $eventRow['datum_ende']);
    $libForm->printTextInput('titel', 'Titel', $eventRow['titel']);
    $libForm->printTextInput('spruch', 'Spruch', $eventRow['spruch']);
    $libForm->printTextarea('beschreibung', 'Beschreibung', $eventRow['beschreibung']);
    $libForm->printTextInput('status', 'Status (Maximal 2 Buchstaben, z. B. ho oder o)', $eventRow['status']);
    $libForm->printTextInput('ort', 'Ort', $eventRow['ort']);
    $libForm->printTextInput('fb_eventid', '<i class="fa fa-facebook-official" aria-hidden="true"></i> Event-Id', $eventRow['fb_eventid']);
    $libForm->printBoolSelectBox('intern', 'Intern', $eventRow['intern']);

    echo '<input type="hidden" name="form_complete" value="1" />';

    $libForm->printSubmitButton('Speichern');

    echo '</fieldset>';
    echo '</form>';
    echo '</div>';
    echo '</div>';
}
