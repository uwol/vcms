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


$action = '';

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
}

$array = [];
$array['id'] = '';
//fields
$fields = ['startdatum', 'verfallsdatum', 'text'];

/*
* actions
*/

//new event
if ($action == 'blank') {
    $array['startdatum'] = date('Y-m-d H:i:s');
    $array['verfallsdatum'] = '';
    $array['text'] = '';
}
//blank data to be saved
elseif ($action == 'insert') {
    if (!isset($_POST['form_complete']) || !$_POST['form_complete']) {
        die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
    }

    $valueArray = $_REQUEST;
    $valueArray['startdatum'] = $libTime->assureMysqlDateTime($valueArray['startdatum'] ?? '');

    if (((int) substr($valueArray['startdatum'], 0, 4)) < 1) {
        $valueArray['startdatum'] = date('Y-m-d H:i:s');
    }

    $valueArray['verfallsdatum'] = $libTime->assureMysqlDateTime($valueArray['verfallsdatum'] ?? '');
    $array = $libDb->insertRow($fields, $valueArray, 'mod_internethome_nachricht', ['id' => '']);
    $libGlobal->notificationTexts[] = 'Die Ankündigung wurde gespeichert.';
}
//modification
elseif ($action == 'update') {
    if (!isset($_POST['form_complete']) || !$_POST['form_complete']) {
        die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
    }

    $valueArray = $_REQUEST;
    $valueArray['startdatum'] = $libTime->assureMysqlDateTime($valueArray['startdatum'] ?? '');

    if (((int) substr($valueArray['startdatum'], 0, 4)) < 1) {
        $valueArray['startdatum'] = date('Y-m-d H:i:s');
    }

    $valueArray['verfallsdatum'] = $libTime->assureMysqlDateTime($valueArray['verfallsdatum'] ?? '');
    $array = $libDb->updateRow($fields, $valueArray, 'mod_internethome_nachricht', ['id' => $_REQUEST['id']]);
    $libGlobal->notificationTexts[] = 'Die Ankündigung wurde gespeichert.';
}
// select
else {
    $stmt = $libDb->prepare('SELECT * FROM mod_internethome_nachricht WHERE id=:id');
    $stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
    $stmt->execute();
    $array = $stmt->fetch(PDO::FETCH_ASSOC);
}

//images
if (isset($_POST['formType']) && $_POST['formType'] == 'imageUpload') {
    if ($_FILES['bilddatei']['tmp_name'] != '') {
        $libImage->saveHomeImageByFilesArray($_REQUEST['id'], 'bilddatei');
    }
} elseif (isset($_POST['action']) && $_POST['action'] == 'imageDelete') {
    $libImage->deleteHomeImage($_POST['id']);
}



/*
* output
*/

echo '<h1>Ankündigung</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<p class="mb-4">Hier können die Daten einer Ankündigung für die Startseite bearbeitet werden. Start- und Verfallsdatum müssen so gewählt werden, dass sich der Zeitraum ergibt, in dem die Ankündigung angezeigt werden soll.</p>';


/*
* deletion
*/
if ($array['id'] != '') {
    echo '<form class="mb-4" method="post" action="index.php?pid=intranet_admin_announcements" onsubmit="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">';
    echo '<input type="hidden" name="action" value="delete" />';
    echo '<input type="hidden" name="id" value="' .$array['id']. '" />';
    echo '<button type="submit" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer"><i class="fa fa-trash" aria-hidden="true"></i> Datensatz löschen</button>';
    echo '</form>';
}


echo '<div class="row">';
echo '<div class="col-sm-9">';


/*
* form
*/
if ($action == 'blank') {
    $extraActionParam = '&amp;action=insert';
} else {
    $extraActionParam = '&amp;action=update';
}

echo '<div class="panel panel-default">';
echo '<div class="panel-body">';
echo '<form action="index.php?pid=intranet_admin_announcement' .$extraActionParam. '" method="post" class="form-horizontal">';
echo '<fieldset>';

echo '<input type="hidden" name="formType" value="newsData" />';
echo '<input type="hidden" name="id" value="' .$array['id']. '" />';

$libForm->printTextInput('id', 'Id', $array['id'], 'text', true);
$libForm->printDateTimeInput('startdatum', 'Anzeigen ab', $array['startdatum']);
$libForm->printDateTimeInput('verfallsdatum', 'Anzeigen bis (optional)', $array['verfallsdatum']);
$libForm->printTextarea('text', 'Beschreibung', $array['text']);

echo '<input type="hidden" name="form_complete" value="1" />';

$libForm->printSubmitButton('Speichern');

echo '</fieldset>';
echo '</form>';
echo '</div>';
echo '</div>';

echo '</div>';
echo '<div class="col-sm-3">';

if ((isset($_REQUEST['id']) && $_REQUEST['id'] != '') || $array['id'] != '') {
    if (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
        $array['id'] = $_REQUEST['id'];
    }

    $posssibleImage = $libModuleHandler->getModuleDirectory(). '/custom/img/' .$array['id']. '.jpg';

    if (is_file($posssibleImage)) {
        echo '<div class="center-block">';
        echo '<div class="img-box">';

        echo '<span class="delete-icon-box">';
        echo '<form method="post" action="index.php?pid=intranet_admin_announcement" class="d-inline">';
        echo '<input type="hidden" name="action" value="imageDelete" />';
        echo '<input type="hidden" name="id" value="' .$array['id']. '" />';
        echo '<button type="submit" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer"><i class="fa fa-trash" aria-hidden="true"></i></button>';
        echo '</form>';
        echo '</span>';

        echo '<img src="' .$posssibleImage. '" class="img-responsive center-block" alt="Veranstaltungsbild" />';
        echo '</div>';
        echo '</div>';
    }

    //image upload form
    echo '<form action="index.php?pid=intranet_admin_announcement&amp;id=' .$array['id']. '" method="post" enctype="multipart/form-data" class="form-horizontal text-center">';
    echo '<input type="hidden" name="formType" value="imageUpload" />';
    $libForm->printFileUpload('bilddatei', 'Bild hochladen', false, false, [], ['image/jpeg']);
    echo '</form>';
}

echo '</div>';
echo '</div>';
