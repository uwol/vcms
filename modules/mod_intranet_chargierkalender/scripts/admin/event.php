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


/*
* actions
*/

$libForm = new LibForm();
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
	echo '<p><a href="index.php?pid=intranet_chargierkalender_adminliste&amp;aktion=delete&amp;id='.$array['id'].'" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">Datensatz löschen</a></p>';
}

/*
* form
*/
if(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == "blank"){
	$extraActionParam = "&amp;aktion=insert";
} else {
	$extraActionParam = "&amp;aktion=update";
}

echo '<form action="index.php?pid=intranet_chargierkalender_adminveranstaltung' .$extraActionParam. '" method="post" class="form-horizontal">';
echo '<fieldset>';

echo '<input type="hidden" name="formtyp" value="veranstaltungsdaten" />';
echo '<input type="hidden" name="id" value="' .$array['id']. '" />';

echo '<div class="form-group">';
echo '<label for="id" class="col-sm-2 control-label">Id</label>';
echo '<div class="col-sm-10"><input type="text" id="id" name="id" value="' .$array['id']. '" class="form-control" disabled /></div>';
echo '</div>';

echo '<div class="form-group">';
echo '<label for="datum" class="col-sm-2 control-label">Datum</label>';
echo '<div class="col-sm-10"><input type="date" id="datum" name="datum" value="' .$array['datum']. '" class="form-control" /></div>';
echo '</div>';

echo '<div class="form-group">';
echo '<label for="verein" class="col-sm-2 control-label">Verein</label>';
echo '<div class="col-sm-10">';
echo $libForm->getVereineDropDownBox("verein", "Verein", $array['verein'], true, false);
echo '</div>';
echo '</div>';

echo '<div class="form-group">';
echo '<label for="beschreibung" class="col-sm-2 control-label">Beschreibung</label>';
echo '<div class="col-sm-10"><textarea id="beschreibung" name="beschreibung" rows="7" class="form-control">' .$array['beschreibung']. '</textarea></div>';
echo '</div>';

echo '<input type="hidden" name="formkomplettdargestellt" value="1" />';

echo '<div class="form-group">';
echo '<div class="col-sm-offset-2 col-sm-10">';
echo '<button type="submit" class="btn btn-default">Speichern</button>';
echo '</div>';
echo '</div>';

echo '</fieldset>';
echo "</form>";
?>