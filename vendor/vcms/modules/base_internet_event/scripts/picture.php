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

if(!is_object($libGlobal) || !is_object($libAuth)){
	http_response_code(500);
} elseif(!isset($_GET['eventid']) || !is_numeric($_GET['eventid'])
		|| !isset($_GET['id']) || !is_numeric($_GET['id'])
		|| !preg_match("/^[0-9]+$/", $_GET['eventid']) || !preg_match("/^[0-9]+$/", $_GET['id'])){
	http_response_code(404);
} else {
	$level = 0;

	if($libAuth->isLoggedin()){
		$level = 2;
	}

	if(!$libGallery->hasPictures($_GET['eventid'], $level)){
		http_response_code(404);
	} else {
		$pictures = $libGallery->getPictures($_GET['eventid'], $level);

		if(!isset($pictures[$_GET['id']]) || $pictures[$_GET['id']] == ''){
			http_response_code(404);
		} else {
			$path = 'custom/veranstaltungsfotos/' .$_GET['eventid']. '/' .$pictures[$_GET['id']];

			if(!is_file($path)){
				http_response_code(404);
			} else {
				header('Content-type: image/jpeg');
				header('Content-transfer-encoding: binary');
				header('Content-length: ' .filesize($path));

				header('Pragma: private');
				header('Last-Modified: ' .gmdate('D, d M Y H:i:s T', filemtime($path)));

				if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && filemtime($path) < strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
					http_response_code(304);
				} else {
					readfile($path);
				}
			}
		}
	}
}
