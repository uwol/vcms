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

	$path = $libPerson->getImageFilePath($_GET['id']);

	if(is_file($path)){
		// send headers
		header("Content-type: image/jpeg\n");
		header("Content-transfer-encoding: binary\n");
		header("Content-length: " .filesize($path). "\n");

		if(!isOwnImage()){
			// send caching headers
			header('Pragma: public');
			header('Cache-Control: max-age=600');
			header('Expires: ' .gmdate('D, d M Y H:i:s \G\M\T', time() + 600));
		}

		$fp = fopen($path, 'r');
		fpassthru($fp);
	}
}

function isOwnImage(){
	global $libAuth;

	return $_GET['id'] === $libAuth->getId();
}
