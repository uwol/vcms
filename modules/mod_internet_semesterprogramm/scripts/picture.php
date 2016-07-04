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

if(!is_object($libGlobal))
	exit();


if(isset($_GET['eventid']) && is_numeric($_GET['eventid']) &&
		isset($_GET['pictureid']) && is_numeric($_GET['pictureid']) &&
		preg_match("/^[0-9]+$/", $_GET['eventid']) && preg_match("/^[0-9]+$/", $_GET['pictureid'])){

	if($libAuth->isLoggedin()){
		$level = 2;
	} else {
		$level = 0;
	}

	if($libGallery->hasPictures($_GET['eventid'], $level)){
		$pictures = $libGallery->getPictures($_GET['eventid'], $level);

		if(isset($pictures[$_GET['pictureid']]) && $pictures[$_GET['pictureid']] != ''){
			$path = 'custom/veranstaltungsfotos/' .$_GET['eventid']. '/' .$pictures[$_GET['pictureid']];

			if(!is_file($path)){
				exit();
			}

			// send headers
			header("Content-type: image/jpeg\n");
			header("Content-transfer-encoding: binary\n");
			header("Content-length: " .filesize($path). "\n");

			// send content
			$fp=fopen($path, 'r');
			fpassthru($fp);
			fclose($fp);
		}
	}
}
?>