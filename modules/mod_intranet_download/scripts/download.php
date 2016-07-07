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


if($libAuth->isLoggedin() && isset($_GET['hash']) && $_GET['hash'] != ''){
	require_once("lib/lib.php");

	$rootFolderPathString = 'custom/intranet/downloads';

	$rootFolderObject = new Folder('', '/', $rootFolderPathString);
	$hashes = $rootFolderObject->getHashMap();
	$file = $hashes[$_GET['hash']];

	if(is_object($file)){
		$outputFileName = $file->name;
		$outputFilePathString = $file->getFileSystemPath();

		if(in_array($libAuth->getGruppe(), $file->readGroups)){
			$libMime = new vcms\LibMime();
			$mime = $libMime->detectMime($outputFileName);

			/*
			* disable caching
			*/
			header('Cache-Control: no-cache, no-store, must-revalidate');
			header('Pragma: no-cache');
			header('Expires: 0');

			// mime
			header("Content-Type: " . $mime);
			header('Content-Disposition: attachment; filename="' . $outputFileName . '"');
			header("Content-Length: " . $file->size);

			if(is_file($outputFilePathString)){
				readfile($outputFilePathString);
			}
		}
	}
}
?>