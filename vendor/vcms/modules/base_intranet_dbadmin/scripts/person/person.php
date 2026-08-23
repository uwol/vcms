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

    $personRow = [];
    $personRow['id'] = '';
    // Specify the fields of the person table -> metadata
    $fields = [
        'anrede', 'titel', 'rang', 'vorname', 'praefix', 'name', 'suffix', 'geburtsname',
        'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1',
        'zusatz2', 'strasse2', 'ort2', 'plz2', 'land2',
        'region1', 'region2', 'telefon1', 'telefon2', 'mobiltelefon', 'email', 'skype', 'webseite',
        'datum_geburtstag', 'beruf', 'heirat_datum', 'heirat_partner', 'tod_datum', 'tod_ort', 'status',
        'semester_reception', 'semester_promotion', 'semester_philistrierung', 'semester_aufnahme', 'semester_fusion',
        'austritt_datum', 'spitzname', 'leibmitglied',
        'anschreiben_zusenden', 'spendenquittung_zusenden',
        'bemerkung', 'vita'];

    // Is the editor an Internetwart?
    if (in_array('internetwart', $libAuth->getOffices()) || in_array('datenpflegewart', $libAuth->getOffices())) {
        // Then also edit the sensitive fields
        $fields = array_merge($fields, ['gruppe', 'password_hash']);
    }

    /**
    *
    * Perform different actions on the database, depending on the context
    * defined by action
    *
    */

    // New person, empty record
    if ($action == 'blank') {
        foreach ($fields as $field) {
            $personRow[$field] = '';
        }

        $personRow['anrede'] = 'Anrede angeben!';
        $personRow['vorname'] = 'Vornamen angeben!';
        $personRow['name'] = 'Namen angeben!';
        $personRow['anschreiben_zusenden'] = '1';
        $personRow['spendenquittung_zusenden'] = '1';
        $personRow['datum_adresse1_stand'] = '';
        $personRow['datum_adresse2_stand'] = '';
        $personRow['gruppe'] = '';
        $personRow['datum_gruppe_stand'] = '';
        $personRow['password_hash'] = '';
    }
    // Data was entered with blank, now being saved: INSERT
    elseif ($action == 'insert') {
        if (!isset($_POST['form_complete']) || !$_POST['form_complete']) {
            die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
        }

        // Is the editor not an Internetwart?
        if (!in_array('internetwart', $libAuth->getOffices()) && !in_array('datenpflegewart', $libAuth->getOffices())) {
            die('Diese Aktion darf nur von einem Internetwart ausgeführt werden.');
        }

        $valueArray = $_REQUEST;
        $valueArray['email'] = strtolower((string) ($valueArray['email'] ?? ''));
        $valueArray['datum_geburtstag'] = $libTime->assureMysqlDate($valueArray['datum_geburtstag'] ?? '');
        $valueArray['heirat_datum'] = $libTime->assureMysqlDate($valueArray['heirat_datum'] ?? '');
        $valueArray['tod_datum'] = $libTime->assureMysqlDate($valueArray['tod_datum'] ?? '');
        $valueArray['austritt_datum'] = $libTime->assureMysqlDate($valueArray['austritt_datum'] ?? '');
        $personRow = $libDb->insertRow($fields, $valueArray, 'base_person', ['id' => '']);

        updateAddressAsOf('base_person', 'datum_adresse1_stand', $personRow['id']);
        updateAddressAsOf('base_person', 'datum_adresse2_stand', $personRow['id']);
        updateGroupAsOf($personRow['id']);

        // If a spouse is given, this member must also be set as spouse in that person's record
        updateCorrespondingSpouse($_REQUEST['heirat_partner'], $personRow['id']);
    }
    // Existing member data is being modified: UPDATE
    elseif ($action == 'update') {
        if (!isset($_POST['form_complete']) || !$_POST['form_complete']) {
            die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
        }

        // Fetch current data
        $stmt = $libDb->prepare('SELECT * FROM base_person WHERE id=:id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $personRow = $stmt->fetch(PDO::FETCH_ASSOC);

        // Detect address changes and record them in the as-of field
        if ($_REQUEST['strasse1'] != $personRow['strasse1'] || $_REQUEST['ort1'] != $personRow['ort1'] || $_REQUEST['plz1'] != $personRow['plz1'] || $_REQUEST['land1'] != $personRow['land1'] || $_REQUEST['telefon1'] != $personRow['telefon1']) {
            updateAddressAsOf('base_person', 'datum_adresse1_stand', $personRow['id']);
        }

        if ($_REQUEST['strasse2'] != $personRow['strasse2'] || $_REQUEST['ort2'] != $personRow['ort2'] || $_REQUEST['plz2'] != $personRow['plz2'] || $_REQUEST['land2'] != $personRow['land2'] || $_REQUEST['telefon2'] != $personRow['telefon2']) {
            updateAddressAsOf('base_person', 'datum_adresse2_stand', $personRow['id']);
        }

        if (isset($_REQUEST['gruppe']) && $_REQUEST['gruppe'] != $personRow['gruppe']) {
            updateGroupAsOf($personRow['id']);
        }

        //if a spouse is given, this member must also be set as spouse in that person's record
        if ($_REQUEST['heirat_partner'] != $personRow['heirat_partner']) {
            updateCorrespondingSpouse($_REQUEST['heirat_partner'], $personRow['id']);
        }

        $valueArray = $_REQUEST;
        $valueArray['email'] = strtolower((string) ($valueArray['email'] ?? ''));
        $valueArray['datum_geburtstag'] = $libTime->assureMysqlDate($valueArray['datum_geburtstag'] ?? '');
        $valueArray['heirat_datum'] = $libTime->assureMysqlDate($valueArray['heirat_datum'] ?? '');
        $valueArray['tod_datum'] = $libTime->assureMysqlDate($valueArray['tod_datum'] ?? '');
        $valueArray['austritt_datum'] = $libTime->assureMysqlDate($valueArray['austritt_datum'] ?? '');
        $personRow = $libDb->updateRow($fields, $valueArray, 'base_person', ['id' => $id]);
    } else {
        $stmt = $libDb->prepare('SELECT * FROM base_person WHERE id=:id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $personRow = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Perform image upload
    // Was a file uploaded?
    if (isset($_POST['formType']) && $_POST['formType'] == 'photoUpload') {
        // Was a file uploaded?
        if ($_FILES['bilddatei']['tmp_name'] != '') {
            if ($personRow['id'] != '') {
                $libImage->savePersonPhotoByFilesArray($personRow['id'], 'bilddatei');
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'photoDelete') {
        if ($personRow['id'] != '') {
            $libImage->deletePersonPhoto($personRow['id']);
        }
    }


    /**
    *
    * Introductory text
    *
    */

    echo '<h1>Person</h1>';

    echo $libString->getErrorBoxText();
    echo $libString->getNotificationBoxText();

    echo '<p class="mb-4">Hier können sämtliche Daten einer Person bearbeitet werden. Die Gruppe (Fuchs, Bursch etc.) kann nur von einem Internetwart ausgewählt werden, da sie als Zugangskontrolle für Seiten im VCMS dient.</p>';
    echo '<hr />';

    /**
    *
    * Deletion option
    *
    */
    if (in_array('internetwart', $libAuth->getOffices()) || in_array('datenpflegewart', $libAuth->getOffices())) {
        if ($personRow['id'] != '') {
            echo '<form class="mb-4" method="post" action="index.php?pid=intranet_admin_persons" onsubmit="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">';
            echo '<input type="hidden" name="action" value="delete" />';
            echo '<input type="hidden" name="id" value="' .$personRow['id']. '" />';
            echo '<button type="submit" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer"><i class="fa fa-trash" aria-hidden="true"></i> Datensatz löschen</button>';
            echo '</form>';
        }
    }

    echo '<div class="row">';
    echo '<div class="col-sm-9">';


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

    echo '<div class="card">';
    echo '<div class="card-body">';
    echo '<form action="index.php?pid=intranet_admin_person' .$extraActionParam. '" method="post">';
    echo '<fieldset>';
    echo '<input type="hidden" name="formType" value="personData" />';
    echo '<input type="hidden" name="id" value="' .$personRow['id']. '" />';

    $libForm->printTextInput('id', 'Id', $personRow['id'], 'text', true);
    $libForm->printTextInput('anrede', 'Anrede', $personRow['anrede']);
    $libForm->printTextInput('titel', 'Titel', $personRow['titel']);
    $libForm->printTextInput('rang', 'Rang', $personRow['rang']);
    $libForm->printTextInput('vorname', 'Vorname', $personRow['vorname']);
    $libForm->printTextInput('praefix', 'Präfix', $personRow['praefix']);
    $libForm->printTextInput('name', 'Name', $personRow['name']);
    $libForm->printTextInput('suffix', 'Suffix', $personRow['suffix']);
    $libForm->printTextInput('geburtsname', 'Geburtsname', $personRow['geburtsname']);

    $libForm->printTextInput('zusatz1', 'Zusatz 1', $personRow['zusatz1']);
    $libForm->printTextInput('strasse1', 'Strasse 1', $personRow['strasse1']);
    $libForm->printTextInput('ort1', 'Ort 1', $personRow['ort1']);
    $libForm->printTextInput('plz1', 'Plz 1', $personRow['plz1']);
    $libForm->printTextInput('land1', 'Land 1', $personRow['land1']);
    $libForm->printTextInput('telefon1', 'Telefon 1', $personRow['telefon1'], 'tel');
    $libForm->printDateInput('datum_adresse1_stand', 'Stand 1', $personRow['datum_adresse1_stand'], true);

    $libForm->printTextInput('zusatz2', 'Zusatz 2', $personRow['zusatz2']);
    $libForm->printTextInput('strasse2', 'Strasse 2', $personRow['strasse2']);
    $libForm->printTextInput('ort2', 'Ort 2', $personRow['ort2']);
    $libForm->printTextInput('plz2', 'Plz 2', $personRow['plz2']);
    $libForm->printTextInput('land2', 'Land 2', $personRow['land2']);
    $libForm->printTextInput('telefon2', 'Telefon 2', $personRow['telefon2'], 'tel');
    $libForm->printDateInput('datum_adresse2_stand', 'Stand 2', $personRow['datum_adresse2_stand'], true);

    $libForm->printRegionDropDownBox('region1', 'Region 1', $personRow['region1']);
    $libForm->printRegionDropDownBox('region2', 'Region 2', $personRow['region2']);

    $libForm->printTextInput('mobiltelefon', 'Mobiltelefon', $personRow['mobiltelefon'], 'tel');
    $libForm->printTextInput('email', 'E-Mail-Adresse', $personRow['email'], 'email');
    $libForm->printTextInput('skype', 'Skype', $personRow['skype']);
    $libForm->printTextInput('webseite', 'Webseite', $personRow['webseite']);
    $libForm->printDateInput('datum_geburtstag', 'Geburtsdatum', $personRow['datum_geburtstag']);
    $libForm->printTextInput('beruf', 'Beruf', $personRow['beruf']);
    $libForm->printDateInput('heirat_datum', 'Heiratsdatum', $personRow['heirat_datum']);

    $libForm->printMembersDropDownBox('heirat_partner', 'Ehepartner', $personRow['heirat_partner']);

    $libForm->printDateInput('tod_datum', 'Todesdatum', $personRow['tod_datum']);
    $libForm->printTextInput('tod_ort', 'Todesort', $personRow['tod_ort']);

    $libForm->printStatusDropDownBox('status', 'Status', $personRow['status']);

    $libForm->printSemesterDropDownBox('semester_reception', 'Semester Reception', $personRow['semester_reception']);
    $libForm->printSemesterDropDownBox('semester_promotion', 'Semester Promotion', $personRow['semester_promotion']);
    $libForm->printSemesterDropDownBox('semester_philistrierung', 'Semester Philistrierung', $personRow['semester_philistrierung']);
    $libForm->printSemesterDropDownBox('semester_aufnahme', 'Semester Aufnahme', $personRow['semester_aufnahme']);
    $libForm->printSemesterDropDownBox('semester_fusion', 'Semester Fusion', $personRow['semester_fusion']);

    $libForm->printDateInput('austritt_datum', 'Austrittsdatum', $personRow['austritt_datum']);
    $libForm->printTextInput('spitzname', 'Spitzname', $personRow['spitzname']);

    $libForm->printMembersDropDownBox('leibmitglied', 'Leibmitglied', $personRow['leibmitglied']);

    // Send letter
    $libForm->printBoolSelectBox('anschreiben_zusenden', 'Anschreiben zusenden', $personRow['anschreiben_zusenden']);

    // Send donation receipt
    $libForm->printBoolSelectBox('spendenquittung_zusenden', 'Spendenquittung zusenden', $personRow['spendenquittung_zusenden']);

    $libForm->printTextInput('bemerkung', 'Bemerkung', $personRow['bemerkung']);
    $libForm->printTextarea('vita', 'Vita', $personRow['vita']);

    // Only the Internetwart may access sensitive data
    if (in_array('internetwart', $libAuth->getOffices()) || in_array('datenpflegewart', $libAuth->getOffices())) {
        $libForm->printGroupDropDownBox('gruppe', 'Gruppe', $personRow['gruppe'], false);
        $libForm->printDateInput('datum_gruppe_stand', 'Stand', $personRow['datum_gruppe_stand'], true);
        $libForm->printTextInput('password_hash', 'Passwort-Hash', $personRow['password_hash']);
    }

    echo '<input type="hidden" name="form_complete" value="1" />';

    $libForm->printSubmitButton('Speichern');

    echo '</fieldset>';
    echo '</form>';
    echo '</div>';
    echo '</div>';

    echo '</div>';
    echo '<div class="col-sm-3">';

    if ($personRow['id'] != '') {
        echo '<div class="d-block mx-auto person-signature-box mb-3">';
        echo '<div class="img-box">';

        echo '<span class="delete-icon-box">';
        echo '<form method="post" action="index.php?pid=intranet_admin_person" class="d-inline" onsubmit="return confirm(\'Willst Du das Foto wirklich löschen?\')">';
        echo '<input type="hidden" name="action" value="photoDelete" />';
        echo '<input type="hidden" name="id" value="' .$personRow['id']. '" />';
        echo '<button type="submit" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer"><i class="fa fa-trash" aria-hidden="true"></i></button>';
        echo '</form>';
        echo '</span>';

        echo $libPerson->getImage($personRow['id'], 'lg');
        echo '</div>';
        echo '</div>';

        //image upload form
        echo '<form action="index.php?pid=intranet_admin_person&amp;id='. $personRow['id'] .'" method="post" enctype="multipart/form-data" class="text-center">';
        echo '<input type="hidden" name="formType" value="photoUpload" />';
        $libForm->printFileUpload('bilddatei', 'Foto (4x3) hochladen', false, false, [], ['image/jpeg']);
        echo '</form>';
    }

    echo '</div>';
    echo '</div>';
}

function updateGroupAsOf($id)
{
    global $libDb;

    $stmt = $libDb->prepare('UPDATE base_person SET datum_gruppe_stand=NOW() WHERE id=:id');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}

function updateAddressAsOf($table, $field, $id)
{
    global $libDb;

    $stmt = $libDb->prepare('UPDATE '.$table.' SET ' .$field. '=NOW() WHERE id=:id');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}

function updateCorrespondingSpouse($spouseId, $memberId)
{
    global $libDb;

    if (is_numeric($memberId) && is_numeric($spouseId)) {
        $stmt = $libDb->prepare('UPDATE base_person SET heirat_partner=:heirat_partner WHERE id=:id');
        $stmt->bindValue(':heirat_partner', $memberId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $spouseId, PDO::PARAM_INT);
        $stmt->execute();
    }
}
