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


if(!$libGenericStorage->attributeExistsInCurrentModule('checkFilePermissions')){
	$libGenericStorage->saveValueInCurrentModule('checkFilePermissions', 0);
}


if(in_array('internetwart', $libAuth->getAemter())){
	/*
	* actions
	*/
	$libCronJobs->executeJobs();

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
	if(version_compare(PHP_VERSION, '5.4') < 0){
		$errors[] = 'Die PHP-Version ist ' .PHP_VERSION. '.';
	} else {
		$oks[] = 'PHP-Version=' .PHP_VERSION. '.';
	}

	/*
	* safe_mode
	*/
	if(ini_get('safe_mode')){ //ist safe_mode in der php.ini aktiviert?
		$errors[] = 'In der PHP-Version auf diesem Server ist safe_mode=On konfiguriert.';
	} else {
		$oks[] = 'safe_mode=Off ist konfiguriert.';
	}

	/*
	* system config
	*/
	if($libConfig->sitePath == ''){
		$errors[] = 'In der Systemkonfiguration ist kein sitepath konfiguriert.';
	} else {
		$oks[] = 'In der Systemkonfiguration ist ein sitepath konfiguriert.';
	}

	/*
	* HTTPS check
	*/
	if($libGenericStorage->loadValue('base_internet_login', 'useHttps') != '1'){
		$errors[] = 'HTTPS ist in der Konfiguration für das Intranet nicht aktiviert.';
	} else {
		$oks[] = 'HTTPS ist für das Intranet aktiviert.';
	}

	/*
	* missing folders
	*/
	$dirs = array('custom', 'custom/intranet', 'custom/styles', 'custom/intranet/downloads',
			'custom/intranet/mitgliederfotos', 'custom/semestercover', 'custom/veranstaltungsfotos',
			'temp');

	foreach($dirs as $dir){
		if(!is_dir($dir)){
			$errors[] = 'Ordner ' .$dir. ' fehlt.';
		} else {
			$oks[] = 'Ordner ' .$dir. ' vorhanden.';
		}
	}

	/*
	* missing htaccess deny files
	*/
	$htaccessDirs = array('lib', 'custom/intranet', 'custom/veranstaltungsfotos', 'temp');

	foreach($htaccessDirs as $dir){
		if(is_dir($dir)){
			if(hasHtaccessDenyFile($dir)){
				$securedFolders[] = $dir;
			} else {
				$unsecuredFolders[] = $dir;
			}
		}
	}


	$modulesDir = 'modules';

	$files = array_diff(scandir($modulesDir), array('..', '.'));
	$folders = array();

	foreach ($files as $file){
		//module folder
		if (is_dir($modulesDir. '/' .$file)){
			$modulePath = $modulesDir. '/' .$file;

			//deny access to folder by htaccess
			if(is_dir($modulePath. '/scripts')){
				if(hasHtaccessDenyFile($modulePath. '/scripts')){
					$securedFolders[] = $modulePath. '/scripts';
				} else {
					$unsecuredFolders[] = $modulePath. '/scripts';
				}
			}

			if(is_dir($modulePath. '/install')){
				if(hasHtaccessDenyFile($modulePath. '/install')){
					$securedFolders[] = $modulePath. '/install';
				} else {
					$unsecuredFolders[] = $modulePath. '/install';
				}
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

function hasHtaccessDenyFile($directory){
	$filename = $directory. '/.htaccess';

   	if(!is_file($filename)){
   		return false;
   	}

	$handle = @fopen($filename, 'r');
	$content = @fread($handle, @filesize($filename));
	@fclose($handle);

	if($content == 'deny from all'){
   		return true;
   	} else {
   		return false;
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
?>