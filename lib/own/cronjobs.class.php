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

class LibCronjobs{

	var $libDb;

	var $filesToDelete = array('installer.php', 'installer2.php', 'installer3.php',
		'installer.txt', 'Installationsanleitung.html', 'INSTALLATIONSANLEITUNG.txt',
		'LICENSE', 'LICENSE.txt', 'README.md', '.gitignore');

	function __construct(LibDb $libDb){
		$this->libDb = $libDb;

		$stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM sys_log_intranet WHERE aktion = 10 AND DATEDIFF(NOW(), datum) < 1');
		$stmt->execute();
		$stmt->bindColumn('number', $numberOfCronJobExecutionsToday);
		$stmt->fetch();

		if($numberOfCronJobExecutionsToday == 0){
			$this->executeJobs();
		}
	}

	function executeJobs(){
		global $libGenericStorage;

		$this->deleteFiles();
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

		$this->libDb->query("INSERT INTO sys_log_intranet (aktion, datum) VALUES (10, NOW())");
	}

	function deleteFiles(){
		foreach($this->filesToDelete as $fileToDelete){
			if(is_file($fileToDelete)){
				unlink($fileToDelete);
			}
		}
	}

	// @Deprecated
	function deleteInstaller(){
		$this->deleteFiles();
	}

	function createMissingDirectories(){
		/*
		* checks for missing folders
		*/

		if(!is_dir('modules')){
			die('Fehler: Fehlender Ordner modules');
		}

		if(!is_dir('custom')){
			die('Fehler: Fehlender Ordner custom');
		}

		if(!is_dir('temp')){
			@mkdir('temp');
		}

		if(!is_dir('custom/styles')){
			@mkdir('custom/styles');
		}

		if(!is_dir('custom/intranet')){
			@mkdir('custom/intranet');
		}

		if(!is_dir('custom/intranet/downloads')){
			@mkdir('custom/intranet/downloads');
		}

		if(!is_dir('custom/intranet/mitgliederfotos')){
			@mkdir('custom/intranet/mitgliederfotos');
		}

		if(!is_dir('custom/semestercover')){
			@mkdir('custom/semestercover');
		}

		if(!is_dir('custom/veranstaltungsfotos')){
			@mkdir('custom/veranstaltungsfotos');
		}
	}

	function repairHtaccessFiles(){
		if(!$this->hasHtaccessDenyFile('lib')){
			$this->generateHtaccessDenyFile('lib');
		}

		if(!$this->hasHtaccessDenyFile('custom/intranet')){
			$this->generateHtaccessDenyFile('custom/intranet');
		}

		if(!$this->hasHtaccessDenyFile('custom/veranstaltungsfotos')){
			$this->generateHtaccessDenyFile('custom/veranstaltungsfotos');
		}

		if(!$this->hasHtaccessDenyFile('temp')){
			$this->generateHtaccessDenyFile('temp');
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

	function cleanSysLogIntranet(){
		$this->libDb->query("DELETE FROM sys_log_intranet WHERE DATEDIFF(NOW(), datum) > 90");
	}

	function cleanBasePerson(){
		// clean table base_person
		$this->libDb->query("UPDATE base_person SET zusatz1=NULL, strasse1=NULL, ort1=NULL, plz1=NULL, land1=NULL, datum_adresse1_stand=NULL, zusatz2=NULL, strasse2=NULL, ort2=NULL, plz2=NULL, land2=NULL, datum_adresse2_stand=NULL, region1=NULL, region2=NULL, telefon1=NULL, telefon2=NULL, mobiltelefon=NULL, email=NULL, skype=NULL, jabber=NULL, webseite=NULL, datum_geburtstag=NULL, beruf=NULL, heirat_partner=NULL, heirat_datum=NULL, tod_datum=NULL, tod_ort=NULL, status=NULL, spitzname=NULL, vita=NULL, vita_letzterautor=NULL, bemerkung=NULL, username=NULL, password_hash=NULL, validationkey=NULL WHERE gruppe='X' AND (datum_gruppe_stand = '0000-00-00' OR datum_gruppe_stand IS NULL OR DATEDIFF(NOW(), datum_gruppe_stand) > 30)");
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

	function generateHtaccessAllowFile($directory){
		$content = 'allow from all';
		$this->generateHtaccessFile($directory, $content);
	}

	function generateHtaccessDenyFile($directory){
		$content = 'deny from all';
		$this->generateHtaccessFile($directory, $content);
    }

    function generateHtaccessFile($directory, $content){
    	$filename = $directory. '/.htaccess';
	    $handle = @fopen($filename, 'w');
    	@fwrite($handle, $content);
    	@fclose($handle);
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
}
?>