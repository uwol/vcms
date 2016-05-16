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


if($libAuth->isLoggedin() && isset($_GET['id']) && is_numeric($_GET['id']) &&
		preg_match("/^[0-9]+$/", $_GET['id'])){

	$path = "custom/intranet/mitgliederfotos/". $_GET['id'] .".jpg";

	if(!is_file($path)){
		$path = "custom/intranet/mitgliederfotos/blank.jpg";
	}

	// send headers
	header("Content-type: image/jpeg\n");
	header("Content-transfer-encoding: binary\n");
	header("Content-length: " . filesize($path) . "\n");

	if($_GET['id'] != $libAuth->getId()){
		$expires = 60*60*24;
		header("Pragma: public");
		header("Cache-Control: maxage=".$expires);
		header("Cache-Control: private");
		header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
	}

	$fp=fopen($path, "r");
	fpassthru($fp);
}
?>