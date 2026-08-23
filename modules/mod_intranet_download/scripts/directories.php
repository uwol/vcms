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


if (!$libGenericStorage->attributeExistsInCurrentModule('preselect_rights')) {
    $libGenericStorage->saveValueInCurrentModule('preselect_rights', 1);
}


if (!isset($_SESSION['openFolders']) || !is_array($_SESSION['openFolders'])) {
    $_SESSION['openFolders'] = [];
}

$rootFolderPathString = 'custom/intranet/downloads';
$rootFolderAbsolutePathString = $libFilesystem->getAbsolutePath($rootFolderPathString);

/*
* pre scan actions
*/
foreach ($libAuth->getOffices() as $office) {
    if (!is_dir($rootFolderAbsolutePathString. '/' .$office)) {
        mkdir($rootFolderAbsolutePathString. '/' .$office);
    }
}

if (isset($_GET['action']) && isset($_GET['hash']) && $_GET['action'] == 'open') {
    $_SESSION['openFolders'][$_GET['hash']] = 1;
} elseif (isset($_GET['action']) && isset($_GET['hash']) && $_GET['action'] == 'close') {
    unset($_SESSION['openFolders'][$_GET['hash']]);
}


$rootFolderObject = new \vcms\filesystem\Folder('', '/', $rootFolderAbsolutePathString);
$hashes = $rootFolderObject->getHashMap();

/*
* actions
*/

//delete file
if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['hash']) && isset($hashes[$_POST['hash']])) {
    $element = $hashes[$_POST['hash']];

    if (in_array($element->owningOffice, $libAuth->getOffices())) {
        $element->delete();
        $libGlobal->notificationTexts[] = 'Das Element wurde gelöscht.';
    } else {
        $libGlobal->errorTexts[] = 'Du hast keine Löschberechtigung.';
    }
}
//upload file
elseif (isset($_POST['action']) && $_POST['action'] == 'upload' && isset($_POST['hash']) && isset($hashes[$_POST['hash']])) {
    $folder = $hashes[$_POST['hash']];

    if (in_array($folder->owningOffice, $libAuth->getOffices())) {
        if (isset($_POST['gruppen']) && count($_POST['gruppen']) > 0) {
            if (isset($_FILES['file']['tmp_name']) && $_FILES['file']['tmp_name'] != '') {
                $groupArray = array_merge($_POST['gruppen'], [$libAuth->getGroup()]);
                if ($folder->addFile($_FILES['file']['tmp_name'], $_FILES['file']['name'], $groupArray)) {
                    $libGlobal->notificationTexts[] = 'Die Datei wurde hochgeladen.';
                    $rootFolderObject->scanFileSystem();
                } else {
                    $libGlobal->errorTexts[] = 'Die Datei konnte nicht hochgeladen werden. Dieser Dateityp ist aus Sicherheitsgründen nicht erlaubt.';
                }
            }
        } else {
            $libGlobal->errorTexts[] = 'Du hast keine Gruppe mit Leseberechtigung ausgewählt.';
        }
    } else {
        $libGlobal->errorTexts[] = 'Du darfst die Datei nicht in diesen Ordner hochladen.';
    }
}
// new folder
elseif (isset($_POST['action']) && $_POST['action'] == "newFolder" && isset($_POST['hash']) && isset($hashes[$_POST['hash']])) {
    $folder = $hashes[$_POST['hash']];

    if (in_array($folder->owningOffice, $libAuth->getOffices())) {
        $folder->addFolder($_POST['foldername'] ?? '');
        $libGlobal->notificationTexts[] = 'Der Ordner wurde angelegt.';
    } else {
        $libGlobal->errorTexts[] = 'Du darfst in diesem Ordner keinen Unterordner anlegen.';
    }
}


/*
* output
*/
echo '<h1>Dateien</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<div class="row">';

listFolderContentRec($rootFolderObject, true);

echo '</div>';


if (!empty($libAuth->getOffices())) {
    /*
    * upload form
    */
    echo '<h2>Datei hochladen</h2>';

    echo '<div class="panel panel-default">';
    echo '<div class="panel-body">';
    echo '<form action="index.php?pid=intranet_directories" method="post" enctype="multipart/form-data" class="form-horizontal">';
    echo '<fieldset>';
    echo '<input type="hidden" name="action" value="upload" />';

    echo '<div class="form-group">';
    echo '<label for="hash" class="col-sm-3 control-label">in den Ordner</label>';
    echo '<div class="col-sm-3"><select name="hash" class="form-control">';

    foreach ($rootFolderObject->getNestedFoldersRec() as $folderElement) {
        if (in_array($folderElement->owningOffice, $libAuth->getOffices())) {
            echo '<option value="' .$folderElement->getHash(). '">' .$libString->protectXSS((string) $folderElement->name). '</option>';
        }
    }

    echo '</select></div>';
    echo '</div>';


    echo '<div class="form-group">';
    echo '<label class="col-sm-3 control-label">mit Leserecht für</label>';
    echo '<div class="col-sm-9">';

    $stmt = $libDb->prepare("SELECT * FROM base_gruppe ORDER BY bezeichnung");
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['bezeichnung'] != "X" && $row['bezeichnung'] != "T" && $row['bezeichnung'] != "V") {
            echo '<div class="checkbox"><label><input type="checkbox" name="gruppen[]" value="' .$libString->protectXSS($row['bezeichnung']). '"';

            if ($libGenericStorage->loadValueInCurrentModule('preselect_rights') == 1) {
                echo ' checked="checked"';
            }

            echo '/>';
            echo $libString->protectXSS($row['bezeichnung']). ' - ' .$libString->protectXSS((string) $row['beschreibung']);
            echo '</label></div>';
        }
    }

    echo '</div></div>';

    echo '<div class="form-group">';
    echo '<div class="col-sm-offset-3 col-sm-3">';
    echo '<label class="btn btn-default btn-file"><i class="fa fa-upload" aria-hidden="true"></i> Datei hochladen';
    echo '<input type="file" name="file" onchange="this.form.submit()" style="display:none">';
    echo '</label>';
    echo '</div>';
    echo '</div>';

    echo '</fieldset>';
    echo '</form>';
    echo '</div>';
    echo '</div>';


    /*
    * new folder form
    */
    echo '<h2>Ordner anlegen</h2>';

    echo '<div class="panel panel-default">';
    echo '<div class="panel-body">';
    echo '<form action="index.php?pid=intranet_directories" method="post" class="form-horizontal">';
    echo '<fieldset>';
    echo '<input type="hidden" name="action" value="newFolder" />';

    echo '<div class="form-group">';
    echo '<label for="foldername" class="col-sm-3 control-label">Neuen Ordner</label>';
    echo '<div class="col-sm-3"><input type="text" id="foldername" name="foldername" class="form-control" /></div>';
    echo '</div>';

    echo '<div class="form-group">';
    echo '<label for="hash" class="col-sm-3 control-label">in Ordner</label>';
    echo '<div class="col-sm-3"><select name="hash" class="form-control">';

    foreach ($rootFolderObject->getNestedFoldersRec() as $folderElement) {
        if (in_array($folderElement->owningOffice, $libAuth->getOffices())) {
            echo '<option value="' .$folderElement->getHash(). '">' .$libString->protectXSS((string) $folderElement->name). '</option>';
        }
    }

    echo '</select></div>';
    echo '</div>';

    echo '<div class="form-group">';
    echo '<div class="col-sm-offset-3 col-sm-3">';
    echo '<button type="submit" class="btn btn-default"><i class="fa fa-plus" aria-hidden="true"></i> anlegen</button>';
    echo '</div>';
    echo '</div>';

    echo '</form>';
    echo '</div>';
    echo '</div>';
}



/*
* functions
*/
function listFolderContentRec(&$rootFolderObject, $firstLevel)
{
    global $libAuth, $libModuleHandler, $libString;

    echo '<div style="margin-left:1.5em">';

    foreach ($rootFolderObject->nestedFolderElements as $folderElement) {
        //folder?
        if ($folderElement->type == 1) {
            if (!$folderElement->isOfficeRootFolder() || $folderElement->hasNestedFolderElements()) {
                if ($firstLevel) {
                    echo '<div class="col-md-6">';
                    echo '<div class="panel panel-default">';
                    echo '<div class="panel-body">';
                }

                if ($folderElement->isOpen) {
                    echo '<i class="fa fa-lg fa-fw fa-folder-open-o" aria-hidden="true"></i> ';
                    echo '<a href="index.php?pid=intranet_directories&amp;action=close&amp;hash=' .$folderElement->getHash(). '">';
                } else {
                    echo '<i class="fa fa-lg fa-fw fa-folder-o" aria-hidden="true"></i> ';
                    echo '<a href="index.php?pid=intranet_directories&amp;action=open&amp;hash=' .$folderElement->getHash(). '">';
                }

                echo $libString->protectXSS((string) $folderElement->name);
                echo '</a>';

                $size = $folderElement->getSize();

                if ($size > 0) {
                    echo ' <span class="text-muted"><small>' .getSizeString($folderElement->getSize()). '</small></span>';
                }

                if ($folderElement->isDeleteable() && in_array($folderElement->owningOffice, $libAuth->getOffices())) {
                    echo ' <form method="post" action="index.php?pid=intranet_directories" class="d-inline" onsubmit="return confirm(\'Willst Du den Ordner wirklich löschen?\')">';
                    echo '<input type="hidden" name="action" value="delete" />';
                    echo '<input type="hidden" name="hash" value="' .$folderElement->getHash(). '" />';
                    echo '<button type="submit" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer"><i class="fa fa-trash" aria-hidden="true"></i></button>';
                    echo '</form>';
                }

                echo '<br />';

                if ($folderElement->isOpen) {
                    listFolderContentRec($folderElement, false);
                }

                if ($firstLevel) {
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            }
        }
        //file & readable?
        elseif ($folderElement->type == 2 && in_array($libAuth->getGroup(), $folderElement->readGroups)) {
            $extension = $folderElement->getExtension();

            switch ($extension) {
                case 'doc':
                case 'docx':
                    echo '<i class="fa fa-lg fa-fw fa-file-word-o" aria-hidden="true"></i>';
                    break;
                case 'xls':
                case 'xlsx':
                    echo '<i class="fa fa-lg fa-fw fa-file-excel-o" aria-hidden="true"></i>';
                    break;
                case 'ppt':
                case 'pptx':
                    echo '<i class="fa fa-lg fa-fw fa-file-powerpoint-o" aria-hidden="true"></i>';
                    break;
                case 'pdf':
                    echo '<i class="fa fa-lg fa-fw fa-file-pdf-o" aria-hidden="true"></i>';
                    break;
                case 'cdr':
                case 'jpg':
                case 'jpeg':
                case 'gif':
                case 'png':
                case 'svg':
                    echo '<i class="fa fa-lg fa-fw fa-file-image-o" aria-hidden="true"></i>';
                    break;
                case 'txt':
                    echo '<i class="fa fa-lg fa-fw fa-file-text-o" aria-hidden="true"></i>';
                    break;
                case 'aac':
                case 'mp3':
                case 'wav':
                    echo '<i class="fa fa-lg fa-fw fa-file-audio-o" aria-hidden="true"></i>';
                    break;
                case 'mp4':
                case 'xvid':
                    echo '<i class="fa fa-lg fa-fw fa-file-video-o" aria-hidden="true"></i>';
                    break;
                case 'html':
                case 'htm':
                case 'css':
                    echo '<i class="fa fa-lg fa-fw fa-file-code-o" aria-hidden="true"></i>';
                    break;
                default:
                    echo '<i class="fa fa-lg fa-fw fa-file-o" aria-hidden="true"></i>';
            }

            $fileName = $folderElement->getFilename();

            echo ' <a href="api.php?iid=intranet_download&amp;hash=' .$folderElement->getHash(). '">' .$libString->protectXSS((string) $fileName). '</a>';
            echo ' <span class="text-muted"><small>' .implode('', array_map([$libString, 'protectXSS'], (array) $folderElement->readGroups)). '</small></span>';
            echo ' <span class="text-muted"><small>' .getSizeString($folderElement->getSize()). '</small></span>';

            if (in_array($folderElement->owningOffice, $libAuth->getOffices())) {
                echo ' <form method="post" action="index.php?pid=intranet_directories" class="d-inline" onsubmit="return confirm(\'Willst Du die Datei wirklich löschen?\')">';
                echo '<input type="hidden" name="action" value="delete" />';
                echo '<input type="hidden" name="hash" value="' .$folderElement->getHash(). '" />';
                echo '<button type="submit" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer"><i class="fa fa-trash" aria-hidden="true"></i></button>';
                echo '</form>';
            }

            echo '<br />';
        }
    }

    echo '</div>';
}

function getSizeString($size)
{
    if ($size > 1000000) {
        return round($size / 1000000, 1). ' MB';
    } else {
        return round($size / 1000, 0). ' KB';
    }
}
