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


if(!$libGenericStorage->attributeExistsInCurrentModule('checkFilePermissions')){
	$libGenericStorage->saveValueInCurrentModule('checkFilePermissions', 0);
}


if(in_array('internetwart', $libAuth->getAemter())){
	/*
	* output
	*/
	$oks = array();
	$errors = array();

	$securedFolders = array();
	$unsecuredFolders = array();

	$notReadableFiles = array();

	/*
	* php_version
	*/
	if(version_compare(PHP_VERSION, '5.5') < 0){
		$errors[] = 'Die PHP-Version ist ' .PHP_VERSION. '.';
	} else {
		$oks[] = 'PHP-Version=' .PHP_VERSION. '.';
	}

	/*
	* missing folders
	*/
	$directoriesToCreate = $libCronjobs->getDirectoriesToCreate();

	foreach($directoriesToCreate as $directoryRelativePath){
		$directoryAbsolutePath = $libFilesystem->getAbsolutePath($directoryRelativePath);

		if(!is_dir($directoryAbsolutePath)){
			$errors[] = 'Ordner ' .$directoryRelativePath. ' fehlt.';
		} else {
			$oks[] = 'Ordner ' .$directoryRelativePath. ' vorhanden.';
		}
	}

	/*
	* missing htaccess deny files
	*/
	$directoriesWithHtaccessFile = $libCronjobs->getDirectoriesWithHtaccessFile();

	foreach($directoriesWithHtaccessFile as $directoryRelativePath){
		$directoryAbsolutePath = $libFilesystem->getAbsolutePath($directoryRelativePath);

		if(is_dir($directoryAbsolutePath)){
			if($libCronjobs->hasHtaccessDenyFile($directoryAbsolutePath)){
				$securedFolders[] = $directoryRelativePath;
			} else {
				$unsecuredFolders[] = $directoryRelativePath;
			}
		}
	}

	/*
	* nonreadable files
	*/
	if(function_exists('posix_access')){
		if($libGenericStorage->loadValueInCurrentModule('checkFilePermissions') == 1){
			$notReadableFiles = searchNotReadAbleFiles('.');
		}
	}

	//----------------------------------------------------------------------------------------------------------------

	/*
	* output
	*/
	if(count($errors) > 0 || count($unsecuredFolders) > 0 || count($notReadableFiles) > 0){
		foreach($errors as $error){
			$libGlobal->errorTexts[] = $error;
		}

		if(is_array($unsecuredFolders) && count($unsecuredFolders) > 0){
			$unsecuredFoldersText = 'Folgende Ordner sind nicht durch eine htaccess-Datei geschützt: ';
			$unsecuredFoldersText .= $libString->protectXSS(implode(', ', $unsecuredFolders));
			$libGlobal->errorTexts[] = $unsecuredFoldersText;
		}

		if(is_array($notReadableFiles) && count($notReadableFiles) > 0){
			$notReadableFilesText = 'PHP besitzt für die folgenden Dateien bzw. Ordner keine Zugriffsrechte: ';
			$notReadableFilesText .= $libString->protectXSS(implode(', ', $notReadableFiles));
			$libGlobal->errorTexts[] = $notReadableFilesText;
		}
	}
}

function searchNotReadAbleFiles($dir){
	$notReadableFiles = array();

	$files = array_diff(scandir($dir), array('..', '.'));
	$folders = array();

	foreach ($files as $file){
		if(is_dir($dir. '/' .$file)){
			$folders[] = $file;

			if(!@posix_access($dir . '/' . $file, POSIX_R_OK | POSIX_W_OK)){
				$notReadableFiles[] = $dir. '/' .$file;
			}

			if(@is_dir($dir. '/' .$file) && $dir. '/' .$file != 'custom/veranstaltungsfotos'){
				$notReadableFiles = array_merge($notReadableFiles, searchNotReadAbleFiles($dir . '/' . $file));
			}
		}
	}

	return $notReadableFiles;
}
