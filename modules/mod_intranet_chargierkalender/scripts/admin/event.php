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


/*
* actions
*/

$array = array();
//table fields
$felder = array("datum", "beschreibung", "verein");

$id = '';

if(isset($_REQUEST['id'])){
	$id = $_REQUEST['id'];
}

//new event, empty row
if(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == "blank"){
	foreach($felder as $feld){
		$array[$feld] = '';
	}

	$array['id'] = '';
	$array['datum'] = @date("Y-m-d H:i:s");
}
//blank data to save
elseif(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == "insert"){
	if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
		die("Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.");
	}

	$array = $libDb->insertRow($felder,$_REQUEST, "mod_chargierkalender_veranstaltung", array("id"=>''));
	$libGlobal->notificationTexts[] = 'Die Chargierveranstaltung wurde gespeichert.';
}
//data modification
elseif(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == "update"){
	if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
		die("Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.");
	}

	$array = $libDb->updateRow($felder,$_REQUEST, "mod_chargierkalender_veranstaltung", array("id" => $id));
	$libGlobal->notificationTexts[] = 'Die Chargierveranstaltung wurde gespeichert.';
}
// select
else {
	$stmt = $libDb->prepare("SELECT * FROM mod_chargierkalender_veranstaltung WHERE id=:id");
	$stmt->bindValue(':id', $id, PDO::PARAM_INT);
	$stmt->execute();
	$array = $stmt->fetch(PDO::FETCH_ASSOC);
}




/*
* output
*/

echo '<h1>Chargierveranstaltung</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

/*
* delete option
*/
if($array['id'] != ''){
	echo '<p><a href="index.php?pid=intranet_chargierkalender_adminliste&amp;aktion=delete&amp;id='.$array['id'].'" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')"><i class="fa fa-trash" aria-hidden="true"></i> Datensatz löschen</a></p>';
}

/*
* form
*/
if(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == "blank"){
	$extraActionParam = "&amp;aktion=insert";
} else {
	$extraActionParam = "&amp;aktion=update";
}

echo '<div class="panel panel-default">';
echo '<div class="panel-body">';
echo '<form action="index.php?pid=intranet_chargierkalender_adminveranstaltung' .$extraActionParam. '" method="post" class="form-horizontal">';
echo '<fieldset>';

echo '<input type="hidden" name="formtyp" value="veranstaltungsdaten" />';
echo '<input type="hidden" name="id" value="' .$array['id']. '" />';

$libForm->printTextInput('id', 'Id', $array['id'], 'text', true);
$libForm->printTextInput('datum', 'Datum', $array['datum'], 'date');
$libForm->printVereineDropDownBox("verein", "Verein", $array['verein'], true, false);
$libForm->printTextInput('beschreibung', 'Beschreibung', $array['beschreibung']);

echo '<input type="hidden" name="formkomplettdargestellt" value="1" />';

$libForm->printSubmitButton('Speichern');

echo '</fieldset>';
echo '</form>';
echo '</div>';
echo '</div>';
