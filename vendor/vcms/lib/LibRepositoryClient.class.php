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

namespace vcms;

class LibRepositoryClient{

  var $repoHostname;
  var $modulesRelativeDirectoryPath = 'modules';
  var $engineUpdateScript = 'vendor/vcms/install/update.php';
  var $tempRelativeDirectoryPath = 'temp';
  var $tempAbsoluteDirectoryPath;

  function __construct(){
    global $libGlobal, $libFilesystem;

		$this->repoHostname = 'repository.' . $libGlobal->vcmsHostname;
    $this->tempAbsoluteDirectoryPath = $libFilesystem->getAbsolutePath($this->tempRelativeDirectoryPath);
	}

  function getModuleVersions(){
  	global $libGlobal, $libHttp, $libModuleHandler;

    $manifestUrl = 'http://' .$this->repoHostname. '/manifest.json?id=' .$libGlobal->getSiteUrlAuthority(). '&version=' .$libGlobal->version;
  	$modules = $libHttp->get($manifestUrl);

  	if(!is_array($modules)){
  		$modules = json_decode($modules, true);
  	}

  	foreach($libModuleHandler->getModules() as $module){
  		$isBaseModule = substr($module->getId(), 0, 5) == 'base_';

  		if(!$isBaseModule){
  			if(!array_key_exists($module->getId(), $modules)){
  				$modules[$module->getId()] = '';
  			}
  		}
  	}

  	ksort($modules);

  	return $modules;
  }

  function getModuleStates(){
    global $libGlobal, $libModuleHandler;

    $moduleVersions = $this->getModuleVersions();
    $result = array();

    foreach($moduleVersions as $key => $newVersion){
      if($key == 'engine'){
        $result['engine'] = ((double) $newVersion) > ((double) $libGlobal->version);
      } elseif($libModuleHandler->moduleIsAvailable($key)){
        $module = $libModuleHandler->getModuleByModuleid($key);
        $result[$key] = ((double) $newVersion) > ((double) $module->getVersion());
      }
    }

    return $result;
  }

  function installModule($module){
  	global $libHttp, $libModuleHandler, $libFilesystem;

  	// globals required for install/update scripts
  	global $libGlobal, $libDb;

  	$tarRelativeFilePath = $this->tempRelativeDirectoryPath. '/' .$module. '.tar';
  	$tarAbsoluteFilePath = $libFilesystem->getAbsolutePath($tarRelativeFilePath);

  	$tempModuleRelativeDirectoryPath = $this->tempRelativeDirectoryPath. '/' .$module;
  	$tempModuleAbsoluteDirectoryPath = $libFilesystem->getAbsolutePath($tempModuleRelativeDirectoryPath);

  	$moduleRelativeDirectoryPath = $this->modulesRelativeDirectoryPath. '/' .$module;
  	$moduleAbsoluteDirectoryPath = $libFilesystem->getAbsolutePath($moduleRelativeDirectoryPath);

  	$isUpdate = is_dir($moduleAbsoluteDirectoryPath);

  	$libGlobal->notificationTexts[] = 'Lade Modulpaket aus dem Repository.';
  	$libHttp->get('http://' .$this->repoHostname. '/packages/'. $module. '.tar', $tarAbsoluteFilePath);

  	//untar module package
  	$tar = new \pear\Archive\Archive_Tar($tarAbsoluteFilePath);
  	$libGlobal->notificationTexts[] = 'Entpacke das Paket in den temp-Ordner.';
  	$tar->extract($this->tempRelativeDirectoryPath. '/');

  	if(is_dir($tempModuleAbsoluteDirectoryPath)){
  		if(is_file($tempModuleAbsoluteDirectoryPath. '/meta.json')){
  			if(!$isUpdate){
  				$libFilesystem->copyDirectory($tempModuleRelativeDirectoryPath, $moduleRelativeDirectoryPath);
  			} else {
  				$filesToDelete = array_diff(scandir($moduleAbsoluteDirectoryPath), array('.', '..', 'custom'));

  				foreach($filesToDelete as $file){
  					$fileRelativePath = $moduleRelativeDirectoryPath. '/' .$file;
  					$fileAbsolutePath = $libFilesystem->getAbsolutePath($fileRelativePath);

  					if(is_dir($fileAbsolutePath)){
  						$libGlobal->notificationTexts[] = 'Lösche ' .$fileRelativePath. '.';
  						$libFilesystem->deleteDirectory($fileRelativePath);
  					} elseif(is_file($fileAbsolutePath)){
  						$libGlobal->notificationTexts[] = 'Lösche ' .$fileRelativePath. '.';
  						unlink($fileAbsolutePath);
  					}
  				}

  				$libGlobal->notificationTexts[] = 'Kopiere aktualisiertes Modul in den Modulordner ' .$moduleRelativeDirectoryPath. '.';

  				$filesToCopy = array_diff(scandir($tempModuleAbsoluteDirectoryPath), array('.', '..'));

  				foreach($filesToCopy as $file){
  					$fileRelativePath = $tempModuleRelativeDirectoryPath. '/' .$file;
  					$fileAbsolutePath = $libFilesystem->getAbsolutePath($fileRelativePath);

  					if($file == 'custom'){
  						$libFilesystem->mergeDirectory($fileRelativePath, $moduleRelativeDirectoryPath. '/' .$file);
  					} elseif(is_dir($fileAbsolutePath)){
  						$libFilesystem->copyDirectory($fileRelativePath, $moduleRelativeDirectoryPath. '/' .$file);
  					} elseif(is_file($fileAbsolutePath)){
  						copy($fileRelativePath, $moduleRelativeDirectoryPath. '/' .$file);
  					}
  				}
  			}

  			$this->refreshModuleHandler();

  			$moduleObject = $libModuleHandler->getModuleByModuleid($module);

  			if(!$isUpdate && $moduleObject->getInstallScript() != ''){
  				$libGlobal->notificationTexts[] = 'Führe Installationsskript des Moduls aus.';
  				$scriptAbsolutePath = $libFilesystem->getAbsolutePath($moduleObject->getPath(). '/' .$moduleObject->getInstallScript());
  				include($scriptAbsolutePath);
  			} elseif($isUpdate && $moduleObject->getUpdateScript() != ''){
  				$libGlobal->notificationTexts[] = 'Führe Aktualisierungsskript des Moduls aus.';
  				$scriptAbsolutePath = $libFilesystem->getAbsolutePath($moduleObject->getPath(). '/' .$moduleObject->getUpdateScript());
  				include($scriptAbsolutePath);
  			}
  		} else {
  			$libGlobal->errorTexts[] = 'Das heruntergeladene Modulpaket enthält keine meta.json';
  		}
  	} else {
  		$libGlobal->errorTexts[] = 'Das heruntergeladene Modulpaket konnte nicht entpackt werden.';
  	}

  	//delete temporary module folder
  	$libGlobal->notificationTexts[] = 'Lösche temporäres Modulpaket ' .$tarRelativeFilePath. '.';
  	@unlink($tarAbsoluteFilePath);

  	$libGlobal->notificationTexts[] = 'Lösche temporären Modulordner ' .$tempModuleRelativeDirectoryPath. '.';

  	if(is_dir($tempModuleAbsoluteDirectoryPath)){
  		$libFilesystem->deleteDirectory($tempModuleRelativeDirectoryPath);
  	}
  }

  function uninstallModule($module){
  	global $libModuleHandler, $libFilesystem;

  	// globals required for install/update scripts
  	global $libGlobal, $libDb;

  	$moduleRelativeDirectoryPath = $this->modulesRelativeDirectoryPath. '/' .$module;
  	$moduleObject = $libModuleHandler->getModuleByModuleid($module);

  	if(is_object($moduleObject) && $moduleObject->getUninstallScript() != ''){
  		$libGlobal->notificationTexts[] = 'Führe Deinstallationsskript des Moduls aus.';
  		$scriptAbsolutePath = $libFilesystem->getAbsolutePath($moduleObject->getPath(). '/'. $moduleObject->getUninstallScript());
  		include($scriptAbsolutePath);
  	}

  	//delete module directory
  	$libGlobal->notificationTexts[] = 'Lösche den Modulordner ' .$moduleRelativeDirectoryPath. '.';
  	$libFilesystem->deleteDirectory($moduleRelativeDirectoryPath);

  	$this->refreshModuleHandler();
  }

  function updateEngine(){
  	global $libHttp, $libFilesystem, $libCronjobs;
  	// globals required for install/update scripts
  	global $libGlobal, $libDb, $libGenericStorage;

  	$tarRelativeFilePath = $this->tempRelativeDirectoryPath. '/engine.tar';
  	$tarAbsoluteFilePath = $libFilesystem->getAbsolutePath($tarRelativeFilePath);

  	$tempEngineRelativeDirectoryPath = $this->tempRelativeDirectoryPath. '/engine';
  	$tempEngineAbsoluteDirectoryPath = $libFilesystem->getAbsolutePath($tempEngineRelativeDirectoryPath);

  	$libGlobal->notificationTexts[] = 'Lade Enginepaket aus dem Repository.';
  	$libHttp->get('http://' .$this->repoHostname. '/packages/engine.tar', $tarAbsoluteFilePath);

  	$tar = new \pear\Archive\Archive_Tar($tarRelativeFilePath);
  	$libGlobal->notificationTexts[] = 'Entpacke Enginepaket in den temp-Ordner.';
  	$tar->extract($this->tempRelativeDirectoryPath. '/');

  	if(!is_dir($tempEngineAbsoluteDirectoryPath)){
  		$libGlobal->errorTexts[] = 'Das Enginepaket konnte nicht entpackt werden.';
  	} elseif(!is_file($tempEngineAbsoluteDirectoryPath. '/index.php')) {
  		$libGlobal->errorTexts[] = 'Das Enginepaket ist fehlerhaft.';
  	} else {
  		$libCronjobs->deleteFiles();

  		unlink($libFilesystem->getAbsolutePath('api.php'));
  		unlink($libFilesystem->getAbsolutePath('index.php'));

  		$libFilesystem->deleteDirectory('vendor');

  		$libGlobal->notificationTexts[] = 'Installiere neue Engine.';
  		$libFilesystem->copyDirectory($tempEngineRelativeDirectoryPath, '.');

  		$libGlobal->notificationTexts[] = 'Führe Aktualisierungsskript der Engine aus.';
  		$scriptAbsolutePath = $libFilesystem->getAbsolutePath($this->engineUpdateScript);
  		include($scriptAbsolutePath);
  	}
  }

  function refreshModuleHandler(){
  	global $libModuleHandler;

  	$libModuleHandler = new \vcms\LibModuleHandler();
  	$libModuleHandler->initModules();
  }

  function resetTempDirectory(){
    global $libFilesystem;

    $libFilesystem->deleteDirectory($this->tempRelativeDirectoryPath);
    @mkdir($this->tempAbsoluteDirectoryPath);
  }
}
