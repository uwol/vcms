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


$libDb->connect();

header('Content-Type: application/json; charset=utf-8');

if ($libAuth->isLoggedin() &&
        isset($_REQUEST['veranstaltungId']) && is_numeric($_REQUEST['veranstaltungId']) &&
        preg_match("/^[0-9]+$/", $_REQUEST['veranstaltungId']) && isset($_FILES['files']['name'])) {

    $allowedExtensions = ['jpg', 'jpeg'];
    $numerOfFiles = count($_FILES['files']['name']);
    $filesResult = [];

    for ($i = 0; $i < $numerOfFiles; $i++) {
        $fileResult = handleFileUpload($i, $allowedExtensions);
        $filesResult[] = $fileResult;
    }

    $result = [];
    $result['files'] = $filesResult;

    echo json_encode($result, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
} else {
    // The uploader parses every response as JSON, so an unusable request has to
    // be answered with a document as well instead of with an empty body.
    $result = [];
    $result['files'] = [];
    $result['error'] = 'Die Anfrage enthält keine gültige Veranstaltung oder keine Dateien.';

    echo json_encode($result, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}



function handleFileUpload($i, $allowedExtensions)
{
    global $libGlobal, $libImage;

    $name = $_FILES['files']['name'][$i];
    $tmp_name = $_FILES['files']['tmp_name'][$i];
    $size = $_FILES['files']['size'][$i];

    $pathinfo = pathinfo((string) $name);
    $filename = isset($pathinfo['filename']) ? $pathinfo['filename'] : '';
    $ext = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';

    $result = [];

    if (is_array($allowedExtensions) && !in_array(strtolower($ext), $allowedExtensions)) {
        $allowedExtensionsString = implode(', ', $allowedExtensions);
        $result['error'] = 'Die Dateiendung ist nicht korrekt. Erlaubt sind ' .$allowedExtensionsString. '.';
    } else {
        $libImage->saveEventPhotoByAjax($_REQUEST['veranstaltungId'], $filename. '.' .$ext, $tmp_name);

        if (count($libGlobal->errorTexts) > 0) {
            $result['error'] = implode(' ', $libGlobal->errorTexts);
        } else {
            $result['size'] = $size;
        }
    }

    $result['name'] = $name;
    return $result;
}
