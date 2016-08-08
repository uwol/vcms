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


if($libAuth->isLoggedin() && isset($_GET['id']) && is_numeric($_GET['id']) &&
		preg_match('/^[0-9]+$/', $_GET['id'])){

	$path = $libPerson->getMitgliedImageFilePath($id);

	if(is_file($path)){
		// send headers
		header("Content-type: image/jpeg\n");
		header("Content-transfer-encoding: binary\n");
		header("Content-length: " . filesize($path) . "\n");

		if($_GET['id'] != $libAuth->getId()){
			$expires = 60*60*24;
			header('Pragma: public');
			header('Cache-Control: maxage=' .$expires);
			header('Cache-Control: private');
			header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
		}

		$fp = fopen($path, 'r');
		fpassthru($fp);
	}
}
?>