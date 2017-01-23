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
} elseif(!$libAuth->isLoggedin()){
	http_response_code(403);
} elseif(!isset($_GET['hash']) || $_GET['hash'] == '') {
	http_response_code(404);
} else {
	$rootFolderPathString = 'custom/intranet/downloads';
	$rootFolderAbsolutePathString = $libFilesystem->getAbsolutePath($rootFolderPathString);

	$rootFolderObject = new \vcms\filesystem\Folder('', '/', $rootFolderAbsolutePathString);
	$hashes = $rootFolderObject->getHashMap();
	$file = $hashes[$_GET['hash']];

	if(!is_object($file)){
		http_response_code(404);
	} else {
		$outputFileName = $file->name;
		$outputFilePathString = $file->getFileSystemPath();

		if(!in_array($libAuth->getGruppe(), $file->readGroups)){
			http_response_code(403);
		} else {
			$libMime = new \vcms\LibMime();
			$mime = $libMime->detectMime($outputFileName);

			if(!is_file($outputFilePathString)){
				http_response_code(404);
			} else {
				header('Pragma: no-cache');
				header('Cache-Control: no-cache, no-store, must-revalidate');
				header('Expires: 0');

				header('Content-Type: ' .$mime);
				header('Content-Disposition: attachment; filename="' .$outputFileName. '"');
				header('Content-Length: ' .$file->size);

				readfile($outputFilePathString);
			}
		}
	}
}
