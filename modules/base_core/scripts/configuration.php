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

echo '<form action="index.php?pid=configuration" method="POST">'."\r\n";
echo '<table>'."\r\n";

//modules
foreach($storage as $moduleid => $arrays){
	echo '<tr><td colspan="4"><h2>' .$moduleid. '</h2></td></tr>'."\r\n";
	//arrays
	foreach($arrays as $array_name => $positionen){
		echo '<tr><td style="vertical-align: top">' .$array_name. '</td>'."\r\n";

		//positions and values at that positions
		foreach($positionen as $position => $value){
			echo '<td><input type="text" size="2" name="' . $moduleid .'#'. $array_name .'#position' . '" value="' .$position. '" disabled="disabled" /></td>'."\r\n";
			echo '<td><input type="text" size="40" name="'. $moduleid .'#'. $array_name .'#'. $position .'#value" value="' .$value. '" /></td>'."\r\n";
			echo '<td><a href="index.php?pid=configuration&amp;action=delete&amp;moduleid=' .$moduleid. '&amp;array_name=' .$array_name. '&amp;position=' .$position. '"><img src="styles/icons/basic/garbage.svg" alt="garbage" class="icon_small" /></a></td>'."\r\n";
			echo '</tr><tr><td></td>'."\r\n";
		}

		echo '</tr>'."\r\n";
	}
}

echo '</table>'."\r\n";
echo '<input type="hidden" name="action" value="save" />'."\r\n";
echo '<input type="hidden" name="formkomplettdargestellt" value="1" />'."\r\n";
echo '<input type="submit" value="Speichern" />'."\r\n";
echo '</form>';
?>