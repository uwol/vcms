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


//the delete buttons are part of the surrounding save form, so deletion has to be checked first
if (isset($_POST['delete_target']) && $_POST['delete_target'] != '') {
    $target = explode('#', $_POST['delete_target']);

    if (count($target) == 3 && $target[0] != '' && $target[1] != '' && $target[2] != '') {
        $libGenericStorage->deleteArrayValue($target[0], $target[1], $target[2]);
        $libGlobal->notificationTexts[] = 'Der Wert wurde gelöscht.';
    }
} elseif (isset($_POST['form_complete']) && $_POST['form_complete'] && isset($_POST['action']) && $_POST['action'] == "save") {
    foreach ($_POST as $key => $value) {
        if ($key != 'form_complete') {
            $array = explode('#', $key);

            $moduleid = $array[0];

            $array_name = '';

            if (isset($array[1])) {
                $array_name = $array[1];
            }

            $position = '';

            if (isset($array[2])) {
                $position = $array[2];
            }

            if ($moduleid != "" && $array_name != "" && $position != "") {
                $libGenericStorage->saveArrayValue($moduleid, $array_name, $position, $value);
            }
        }
    }
}

echo '<h1>Konfiguration</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

$storage = $libGenericStorage->listAllArrayValues();

echo '<div class="card">';
echo '<div class="card-body">';
echo '<form action="index.php?pid=configuration" method="post">';
echo '<fieldset>';

//modules
foreach ($storage as $moduleid => $arrays) {
    echo '<h2>' .$libString->protectXSS($moduleid). '</h2>';

    //arrays
    foreach ($arrays as $array_name => $positionen) {
        //positions and values at that positions
        foreach ($positionen as $position => $value) {
            echo '<div class="row mb-3">';
            echo '<label class="col-sm-4 col-form-label">' .$libString->protectXSS($array_name). '</label>';

            echo '<div class="col-sm-1">';
            echo '<input type="text" name="' .$libString->protectXSS($moduleid) .'#'. $libString->protectXSS($array_name) .'#position' . '" value="' .$libString->protectXSS((string) $position). '" disabled="disabled" class="form-control form-control-sm" />';
            echo '</div>';

            echo '<div class="col-sm-6">';
            echo '<input type="text" name="'. $libString->protectXSS($moduleid) .'#'. $libString->protectXSS($array_name) .'#'. $libString->protectXSS((string) $position) .'#value" value="' .$libString->protectXSS((string) $value). '" class="form-control form-control-sm" />';
            echo '</div>';

            echo '<div class="col-sm-1">';
            echo '<div class="form-control-plaintext p-0">';
            echo '<button type="submit" name="delete_target" value="' .$libString->protectXSS($moduleid).'#'.$libString->protectXSS($array_name).'#'.$libString->protectXSS((string) $position). '" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer" onclick="return confirm(\'Willst Du den Eintrag wirklich löschen?\')"><i class="fa fa-trash fa-lg" aria-hidden="true"></i></button>';
            echo '</div>';
            echo '</div>';

            echo '</div>';
        }
    }
}

echo '<input type="hidden" name="action" value="save" />';
echo '<input type="hidden" name="form_complete" value="1" />';

$libForm->printSubmitButton('Speichern');

echo '</fieldset>';
echo '</form>';
echo '</div>';
echo '</div>';
