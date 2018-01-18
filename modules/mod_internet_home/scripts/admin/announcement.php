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


$aktion = '';

if(isset($_REQUEST['aktion'])){
	$aktion = $_REQUEST['aktion'];
}

$array = array();
$array['id'] = '';
//fields
$felder = array('startdatum', 'verfallsdatum', 'text');

/*
* actions
*/

//new event
if($aktion == 'blank'){
	$array['startdatum'] = date('Y-m-d H:i:s');
	$array['verfallsdatum'] = '0000-00-00 00:00:00';
	$array['text'] = '';
}
//blank data to be saved
elseif($aktion == 'insert'){
	if(!isset($_POST['form_complete']) || !$_POST['form_complete']){
		die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
	}

	$valueArray = $_REQUEST;
	$valueArray['startdatum'] = $libTime->assureMysqlDateTime($valueArray['startdatum']);

	if(((int) substr($valueArray['startdatum'], 0, 4)) < 1){
		$valueArray['startdatum'] = date('Y-m-d H:i:s');
	}

	$valueArray['verfallsdatum'] = $libTime->assureMysqlDateTime($valueArray['verfallsdatum']);
	$array = $libDb->insertRow($felder, $valueArray, 'mod_internethome_nachricht', array('id'=>''));
	$libGlobal->notificationTexts[] = 'Die Ankündigung wurde gespeichert.';
}
//modification
elseif($aktion == 'update'){
	if(!isset($_POST['form_complete']) || !$_POST['form_complete']){
		die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
	}

	$valueArray = $_REQUEST;
	$valueArray['startdatum'] = $libTime->assureMysqlDateTime($valueArray['startdatum']);

	if(((int) substr($valueArray['startdatum'], 0, 4)) < 1){
		$valueArray['startdatum'] = date('Y-m-d H:i:s');
	}

	$valueArray['verfallsdatum'] = $libTime->assureMysqlDateTime($valueArray['verfallsdatum']);
	$array = $libDb->updateRow($felder, $valueArray, 'mod_internethome_nachricht', array('id' => $_REQUEST['id']));
	$libGlobal->notificationTexts[] = 'Die Ankündigung wurde gespeichert.';
}
// select
else{
	$stmt = $libDb->prepare('SELECT * FROM mod_internethome_nachricht WHERE id=:id');
	$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
	$stmt->execute();
	$array = $stmt->fetch(PDO::FETCH_ASSOC);
}

//images
if(isset($_POST['formtyp']) && $_POST['formtyp'] == 'bildupload'){
	if($_FILES['bilddatei']['tmp_name'] != ''){
		$libImage->saveStartseitenBildByFilesArray($_REQUEST['id'], 'bilddatei');
	}
} elseif(isset($_GET['aktion']) && $_GET['aktion'] == 'bilddelete'){
	$libImage->deleteStartseitenBild($_REQUEST['id']);
}



/*
* output
*/

echo '<h1>Ankündigung</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<p>Hier können die Daten einer Ankündigung für die Startseite bearbeitet werden. Start- und Verfallsdatum müssen so gewählt werden, dass sich der Zeitraum ergibt, in dem die Ankündigung angezeigt werden soll.</p>';
echo '<p>Es können die folgenden <a href="http://de.wikipedia.org/wiki/Bbcode">BBCodes</a> verwendet werden: [b]fett[/b], [i]kursiv[/i], [url=http://www.wikipedia.de]Link[/url]</p>';
echo '<hr />';

/*
* deletion
*/
if($array['id'] != ''){
	echo '<p><a href="index.php?pid=intranet_admin_announcements&amp;aktion=delete&amp;id=' .$array['id']. '" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')"><i class="fa fa-trash" aria-hidden="true"></i> Datensatz löschen</a></p>';
}


echo '<div class="row">';
echo '<div class="col-sm-9">';


/*
* form
*/
if($aktion == 'blank'){
	$extraActionParam = '&amp;aktion=insert';
} else {
	$extraActionParam = '&amp;aktion=update';
}

echo '<form action="index.php?pid=intranet_admin_announcement' .$extraActionParam. '" method="post" class="form-horizontal">';
echo '<fieldset>';

echo '<input type="hidden" name="formtyp" value="newsdaten" />';
echo '<input type="hidden" name="id" value="' .$array['id']. '" />';

$libForm->printTextInput('id', 'Id', $array['id'], 'text', true);
$libForm->printTextInput('startdatum', 'Startdatum', $array['startdatum'], 'date');
$libForm->printTextInput('verfallsdatum', 'Verfallsdatum', $array['verfallsdatum'], 'date');
$libForm->printTextarea('text', 'Beschreibung', $array['text']);

echo '<input type="hidden" name="form_complete" value="1" />';

$libForm->printSubmitButton('Speichern');

echo '</fieldset>';
echo '</form>';


echo '</div>';
echo '<div class="col-sm-3">';

if((isset($_REQUEST['id']) && $_REQUEST['id'] != '') || $array['id'] != ''){
	if(isset($_REQUEST['id']) && $_REQUEST['id'] != ''){
		$array['id'] = $_REQUEST['id'];
	}

	$posssibleImage = $libModuleHandler->getModuleDirectory(). '/custom/img/' .$array['id']. '.jpg';

	if(is_file($posssibleImage)){
		echo '<div class="center-block">';
		echo '<div class="img-box">';

		echo '<span class="delete-icon-box">';
		echo '<a href="index.php?pid=intranet_admin_announcement&amp;id=' .$array['id']. '&amp;aktion=bilddelete">';
		echo '<i class="fa fa-trash" aria-hidden="true"></i>';
		echo '</a>';
		echo '</span>';

		echo '<img src="' .$posssibleImage. '" class="img-responsive center-block" alt="Veranstaltungsbild" />';
		echo '</div>';
		echo '</div>';
	}

	//image upload form
	echo '<form action="index.php?pid=intranet_admin_announcement&amp;id=' .$array['id']. '" method="post" enctype="multipart/form-data" class="form-horizontal text-center">';
	echo '<input type="hidden" name="formtyp" value="bildupload" />';
	$libForm->printFileUpload('bilddatei', 'Bild hochladen', false, false, array(), array('image/jpeg'));
	echo '</form>';
}

echo '</div>';
echo '</div>';
