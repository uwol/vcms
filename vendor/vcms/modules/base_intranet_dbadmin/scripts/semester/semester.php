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
    $action = '';

    if (isset($_REQUEST['action'])) {
        $action = $_REQUEST['action'];
    }

    $semesterRow = [];

    // Specify the fields of the table -> metadata
    $board = ['senior', 'sen_dech', 'consenior', 'con_dech', 'fuchsmajor', 'fm_dech', 'fuchsmajor2', 'fm2_dech', 'scriptor', 'scr_dech', 'quaestor', 'quaes_dech', 'jubelsenior', 'jubelsen_dech'];
    $ahv = ['ahv_senior', 'ahv_consenior', 'ahv_keilbeauftragter', 'ahv_scriptor', 'ahv_quaestor', 'ahv_beisitzer1', 'ahv_beisitzer2'];
    $hv = ['hv_vorsitzender', 'hv_kassierer', 'hv_beisitzer1', 'hv_beisitzer2'];
    $warte = [
        'archivar', 'ausflugswart',
        'bierwart', 'bootshauswart',
        'couleurartikelwart',
        'dachverbandsberichterstatter', 'datenpflegewart',
        'fechtwart', 'ferienordner', 'fotowart',
        'hauswart', 'huettenwart',
        'internetwart',
        'kuehlschrankwart',
        'musikwart',
        'redaktionswart',
        'sportwart', 'stammtischwart',
        'technikwart', 'thekenwart',
        'wichswart', 'wirtschaftskassenwart'];
    $vorort = ['vop', 'vvop', 'vopxx', 'vopxxx', 'vopxxxx'];
    $fields = array_merge(['semester'], $board, $ahv, $hv, $warte, $vorort);

    /**
    *
    * Perform different actions on the database, depending on the context
    * defined by action
    *
    */

    // New semester, empty record
    if ($action == 'blank') {
        $stmt = $libDb->prepare('SELECT * FROM base_semester ORDER BY SUBSTRING(semester,3) DESC LIMIT 0,1');
        $stmt->execute();
        $lastSemester = $stmt->fetch(PDO::FETCH_ASSOC);

        $semesterRow['semester'] = $libTime->getFollowingSemesterName();

        foreach ($board as $office) {
            $semesterRow[$office] = '';
        }

        foreach ($vorort as $office) {
            $semesterRow[$office] = '';
        }

        // Copy data over from the last semester
        foreach ($warte as $office) {
            $semesterRow[$office] = $lastSemester[$office];
        }

        foreach ($ahv as $office) {
            $semesterRow[$office] = $lastSemester[$office];
        }

        foreach ($hv as $office) {
            $semesterRow[$office] = $lastSemester[$office];
        }
    }
    // Data was entered with blank, now being saved: INSERT
    elseif ($action == 'insert') {
        if (!isset($_POST['form_complete']) || !$_POST['form_complete']) {
            die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
        }

        if (!$libTime->isValidSemesterString($_REQUEST['semester'])) {
            die('Das Format des Semesters '.$libString->protectXSS($_REQUEST['semester']).' ist nicht korrekt. Erlaubt sind z. B. SS2015 oder WS20152016.');
        }

        $stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM base_semester WHERE semester=:semester');
        $stmt->bindValue(':semester', $_REQUEST['semester']);
        $stmt->execute();
        $stmt->bindColumn('number', $count);
        $stmt->fetch();

        if ($count > 0) {
            $libGlobal->errorTexts[] = 'Das Semester ist bereits vorhanden.';
            $semesterRow = $_REQUEST;
        } else {
            $semesterRow = $libDb->insertRow($fields, $_REQUEST, 'base_semester', ['semester' => $_REQUEST['semester']]);
        }
    }
    // Existing member data is being modified: UPDATE
    elseif ($action == 'update') {
        if (!isset($_POST['form_complete']) || !$_POST['form_complete']) {
            die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
        }

        if (!$libTime->isValidSemesterString($_REQUEST['semester'])) {
            die('Das Format des Semesters '.$libString->protectXSS($_REQUEST['semester']).' ist nicht korrekt. Erlaubt sind z. B. SS2015 oder WS20152016.');
        }

        $stmt = $libDb->prepare('SELECT * FROM base_semester WHERE semester=:semester');
        $stmt->bindValue(':semester', $_REQUEST['semester']);
        $stmt->execute();
        $semesterRow = $stmt->fetch(PDO::FETCH_ASSOC);

        $semesterRow = $libDb->updateRow($fields, $_REQUEST, 'base_semester', ['semester' => $_REQUEST['semester']]);
    }
    // No action
    else {
        $stmt = $libDb->prepare('SELECT * FROM base_semester WHERE semester=:semester');
        $stmt->bindValue(':semester', $_REQUEST['semester']);
        $stmt->execute();
        $semesterRow = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Perform image upload
    // Was a file uploaded?
    if (isset($_POST['formType']) && $_POST['formType'] == 'semesterCoverUpload') {
        if ($semesterRow['semester'] != '') {
            $libImage->saveSemesterCoverByFilesArray($semesterRow['semester'], 'semestercover');
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'semesterCoverDelete') {
        if ($semesterRow['semester'] != '') {
            $libImage->deleteSemesterCover($semesterRow['semester']);
        }
    }


    /**
    *
    * Introductory text
    *
    */
    echo '<h1>Semester</h1>';

    echo $libString->getErrorBoxText();
    echo $libString->getNotificationBoxText();

    echo '<p class="mb-4">Hier können sämtliche Daten eines Semesters bearbeitet werden. Diese Seite ist nur für den Internetwart zugänglich, weil über die Vergabe von Vorstands- und Wartsposten im Semester die Zugangsberechtigungen geregelt werden.</p>';
    echo '<hr />';

    /**
    *
    * Deletion option
    *
    */
    if ($semesterRow['semester'] != '') {
        echo '<form class="mb-4" method="post" action="index.php?pid=intranet_admin_semesters" onsubmit="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">';
        echo '<input type="hidden" name="action" value="delete" />';
        echo '<input type="hidden" name="semester" value="' .$semesterRow['semester']. '" />';
        echo '<button type="submit" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer"><i class="fa fa-trash" aria-hidden="true"></i> Datensatz löschen</button>';
        echo '</form>';
    }

    echo '<div class="row">';
    echo '<div class="col-sm-9">';


    /**
    *
    * Start form output
    *
    */
    if ($action == 'blank') {
        $extraActionParam = "&amp;action=insert";
    } else {
        $extraActionParam = "&amp;action=update";
    }

    echo '<div class="card">';
    echo '<div class="card-body">';
    echo '<form action="index.php?pid=intranet_admin_semester' .$extraActionParam. '" method="post">';
    echo '<fieldset>';
    echo '<input type="hidden" name="formType" value="semesterData" />';
    echo '<input type="hidden" name="semester" value="' .$semesterRow['semester']. '" />';

    $semesterDisabled = false;

    if ($action != 'blank') {
        $semesterDisabled = true;
    }

    $libForm->printTextInput('semester', 'Semester', $semesterRow['semester'], 'text', $semesterDisabled);

    // Board
    echo '<h2>Vorstand</h2>';
    $libForm->printMembersDropDownBox('senior', 'Senior', $semesterRow['senior']);
    $libForm->printBoolSelectBox('sen_dech', 'Senior Decharge', $semesterRow['sen_dech']);
    $libForm->printMembersDropDownBox('consenior', 'Consenior', $semesterRow['consenior']);
    $libForm->printBoolSelectBox('con_dech', 'Consenior Decharge', $semesterRow['con_dech']);
    $libForm->printMembersDropDownBox('fuchsmajor', 'Fuchsmajor', $semesterRow['fuchsmajor']);
    $libForm->printBoolSelectBox('fm_dech', 'Fuchsmajor Decharge', $semesterRow['fm_dech']);
    $libForm->printMembersDropDownBox('scriptor', 'Scriptor', $semesterRow['scriptor']);
    $libForm->printBoolSelectBox('scr_dech', 'Scriptor Decharge', $semesterRow['scr_dech']);
    $libForm->printMembersDropDownBox('quaestor', 'Quaestor', $semesterRow['quaestor']);
    $libForm->printBoolSelectBox('quaes_dech', 'Quaestor Decharge', $semesterRow['quaes_dech']);
    $libForm->printMembersDropDownBox('jubelsenior', 'Jubelsenior', $semesterRow['jubelsenior']);
    $libForm->printBoolSelectBox('jubelsen_dech', 'Jubelsenior Decharge', $semesterRow['jubelsen_dech']);
    $libForm->printMembersDropDownBox('fuchsmajor2', 'Fuchsmajor 2', $semesterRow['fuchsmajor2']);
    $libForm->printBoolSelectBox('fm2_dech', 'Fuchsmajor 2 Decharge', $semesterRow['fm2_dech']);

    echo '<h2>Philister-Vorstand</h2>';
    $libForm->printMembersDropDownBox('ahv_senior', 'AHV Senior', $semesterRow['ahv_senior']);
    $libForm->printMembersDropDownBox('ahv_consenior', 'AHV Consenior', $semesterRow['ahv_consenior']);
    $libForm->printMembersDropDownBox('ahv_keilbeauftragter', 'AHV Keilbeauftragter', $semesterRow['ahv_keilbeauftragter']);
    $libForm->printMembersDropDownBox('ahv_scriptor', 'AHV Scriptor', $semesterRow['ahv_scriptor']);
    $libForm->printMembersDropDownBox('ahv_quaestor', 'AHV Quaestor', $semesterRow['ahv_quaestor']);
    $libForm->printMembersDropDownBox('ahv_beisitzer1', 'AHV Beisitzer 1', $semesterRow['ahv_beisitzer1']);
    $libForm->printMembersDropDownBox('ahv_beisitzer2', 'AHV Beisitzer 2', $semesterRow['ahv_beisitzer2']);

    echo '<h2>Hausvereins-Vorstand</h2>';
    $libForm->printMembersDropDownBox('hv_vorsitzender', 'HV Vorsitzender', $semesterRow['hv_vorsitzender']);
    $libForm->printMembersDropDownBox('hv_kassierer', 'HV Kassierer', $semesterRow['hv_kassierer']);
    $libForm->printMembersDropDownBox('hv_beisitzer1', 'HV Beisitzer 1', $semesterRow['hv_beisitzer1']);
    $libForm->printMembersDropDownBox('hv_beisitzer2', 'HV Beisitzer 2', $semesterRow['hv_beisitzer2']);

    echo '<h2>Warte</h2>';
    $libForm->printMembersDropDownBox('archivar', 'Archivar', $semesterRow['archivar']);
    $libForm->printMembersDropDownBox('ausflugswart', 'Ausflugswart', $semesterRow['ausflugswart']);
    $libForm->printMembersDropDownBox('bierwart', 'Bierwart', $semesterRow['bierwart']);
    $libForm->printMembersDropDownBox('bootshauswart', 'Bootshauswart', $semesterRow['bootshauswart']);
    $libForm->printMembersDropDownBox('couleurartikelwart', 'Couleurartikelwart', $semesterRow['couleurartikelwart']);
    $libForm->printMembersDropDownBox('dachverbandsberichterstatter', 'Dachverbandsberichterstatter', $semesterRow['dachverbandsberichterstatter']);
    $libForm->printMembersDropDownBox('datenpflegewart', 'Datenpflegewart', $semesterRow['datenpflegewart']);
    $libForm->printMembersDropDownBox('fechtwart', 'Fechtwart', $semesterRow['fechtwart']);
    $libForm->printMembersDropDownBox('ferienordner', 'Ferienordner', $semesterRow['ferienordner']);
    $libForm->printMembersDropDownBox('fotowart', 'Fotowart', $semesterRow['fotowart']);
    $libForm->printMembersDropDownBox('hauswart', 'Hauswart', $semesterRow['hauswart']);
    $libForm->printMembersDropDownBox('huettenwart', 'Hüttenwart', $semesterRow['huettenwart']);
    $libForm->printMembersDropDownBox('internetwart', 'Internetwart', $semesterRow['internetwart']);
    $libForm->printMembersDropDownBox('kuehlschrankwart', 'Kühlschrankwart', $semesterRow['kuehlschrankwart']);
    $libForm->printMembersDropDownBox('musikwart', 'Musikwart', $semesterRow['musikwart']);
    $libForm->printMembersDropDownBox('redaktionswart', 'Redaktionswart', $semesterRow['redaktionswart']);
    $libForm->printMembersDropDownBox('sportwart', 'Sportwart', $semesterRow['sportwart']);
    $libForm->printMembersDropDownBox('stammtischwart', 'Stammtischwart', $semesterRow['stammtischwart']);
    $libForm->printMembersDropDownBox('technikwart', 'Technikwart', $semesterRow['technikwart']);
    $libForm->printMembersDropDownBox('thekenwart', 'Thekenwart', $semesterRow['thekenwart']);
    $libForm->printMembersDropDownBox('wirtschaftskassenwart', 'Wirtschaftskassenwart', $semesterRow['wirtschaftskassenwart']);
    $libForm->printMembersDropDownBox('wichswart', 'Wichswart', $semesterRow['wichswart']);

    $libForm->printMembersDropDownBox('vop', 'VOP', $semesterRow['vop']);
    $libForm->printMembersDropDownBox('vvop', 'VVOP', $semesterRow['vvop']);
    $libForm->printMembersDropDownBox('vopxx', 'VOPxx', $semesterRow['vopxx']);
    $libForm->printMembersDropDownBox('vopxxx', 'VOPxxx', $semesterRow['vopxxx']);
    $libForm->printMembersDropDownBox('vopxxxx', 'VOPxxxx', $semesterRow['vopxxxx']);

    echo '<input type="hidden" name="form_complete" value="1" />';

    $libForm->printSubmitButton('Speichern');

    echo '</fieldset>';
    echo '</form>';
    echo '</div>';
    echo '</div>';

    echo '</div>';
    echo '<div class="col-sm-3">';

    /**
    *
    * Show photo form
    *
    */
    if ($action != 'blank' && $semesterRow['semester'] != '') {
        echo '<div class="d-block mx-auto">';
        echo '<div class="img-box">';

        $hasSemesterCover = $libTime->hasSemesterCover($semesterRow['semester']);

        if ($hasSemesterCover) {
            echo '<span class="delete-icon-box">';
            echo '<form method="post" action="index.php?pid=intranet_admin_semester" class="d-inline" onsubmit="return confirm(\'Willst Du das Semestercover wirklich löschen?\')">';
            echo '<input type="hidden" name="action" value="semesterCoverDelete" />';
            echo '<input type="hidden" name="semester" value="' .$semesterRow['semester']. '" />';
            echo '<button type="submit" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer"><i class="fa fa-trash" aria-hidden="true"></i></button>';
            echo '</form>';
            echo '</span>';
        }

        echo $libTime->getSemesterCoverString($semesterRow['semester']);
        echo '</div>';
        echo '</div>';

        //image upload form
        echo '<form method="post" enctype="multipart/form-data" action="index.php?pid=intranet_admin_semester&amp;semester='. $semesterRow['semester'] .'" class="text-center">';
        echo '<input type="hidden" name="formType" value="semesterCoverUpload" />';
        $libForm->printFileUpload('semestercover', 'Semestercover hochladen', false, false, [], ['image/jpeg']);
        echo '</form>';
    }

    echo '</div>';
    echo '</div>';
}
