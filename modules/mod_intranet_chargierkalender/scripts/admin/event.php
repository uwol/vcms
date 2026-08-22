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
$fields = array("datum", "beschreibung", "verein");

$id = '';

if(isset($_REQUEST['id'])){
	$id = $_REQUEST['id'];
}

//new event, empty row
if(isset($_REQUEST['action']) && $_REQUEST['action'] == "blank"){
	foreach($fields as $field){
		$array[$field] = '';
	}

	$array['id'] = '';
	$array['datum'] = @date("Y-m-d H:i:s");
}
//blank data to save
elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "insert"){
	if(!isset($_POST['form_complete']) || !$_POST['form_complete']){
		die("Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.");
	}

	$valueArray = $_REQUEST;
	$valueArray['datum'] = $libTime->assureMysqlDateTime($valueArray['datum']);

	$array = $libDb->insertRow($fields, $valueArray, "mod_chargierkalender_veranstaltung", array("id"=>''));
	$libGlobal->notificationTexts[] = 'Die Chargierveranstaltung wurde gespeichert.';
}
//data modification
elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "update"){
	if(!isset($_POST['form_complete']) || !$_POST['form_complete']){
		die("Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.");
	}

	$valueArray = $_REQUEST;
	$valueArray['datum'] = $libTime->assureMysqlDateTime($valueArray['datum']);

	$array = $libDb->updateRow($fields, $valueArray, "mod_chargierkalender_veranstaltung", array("id" => $id));
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
	echo '<form class="mb-4" method="post" action="index.php?pid=intranet_chargierkalender_adminliste" onsubmit="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">';
	echo '<input type="hidden" name="action" value="delete" />';
	echo '<input type="hidden" name="id" value="' .$array['id']. '" />';
	echo '<button type="submit" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer"><i class="fa fa-trash" aria-hidden="true"></i> Datensatz löschen</button>';
	echo '</form>';
}

/*
* form
*/
if(isset($_REQUEST['action']) && $_REQUEST['action'] == "blank"){
	$extraActionParam = "&amp;action=insert";
} else {
	$extraActionParam = "&amp;action=update";
}

echo '<div class="panel panel-default">';
echo '<div class="panel-body">';
echo '<form action="index.php?pid=intranet_chargierkalender_adminveranstaltung' .$extraActionParam. '" method="post" class="form-horizontal">';
echo '<fieldset>';

echo '<input type="hidden" name="formType" value="eventData" />';
echo '<input type="hidden" name="id" value="' .$array['id']. '" />';

$libForm->printTextInput('id', 'Id', $array['id'], 'text', true);
$libForm->printDateTimeInput('datum', 'Datum und Uhrzeit (00:00 = ganztägig)', $array['datum']);
$libForm->printAssociationsDropDownBox("verein", "Verein", $array['verein'], true, false);
$libForm->printTextInput('beschreibung', 'Beschreibung', $array['beschreibung']);

echo '<input type="hidden" name="form_complete" value="1" />';

$libForm->printSubmitButton('Speichern');

echo '</fieldset>';
echo '</form>';
echo '</div>';
echo '</div>';
