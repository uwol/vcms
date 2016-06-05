<?php
/*
This file is part of VCMS.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
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
	echo '<label class="btn btn-default btn-file">Datei hochladen';
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
					echo '<img src="styles/icons/file/folder_open.svg" alt="F" class="icon_small" /> ';
				} else{
					echo '<a href="index.php?pid=intranet_download_directories&amp;aktion=open&amp;hash=' .$folderElement->getHash(). '">';
					echo '<img src="styles/icons/file/folder.svg" alt="F" class="icon_small" /> ';
				}

				echo $folderElement->name;
				echo '</a>';

				$size = $folderElement->getSize();

				if($size > 0){
					echo ' - ' . getSizeString($folderElement->getSize());
				}

				if($folderElement->isDeleteable() && in_array($folderElement->owningAmt, $libAuth->getAemter())){
					echo ' <a href="index.php?pid=intranet_download_directories&amp;aktion=delete&amp;hash=' .$folderElement->getHash(). '" onclick="return confirm(\'Willst Du den Ordner wirklich löschen?\')"><img src="styles/icons/basic/delete.svg" class="icon_small" alt="löschen" /></a>';
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

			echo '<img src="' . $libModuleHandler->getModuleDirectory(). '/img/';

			switch($extension){
				case "doc": echo 'doc.png'; break;
				case "xls": echo 'xls.jpg'; break;
				case "ppt": echo 'ppt.jpg'; break;

				case "docx": echo 'docx.png'; break;
				case "pptx": echo 'pptx.png'; break;
				case "xlsx": echo 'xlsx.png'; break;

				case "odt": echo 'odt.png'; break;
				case "odp": echo 'odp.png'; break;
				case "ods": echo 'ods.png'; break;
				case "odg": echo 'odg.png'; break;

				case "jpg": echo 'jpeg.jpg'; break;
				case "jpeg": echo 'jpeg.jpg'; break;

				case "cdr": echo 'cdr.png'; break;
				case "pdf": echo 'pdf.png'; break;
				case "psd": echo 'psd.png'; break;
				case "rar": echo 'rar.png'; break;
				case "zip": echo 'zip.jpg'; break;

				default : echo 'other.jpg'; break;
			}

			echo '" class="icon_small" alt="Icon" />';

			$fileName = $folderElement->getFilename();

			echo ' <a href="inc.php?iid=intranet_downloads_download&amp;hash=' .$folderElement->getHash(). '">'. $fileName .'</a>';
			echo ' - ' . implode('', $folderElement->readGroups);
			echo ' - ' . getSizeString($folderElement->getSize());

			if(in_array($folderElement->owningAmt, $libAuth->getAemter())){
				echo ' <a href="index.php?pid=intranet_download_directories&amp;aktion=delete&amp;hash=' .$folderElement->getHash(). '" onclick="return confirm(\'Willst Du die Datei wirklich löschen?\')"><img src="styles/icons/basic/delete.svg" class="icon_small" alt="löschen" /></a>';
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