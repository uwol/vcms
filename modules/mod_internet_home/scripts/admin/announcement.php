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
	if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
		die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
	}

	$valueArray = $_REQUEST;
	$valueArray['startdatum'] = $libTime->assureMysqlDateTime($valueArray['startdatum']);

	if(((int) substr($valueArray['startdatum'], 0, 4)) < 1){
		$valueArray['startdatum'] = date('Y-m-d H:i:s');
	}

	$valueArray['verfallsdatum'] = $libTime->assureMysqlDateTime($valueArray['verfallsdatum']);
	$array = $libDb->insertRow($felder, $valueArray, 'mod_internethome_nachricht', array('id'=>''));
	$libGlobal->notificationTexts[] = 'Die Ankündigung ist gespeichert worden.';
}
//modification
elseif($aktion == 'update'){
	if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
		die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
	}

	$valueArray = $_REQUEST;
	$valueArray['startdatum'] = $libTime->assureMysqlDateTime($valueArray['startdatum']);

	if(((int) substr($valueArray['startdatum'], 0, 4)) < 1){
		$valueArray['startdatum'] = date('Y-m-d H:i:s');
	}

	$valueArray['verfallsdatum'] = $libTime->assureMysqlDateTime($valueArray['verfallsdatum']);
	$array = $libDb->updateRow($felder, $valueArray, 'mod_internethome_nachricht', array('id' => $_REQUEST['id']));
	$libGlobal->notificationTexts[] = 'Die Ankündigung ist gespeichert worden.';
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
		$libImage = new LibImage($libTime, $libGenericStorage);
		$libImage->saveStartseitenBildByFilesArray($_REQUEST['id'], 'bilddatei');
	}
} elseif(isset($_GET['aktion']) && $_GET['aktion'] == 'bilddelete'){
	$libImage = new LibImage($libTime, $libGenericStorage);
	$libImage->deleteStartseitenBild($_REQUEST['id']);
}



/*
* output
*/

echo '<h1>Ankündigung</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<p>Hier können die Daten einer Ankündigung auf der Startseite bearbeitet werden. Das Startdatum und Verfallsdatum müssen so gewählt werden, dass sich der Zeitraum ergibt, in dem die Ankündigung angezeigt werden soll. Falls der Zeitraum der Ankündigung nicht begrenzt sein soll, kann das Verfallsdatum auf 0000-00-00 00:00:00 belassen werden. Die Ankündigung wird automatisch in das Archiv verschoben, wenn sie verfällt oder neuere Ankündigung existieren.</p>';

echo '<p>Anstatt von HTML können die folgenden <a href="http://de.wikipedia.org/wiki/Bbcode">BBCodes</a> verwendet werden: [b]fett[/b], [i]kursiv[/i], [url=http://www.wikipedia.de]Link[/url]</li>';

/*
* deletion
*/
if($array['id'] != ''){
	echo '<p><a href="index.php?pid=intranet_internethome_nachricht_adminliste&amp;aktion=delete&amp;id=' .$array['id']. '" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">Datensatz löschen</a></p>';
}

/*
* form
*/
if($aktion == 'blank'){
	$extraActionParam = '&amp;aktion=insert';
} else {
	$extraActionParam = '&amp;aktion=update';
}

echo '<form action="index.php?pid=intranet_internethome_nachricht_adminankuendigung' .$extraActionParam. '" method="post" class="form-horizontal">';
echo '<fieldset>';

echo '<input type="hidden" name="formtyp" value="newsdaten" />';
echo '<input type="hidden" name="id" value="' .$array['id']. '" />';

$libForm->printTextInput('id', 'Id', $array['id'], 'text', true);
$libForm->printTextInput('startdatum', 'Startdatum', $array['startdatum'], 'date');
$libForm->printTextInput('verfallsdatum', 'Verfallsdatum', $array['verfallsdatum'], 'date');
$libForm->printTextarea('text', 'Beschreibung', $array['text']);

echo '<input type="hidden" name="formkomplettdargestellt" value="1" />';

$libForm->printSubmitButton('Speichern');

echo '</fieldset>';
echo '</form>';


if((isset($_REQUEST['id']) && $_REQUEST['id'] != '') || $array['id'] != ''){
	if(isset($_REQUEST['id']) && $_REQUEST['id'] != ''){
		$array['id'] = $_REQUEST['id'];
	}

	$posssibleImage = $libModuleHandler->getModuleDirectory(). 'custom/bilder/' .$array['id']. '.jpg';

	if(is_file($posssibleImage)){
		echo '<img src="' .$posssibleImage. '" style="margin:5px;';
		list($width, $height, $type, $attr) = getimagesize($posssibleImage);

		if(($width / 4 * 3) >= $height){
			echo 'width:400px;';
		} else {
			echo 'height:300px;';
		}

		echo '" alt="" /><br />';
	}

	echo '<form method="post" enctype="multipart/form-data" action="index.php?pid=intranet_internethome_nachricht_adminankuendigung&amp;id='. $array['id'] .'">';
	echo '<input type="hidden" name="formtyp" value="bildupload" />';
	echo '<input name="bilddatei" type="file" size="10" />';
	echo '<input type="submit" value="Bild hochladen" /><br />';
	echo '</form>';

	if(is_file($posssibleImage)){
		echo '<a href="index.php?pid=intranet_internethome_nachricht_adminankuendigung&amp;aktion=bilddelete&amp;id=' .$array['id']. '" onclick="return confirm(\'Willst Du das Bild wirklich löschen?\')">Bild löschen</a>';
	}
}
?>