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

namespace vcms;

use PDO;

class LibCronJobs{

	var $filesToDelete = array('installer.php', 'installer2.php', 'installer3.php',
		'installer.txt', 'Installationsanleitung.html', 'INSTALLATIONSANLEITUNG.txt',
		'LICENSE', 'LICENSE.txt', 'README.md', '.gitignore');

	var $directoriesToDelete = array('design', 'js', 'lib', 
			'modules/base_core', 'modules/base_internet_login',
			'modules/base_internet_vereine', 'modules/base_intranet_administration_dbverwaltung',
			'modules/base_intranet_dbadmin', 'modules/base_intranet_home', 'modules/base_intranet_personen', 
			'modules/base_updatemanager', 'modules/mod_intranet_administration_export');

	var $relativeDirectoriesToCreate = array('temp', 'custom/styles', 'custom/intranet', 
		'custom/intranet/downloads', 'custom/intranet/mitgliederfotos', 
		'custom/semestercover', 'custom/veranstaltungsfotos');

	var $relativeDirectoriesWithHtaccessFile = array('vendor/vcms/lib', 'custom/intranet', 
		'custom/veranstaltungsfotos', 'temp');

	function __construct(){
		global $libDb;

		$stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM sys_log_intranet WHERE aktion = 10 AND DATEDIFF(NOW(), datum) < 1');
		$stmt->execute();
		$stmt->bindColumn('number', $numberOfCronJobExecutionsToday);
		$stmt->fetch();

		if($numberOfCronJobExecutionsToday == 0){
			$this->executeJobs();
		}
	}

	function executeJobs(){
		global $libGenericStorage, $libDb;

		$this->deleteFiles();
		$this->deleteDirectories();
		$this->createMissingDirectories();
		$this->repairHtaccessFiles();
		$this->cleanSysLogIntranet();
		$this->initConfiguration();

		if(!$libGenericStorage->attributeExists('base_core', 'cronjobsLeereAusgetretene')){
			$libGenericStorage->saveValue('base_core', 'cronjobsLeereAusgetretene', 0);
		}

		if($libGenericStorage->loadValue('base_core', 'cronjobsLeereAusgetretene') == 1){
			$this->cleanBasePerson();
		}

		$libDb->query("INSERT INTO sys_log_intranet (aktion, datum) VALUES (10, NOW())");
	}

	function deleteFiles(){
		global $libFilesystem;

		foreach($this->filesToDelete as $fileToDelete){
			$filePath = $libFilesystem->getAbsolutePath($fileToDelete);
		
			if(is_file($filePath)){
				unlink($filePath);
			}
		}
	}
	
	function deleteDirectories(){
		global $libFilesystem;

		foreach($this->directoriesToDelete as $directoryRelativePath){
			$directoryAbsolutePath = $libFilesystem->getAbsolutePath($directoryRelativePath);
		
			if(is_dir($directoryAbsolutePath)){
				$libFilesystem->deleteDirectory($directoryRelativePath);
			}
		}
	}

	// @Deprecated
	function deleteInstaller(){
		$this->deleteFiles();
	}

	function createMissingDirectories(){
		global $libFilesystem;

		foreach($this->relativeDirectoriesToCreate as $relativeDirectoryToCreate){
			$absoluteDirectoryPath = $libFilesystem->getAbsolutePath($relativeDirectoryToCreate);

			if(!is_dir($absoluteDirectoryPath)){
				@mkdir($absoluteDirectoryPath);
			}
		}
	}

	function repairHtaccessFiles(){	
		foreach($this->relativeDirectoriesWithHtaccessFile as $relativeDirectoryWithHtaccessFile){
			$this->repairHtaccessFile($relativeDirectoryWithHtaccessFile);
		}

		$files = array_diff(scandir('modules'), array('..', '.'));

		foreach ($files as $file){
			if(is_dir('modules/' .$file)){
				$modulePath = 'modules/' .$file;

				if(is_dir($modulePath. '/scripts')){
					if(!$this->hasHtaccessDenyFile($modulePath. '/scripts')){
						$this->generateHtaccessDenyFile($modulePath. '/scripts');
					}
				}

				if(is_dir($modulePath. '/install')){
					if(!$this->hasHtaccessDenyFile($modulePath. '/install')){
						$this->generateHtaccessDenyFile($modulePath. '/install');
					}
				}
			}
		}
	}

	function repairHtaccessFile($directory){
		global $libFilesystem;

		$absoluteDirectoryPath = $libFilesystem->getAbsolutePath($directory);
	
		if(!$this->hasHtaccessDenyFile($absoluteDirectoryPath)){
			$this->generateHtaccessDenyFile($absoluteDirectoryPath);
		}
	}

	function cleanSysLogIntranet(){
		global $libDb;

		$libDb->query("DELETE FROM sys_log_intranet WHERE DATEDIFF(NOW(), datum) > 90");
	}

	function cleanBasePerson(){
		global $libDb;

		$libDb->query("UPDATE base_person SET zusatz1=NULL, strasse1=NULL, ort1=NULL, plz1=NULL, land1=NULL, datum_adresse1_stand=NULL, zusatz2=NULL, strasse2=NULL, ort2=NULL, plz2=NULL, land2=NULL, datum_adresse2_stand=NULL, region1=NULL, region2=NULL, telefon1=NULL, telefon2=NULL, mobiltelefon=NULL, email=NULL, skype=NULL, jabber=NULL, webseite=NULL, datum_geburtstag=NULL, beruf=NULL, heirat_partner=NULL, heirat_datum=NULL, tod_datum=NULL, tod_ort=NULL, status=NULL, spitzname=NULL, vita=NULL, vita_letzterautor=NULL, bemerkung=NULL, password_hash=NULL, validationkey=NULL WHERE gruppe='X' AND (datum_gruppe_stand = '0000-00-00' OR datum_gruppe_stand IS NULL OR DATEDIFF(NOW(), datum_gruppe_stand) > 30)");
	}

	function initConfiguration(){
		global $libGenericStorage;

		if(!$libGenericStorage->attributeExists('base_core', 'showTrauerflor')){
			$libGenericStorage->saveValue('base_core', 'showTrauerflor', 0);
		}

		if(!$libGenericStorage->attributeExists('base_core', 'smtpEnable')){
			$libGenericStorage->saveValue('base_core', 'smtpEnable', 0);
		}

		if(!$libGenericStorage->attributeExists('base_core', 'smtpHost')){
			$libGenericStorage->saveValue('base_core', 'smtpHost', '');
		}

		if(!$libGenericStorage->attributeExists('base_core', 'smtpUsername')){
			$libGenericStorage->saveValue('base_core', 'smtpUsername', '');
		}

		if(!$libGenericStorage->attributeExists('base_core', 'smtpPassword')){
			$libGenericStorage->saveValue('base_core', 'smtpPassword', '');
		}

		if(!$libGenericStorage->attributeExists('base_core', 'fbAppId')){
			$libGenericStorage->saveValue('base_core', 'fbAppId', '');
		}

		if(!$libGenericStorage->attributeExists('base_core', 'fbSecretKey')){
			$libGenericStorage->saveValue('base_core', 'fbSecretKey', '');
		}
	}

	//------------------------------------------------------

	function generateHtaccessAllowFile($directoryPath){
		$content = 'allow from all';
		$this->generateHtaccessFile($directoryPath, $content);
	}

	function generateHtaccessDenyFile($directoryPath){
		$content = 'deny from all';
		$this->generateHtaccessFile($directoryPath, $content);
    }

    function generateHtaccessFile($directoryPath, $content){
    	$filePath = $directoryPath. '/.htaccess';
	    $handle = @fopen($filePath, 'w');
    	@fwrite($handle, $content);
    	@fclose($handle);
    }

    function hasHtaccessDenyFile($directoryPath){
    	$filePath = $directoryPath. '/.htaccess';

    	if(!is_file($filePath)){
    		return false;
    	}

    	$handle = @fopen($filePath, 'r');
    	$content = @fread($handle, @filesize($filePath));
    	@fclose($handle);

    	if($content == 'deny from all'){
    		return true;
    	} else {
    		return false;
    	}
    }
}