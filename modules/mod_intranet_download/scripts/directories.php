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

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();


require_once('lib/lib.php');


if(!$libGenericStorage->attributeExistsInCurrentModule('rightsPreselection')){
	$libGenericStorage->saveValueInCurrentModule('rightsPreselection', 1);
}


if(!isset($_SESSION['openFolders']) || !is_array($_SESSION['openFolders'])){
	$_SESSION['openFolders'] = array();
}

$rootFolderPathString = 'custom/intranet/downloads';

/*
* pre scan actions
*/
foreach($libAuth->getAemter() as $amt){
	if(!is_dir($rootFolderPathString. '/' .$amt)){
		mkdir($rootFolderPathString. '/' .$amt);
	}
}

if(isset($_GET['aktion']) && $_GET['aktion'] == 'open'){
	$_SESSION['openFolders'][$_GET['hash']] = 1;
} elseif(isset($_GET['aktion']) && $_GET['aktion'] == 'close'){
	unset($_SESSION['openFolders'][$_GET['hash']]);
}


$rootFolderObject = new Folder('', '/', $rootFolderPathString);
$hashes = $rootFolderObject->getHashMap();


/*
* actions
*/

//delete file
if(isset($_GET['aktion']) && $_GET['aktion'] == 'delete' && isset($_GET['hash'])){
	$element = $hashes[$_GET['hash']];

	if(in_array($element->owningAmt, $libAuth->getAemter())){
		$element->delete();
		$libGlobal->notificationTexts[] = 'Das Element ist gelöscht worden.';
	} else {
		$libGlobal->errorTexts[] = 'Du hast keine Löschberechtigung.';
	}
}
//upload file
elseif(isset($_POST['aktion']) && $_POST['aktion'] == 'upload' && isset($_POST['hash'])){
	$folder = $hashes[$_POST['hash']];

	if(in_array($folder->owningAmt, $libAuth->getAemter())){
		if(isset($_POST['gruppen']) && count($_POST['gruppen']) > 0){
			if($_FILES['datei']['tmp_name'] != ''){
				$groupArray = array_merge($_POST['gruppen'], array($libAuth->getGruppe()));
				$folder->addFile($_FILES['datei']['tmp_name'], $_FILES['datei']['name'], $groupArray);
				$libGlobal->notificationTexts[] = 'Die Datei wurde hochgeladen.';
				$rootFolderObject->scanFileSystem();
			}
		} else {
			$libGlobal->errorTexts[] = 'Du hast keine Gruppe mit Leseberechtigung ausgewählt.';
		}
	} else {
		$libGlobal->errorTexts[] = 'Du darfst die Datei nicht in diesen Ordner hochladen.';
	}
}
// new folder
elseif(isset($_POST['aktion']) && $_POST['aktion'] == "newfolder" && isset($_POST['hash'])){
	$folder = $hashes[$_POST['hash']];

	if(in_array($folder->owningAmt, $libAuth->getAemter())){
		$folder->addFolder($_POST['foldername']);
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

echo '<p>In diesem Bereich können Chargen und Warte Dateien zum Download anbieten. Die Ordner können durch Klicken geöffnet werden. Der Ausdruck hinter einer Datei gibt an, welche Gruppen ein Leserecht für die Datei besitzen. Durch z. B. den Ausdruck BCFGPW wird festgelegt, dass Burschen, Couleurdamen, Füchse, Gattinnen, Philister und Witwen ein Leserecht besitzen.</p>';

listFolderContentRec($rootFolderObject, true);



if(count($libAuth->getAemter()) > 0){
	/*
	* upload form
	*/
	echo '<h2>Datei hochladen</h2>';

	echo '<form action="index.php?pid=intranet_download_directories" method="post" enctype="multipart/form-data" class="form-horizontal">';
	echo '<fieldset>';
	echo '<input type="hidden" name="aktion" value="upload" />';

	echo '<div class="form-group">';
	echo '<label for="hash" class="col-sm-2 control-label">in den Ordner</label>';
	echo '<div class="col-sm-2"><select name="hash" class="form-control">';

	foreach($rootFolderObject->getNestedFoldersRec() as $folderElement){
		if(in_array($folderElement->owningAmt, $libAuth->getAemter())){
			echo '<option value="' .$folderElement->getHash(). '">'.$folderElement->name.'</option>';
		}
	}

	echo '</select></div>';
	echo '</div>';


	echo '<div class="form-group">';
	echo '<label class="col-sm-2 control-label">mit Leserecht für</label>';
	echo '<div class="col-sm-10">';

	$stmt = $libDb->prepare("SELECT * FROM base_gruppe ORDER BY bezeichnung");
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		if($row['bezeichnung'] != "X" && $row['bezeichnung'] != "T" && $row['bezeichnung'] != "V"){
			echo '<div class="checkbox"><label><input type="checkbox" name="gruppen[]" value="' .$row['bezeichnung']. '"';

			if($libGenericStorage->loadValueInCurrentModule('rightsPreselection') == 1){
				echo 'checked="checked"';
			}

			echo '/>';
			echo $row['bezeichnung'].' - ' .$row['beschreibung'];
			echo '</label></div>';
		}
	}

	echo '</div></div>';

	echo '<div class="form-group">';
	echo '<div class="col-sm-offset-2 col-sm-2">';
	echo '<label class="btn btn-default btn-file"><i class="fa fa-upload" aria-hidden="true"></i> Datei hochladen';
	echo '<input type="file" name="datei" onchange="this.form.submit()" style="display:none">';
	echo '</label>';
	echo '</div>';
	echo '</div>';

	echo '</fieldset>';
	echo '</form>';


	/*
	* new folder form
	*/
	echo '<h2>Ordner anlegen</h2>';

	echo '<form action="index.php?pid=intranet_download_directories" method="post" class="form-horizontal">';
	echo '<fieldset>';
	echo '<input type="hidden" name="aktion" value="newfolder" />';

	echo '<div class="form-group">';
	echo '<label for="foldername" class="col-sm-2 control-label">neuen Ordner</label>';
	echo '<div class="col-sm-2"><input type="text" id="foldername" name="foldername" class="form-control" /></div>';
	echo '</div>';

	echo '<div class="form-group">';
	echo '<label for="hash" class="col-sm-2 control-label">in Ordner</label>';
	echo '<div class="col-sm-2"><select name="hash" class="form-control">';

	foreach($rootFolderObject->getNestedFoldersRec() as $folderElement){
		if(in_array($folderElement->owningAmt, $libAuth->getAemter())){
			echo '<option value="' .$folderElement->getHash(). '">'.$folderElement->name.'</option>';
		}
	}

	echo '</select></div>';
	echo '</div>';

	echo '<div class="form-group">';
	echo '<div class="col-sm-offset-2 col-sm-2">';
	echo '<button type="submit" class="btn btn-default">anlegen</button>';
	echo '</div>';
	echo '</div>';

	echo '</form>';
}



/*
* functions
*/
function listFolderContentRec(&$rootFolderObject, $firstLevel){
	global $libAuth, $libModuleHandler;

	echo '<div style="margin-left:20px">';

	foreach($rootFolderObject->nestedFolderElements as $folderElement){
		//folder?
		if($folderElement->type == 1){
			if(!$folderElement->isAmtsRootFolder() || $folderElement->hasNestedFolderElements()){
				if($folderElement->isOpen){
					echo '<a href="index.php?pid=intranet_download_directories&amp;aktion=close&amp;hash=' .$folderElement->getHash(). '">';
					echo '<i class="fa fa-folder-open" aria-hidden="true"></i> ';
				} else{
					echo '<a href="index.php?pid=intranet_download_directories&amp;aktion=open&amp;hash=' .$folderElement->getHash(). '">';
					echo '<i class="fa fa-folder" aria-hidden="true"></i> ';
				}

				echo $folderElement->name;
				echo '</a>';

				$size = $folderElement->getSize();

				if($size > 0){
					echo ' - ' . getSizeString($folderElement->getSize());
				}

				if($folderElement->isDeleteable() && in_array($folderElement->owningAmt, $libAuth->getAemter())){
					echo ' <a href="index.php?pid=intranet_download_directories&amp;aktion=delete&amp;hash=' .$folderElement->getHash(). '" onclick="return confirm(\'Willst Du den Ordner wirklich löschen?\')"><i class="fa fa-trash" aria-hidden="true"></i></a>';
				}

				echo '<br />';

				if($folderElement->isOpen){
					listFolderContentRec($folderElement, false);
				}
			}
		}
		//file & readable?
		elseif($folderElement->type == 2 && in_array($libAuth->getGruppe(), $folderElement->readGroups)){
			$extension = $folderElement->getExtension();

			switch($extension){
				case 'doc':
				case 'docx':
					echo '<i class="fa fa-file-word-o" aria-hidden="true"></i>';
					break;
				case 'xls':
				case 'xlsx':
					echo '<i class="fa fa-file-excel-o" aria-hidden="true"></i>';
					break;
				case 'ppt':
				case 'pptx':
					echo '<i class="fa fa-file-powerpoint-o" aria-hidden="true"></i>';
					break;
				case 'pdf':
					echo '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>';
					break;
				case 'cdr':
				case 'jpg':
				case 'jpeg':
				case 'gif':
				case 'png':
				case 'svg':
					echo '<i class="fa fa-file-image-o" aria-hidden="true"></i>';
					break;
				case 'txt':
					echo '<i class="fa fa-file-text-o" aria-hidden="true"></i>';
					break;
				case 'aac':
				case 'mp3':
				case 'wav':
					echo '<i class="fa fa-file-audio-o" aria-hidden="true"></i>';
					break;
				case 'mp4':
				case 'xvid':
					echo '<i class="fa fa-file-video-o" aria-hidden="true"></i>';
					break;
				case 'html':
				case 'htm':
				case 'css':
					echo '<i class="fa fa-file-code-o" aria-hidden="true"></i>';
					break;
				default:
					echo '<i class="fa fa-file-o" aria-hidden="true"></i>';
			}

			$fileName = $folderElement->getFilename();

			echo ' <a href="inc.php?iid=intranet_downloads_download&amp;hash=' .$folderElement->getHash(). '">'. $fileName .'</a>';
			echo ' - ' . implode('', $folderElement->readGroups);
			echo ' - ' . getSizeString($folderElement->getSize());

			if(in_array($folderElement->owningAmt, $libAuth->getAemter())){
				echo ' <a href="index.php?pid=intranet_download_directories&amp;aktion=delete&amp;hash=' .$folderElement->getHash(). '" onclick="return confirm(\'Willst Du die Datei wirklich löschen?\')"><i class="fa fa-trash" aria-hidden="true"></i></a>';
			}

			echo '<br />';
		}
	}

	echo '</div>';
}

function getSizeString($size){
	if($size > 1000000){
		return round($size / 1000000, 1) . ' MB';
	} else {
		return round($size / 1000, 0) . ' KB';
	}
}
?>