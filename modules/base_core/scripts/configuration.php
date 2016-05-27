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


if(isset($_POST['formkomplettdargestellt']) && $_POST['formkomplettdargestellt'] && isset($_POST['action']) && $_POST['action'] == "save"){
	foreach($_POST as $key => $value){
		if($key != 'formkomplettdargestellt'){
			$array = explode('#', $key);

			$moduleid = $array[0];

			$array_name = '';

			if(isset($array[1])){
				$array_name = $array[1];
			}

			$position = '';

			if(isset($array[2])){
				$position = $array[2];
			}

			if($moduleid != "" && $array_name != "" && $position != ""){
				$libGenericStorage->saveArrayValue($moduleid, $array_name, $position, $value);
			}
		}
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == "delete"){
	$moduleid = $_GET['moduleid'];
	$array_name = $_GET['array_name'];
	$position = $_GET['position'];

	if($moduleid != "" && $array_name != "" && $position != ""){
		$libGenericStorage->deleteArrayValue($moduleid, $array_name, $position);
		$libGlobal->notificationTexts[] = 'Der Wert wurde gelöscht.';
	}
}

echo '<h1>Konfiguration</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<p>Auf dieser Seite können die Module des Systems konfiguriert werden.</p>';

$storage = $libGenericStorage->listAllArrayValues();

echo '<form action="index.php?pid=configuration" method="post" class="form-horizontal">';
echo '<fieldset>';

//modules
foreach($storage as $moduleid => $arrays){
	echo '<h2>' .$moduleid. '</h2>';

	//arrays
	foreach($arrays as $array_name => $positionen){
		//positions and values at that positions
		foreach($positionen as $position => $value){
			echo '<div class="form-group">';
			echo '<label class="col-sm-3 control-label">' .$array_name. '</label>';

			echo '<div class="col-sm-1">';
			echo '<input type="text" name="' . $moduleid .'#'. $array_name .'#position' . '" value="' .$position. '" disabled="disabled" class="form-control input-sm" />';
			echo '</div>';

			echo '<div class="col-sm-7">';
			echo '<input type="text" name="'. $moduleid .'#'. $array_name .'#'. $position .'#value" value="' .$value. '" class="form-control input-sm" />';
			echo '</div>';

			echo '<div class="col-sm-1">';
			echo '<div class="form-control-static">';
			echo '<a href="index.php?pid=configuration&amp;action=delete&amp;moduleid=' .$moduleid. '&amp;array_name=' .$array_name. '&amp;position=' .$position. '"><img src="styles/icons/basic/delete.svg" alt="delete" class="icon_small" /></a>';
			echo '</div>';
			echo '</div>';

			echo '</div>';
		}
	}
}

echo '<input type="hidden" name="action" value="save" />';
echo '<input type="hidden" name="formkomplettdargestellt" value="1" />';

echo '<div class="form-group">';
echo '<div class="col-sm-offset-3 col-sm-10">';
echo '<button type="submit" class="btn btn-default">Speichern</button>';
echo '</div>';
echo '</div>';

echo '</fieldset>';
echo '</form>';
?>