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


if(isset($_REQUEST['modul']) && !preg_match("/^[a-zA-Z0-9_]+$/", $_REQUEST['modul']))
	die();


$repoHostname = 'repository.' . $libGlobal->vcmsHostname;
$gitHubRepoUrl = 'https://github.com/uwol/vcms/tree/master';

$modulesRelativeDirectoryPath = 'modules';
$engineUpdateScript = 'vendor/vcms/install/update.php';

$tempRelativeDirectoryPath = 'temp';
$tempAbsoluteDirectoryPath = $libFilesystem->getAbsolutePath($tempRelativeDirectoryPath);


echo '<h1>Module</h1>';
echo '<div class="alert alert-info" role="alert">';

/*
* actions
*/
$libFilesystem->deleteDirectory($tempRelativeDirectoryPath);
@mkdir($tempAbsoluteDirectoryPath);



if(isset($_REQUEST['modul']) && $_REQUEST['modul'] != '' && $_REQUEST['modul'] != 'engine'){
	$module = $_REQUEST['modul'];

	if($_REQUEST['aktion'] == 'installModule' && $module != ''){
		installModule($module);
	} elseif($_REQUEST['aktion'] == 'uninstallModule' && $module != ''){
		uninstallModule($module);
	}
}

if(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == 'updateEngine'){
	updateEngine();
}

$libCronJobs->executeJobs();


echo 'Lade Paketinformationen aus dem Repository.';
echo '</div>';


$manifestUrl = 'http://' .$repoHostname. '/manifest.json?id=' .$libGlobal->getSiteUrlAuthority(). '&version=' .$libGlobal->version;
$modules = getModules($manifestUrl);


/*
* output
*/
echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<p>Das VCMS besteht aus einer Engine und mehreren Modulen, die auf dieser Seite aktualisiert werden können. Die folgende Liste zeigt die im System installierten sowie die im Repository verfügbaren Versionen.</p>';
echo '<table class="table table-condensed table-striped table-hover">';

echo '<thead>';
echo '<tr>';
echo '<th>Modulname</th><th>Status</th>';
echo '<th>Version<br />(installiert)</th>';
echo '<th>Version<br />(Repository)</th>';
echo '<th class="toolColumn"></th>';
echo '<th class="toolColumn"></th>';
echo '<th class="toolColumn"></th>';
echo '</tr>';
echo '</thead>';


$actualEngineVersion = (double) $libGlobal->version;
$newEngineVersion = (double) $modules['engine'];

$engineIsOld = false;

if($newEngineVersion > $actualEngineVersion){
	$engineIsOld = true;
}

foreach($modules as $key => $value){
	//module id
	echo '<tr>';
	echo '<td>';

	if($value){
		if($key != 'engine'){
			$url = $gitHubRepoUrl. '/modules/' .$key;
		} else {
			$url = $gitHubRepoUrl;
		}

		echo '<a href="' .$url. '">';
	}

	echo $key;

	if($value){
		echo '</a>';
	}

	echo '</td>';

	//status
	echo '<td>';

	if($key != 'engine'){
		if($libModuleHandler->moduleIsAvailable($key)){
			echo 'installiert';
		} else {
			echo 'nicht installiert';
		}
	} else {
		echo 'installiert';
	}

	echo '</td>';

	//version of installed module
	echo '<td>';

	if($key != 'engine'){
		if($libModuleHandler->moduleIsAvailable($key)){
			$module = $libModuleHandler->getModuleByModuleid($key);
			echo $module->getVersion();
		}
	} else {
		echo $libGlobal->version;
	}

	echo '</td>';

	//version of module in repo
	echo '<td>';

	if($value != ''){
		echo $value;
	} else {
		echo 'nicht im Repository';
	}

	echo '</td>';


	// install action
	echo '<td class="toolColumn">';

	if($key != 'engine'){
		if(!$engineIsOld && !$libModuleHandler->moduleIsAvailable($key)){
			echo '<a href="index.php?pid=updater_liste&amp;modul=' .$key. '&amp;aktion=installModule" onclick="return confirm(\'Willst Du das Modul wirklich installieren?\')">';
			echo '<i class="fa fa-plus-circle" aria-hidden="true"></i>';
			echo '</a>';
		}
	}

	echo '</td>';


	// update action
	echo '<td class="toolColumn">';

	if($engineIsOld && $key == 'engine'){
		echo '<a href="index.php?pid=updater_liste&amp;aktion=updateEngine" onclick="return confirm(\'Willst Du die Engine wirklich aktualisieren?\')">';
		echo '<i class="fa fa-cloud-download" aria-hidden="true"></i>';
		echo '</a>';
	} else {
		if($libModuleHandler->moduleIsAvailable($key)){
			$module = $libModuleHandler->getModuleByModuleid($key);
			$actualversion = (double) $module->getVersion();
			$newversion = (double) $value;

			if(!$engineIsOld && $newversion > $actualversion){
				echo '<a href="index.php?pid=updater_liste&amp;modul=' .$key. '&amp;aktion=installModule" onclick="return confirm(\'Willst Du das Modul wirklich aktualisieren?\')">';
				echo '<i class="fa fa-cloud-download" aria-hidden="true"></i>';
				echo '</a>';
			}
		}
	}

	echo '</td>';


	// delete action
	echo '<td class="toolColumn">';

	if($key != 'engine'){
		if(!$engineIsOld && $libModuleHandler->moduleIsAvailable($key)){
			$module = $libModuleHandler->getModuleByModuleid($key);
			$actualversion = (double) $module->getVersion();
			$newversion = (double) $value;

			echo '<a href="index.php?pid=updater_liste&amp;modul=' .$key. '&amp;aktion=uninstallModule" onclick="return confirm(\'Willst Du das Modul wirklich deinstallieren?\')">';
			echo '<i class="fa fa-trash" aria-hidden="true"></i>';
			echo '</a>';
		}
	}

	echo '</td>';
	echo '</tr>';
}

echo '</table>';



function getModules($manifestUrl){
	global $libHttp, $libModuleHandler;

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

function installModule($module){
	global $libHttp, $libModuleHandler, $libFilesystem, $repoHostname,
		$tempRelativeDirectoryPath, $modulesRelativeDirectoryPath;

	// globals required for install/update scripts
	global $libGlobal, $libDb;

	$tarRelativeFilePath = $tempRelativeDirectoryPath. '/' .$module. '.tar';
	$tarAbsoluteFilePath = $libFilesystem->getAbsolutePath($tarRelativeFilePath);

	$tempModuleRelativeDirectoryPath = $tempRelativeDirectoryPath. '/' .$module;
	$tempModuleAbsoluteDirectoryPath = $libFilesystem->getAbsolutePath($tempModuleRelativeDirectoryPath);

	$moduleRelativeDirectoryPath = $modulesRelativeDirectoryPath. '/' .$module;
	$moduleAbsoluteDirectoryPath = $libFilesystem->getAbsolutePath($moduleRelativeDirectoryPath);

	$isUpdate = is_dir($moduleAbsoluteDirectoryPath);

	echo '<p>Lade Modulpaket aus dem Repository.</p>';
	$libHttp->get('http://' .$repoHostname. '/packages/'. $module. '.tar', $tarAbsoluteFilePath);

	//untar module package
	$tar = new \pear\Archive\Archive_Tar($tarAbsoluteFilePath);
	echo '<p>Entpacke das Paket in den temp-Ordner.</p>';
	$tar->extract($tempRelativeDirectoryPath. '/');

	if(is_dir($tempModuleAbsoluteDirectoryPath)){
		if(is_file($tempModuleAbsoluteDirectoryPath. '/meta.json')){
			if(!$isUpdate){
				$libFilesystem->copyDirectory($tempModuleRelativeDirectoryPath, $moduleRelativeDirectoryPath);
			} else {
				$files = array_diff(scandir($moduleAbsoluteDirectoryPath), array('..', '.', 'custom'));

				foreach ($files as $file){
					$fileRelativePath = $moduleRelativeDirectoryPath. '/' .$file;
					$fileAbsolutePath = $libFilesystem->getAbsolutePath($fileRelativePath);

					if(is_dir($fileAbsolutePath)){
						echo 'Lösche ' .$fileRelativePath. '<br />';
						$libFilesystem->deleteDirectory($fileRelativePath);
					} elseif(is_file($fileAbsolutePath)){
						echo 'Lösche ' .$fileRelativePath. '<br />';
						unlink($fileAbsolutePath);
					}
				}

				echo '<p>Kopiere aktualisiertes Modul in den Modulordner ' .$moduleRelativeDirectoryPath. '</p>';

				$files = array_diff(scandir($tempModuleAbsoluteDirectoryPath), array('..', '.', 'custom'));

				foreach ($files as $file){
					$fileRelativePath = $tempModuleRelativeDirectoryPath. '/' .$file;
					$fileAbsolutePath = $libFilesystem->getAbsolutePath($fileRelativePath);

					if(is_dir($fileAbsolutePath)){
						$libFilesystem->copyDirectory($fileRelativePath, $moduleRelativeDirectoryPath. '/' .$file);
					} elseif(is_file($fileAbsolutePath)){
						copy($fileRelativePath, $moduleRelativeDirectoryPath. '/' .$file);
					}
				}
			}

			refreshModuleHandler();

			$moduleObject = $libModuleHandler->getModuleByModuleid($module);

			if(!$isUpdate && $moduleObject->getInstallScript() != ''){
				echo '<p>Führe Installationsskript des Moduls aus.</p>';
				$scriptAbsolutePath = $libFilesystem->getAbsolutePath($moduleObject->getPath(). '/' .$moduleObject->getInstallScript());
				include($scriptAbsolutePath);
			} elseif($isUpdate && $moduleObject->getUpdateScript() != ''){
				echo '<p>Führe Aktualisierungsscript des Moduls aus.</p>';
				$scriptAbsolutePath = $libFilesystem->getAbsolutePath($moduleObject->getPath(). '/' .$moduleObject->getUpdateScript());
				include($scriptAbsolutePath);
			}
		} else {
			echo '<p>Fehler: Das heruntergeladene Modulpaket enthält keine meta.json</p>';
		}
	} else {
		echo '<p>Fehler: Das heruntergeladene Modulpaket konnte nicht entpackt werden.</p>';
	}

	//delete temporary module folder
	echo '<p>Lösche das temporäre Modulpaket ' .$tarRelativeFilePath. '.</p>';
	@unlink($tarAbsoluteFilePath);

	echo '<p>Lösche den temporären Modulordner ' .$tempModuleRelativeDirectoryPath. '.</p>';

	if(is_dir($tempModuleAbsoluteDirectoryPath)){
		$libFilesystem->deleteDirectory($tempModuleRelativeDirectoryPath);
	}
}

function uninstallModule($module){
	global $libModuleHandler, $libFilesystem, $modulesRelativeDirectoryPath;

	// globals required for install/update scripts
	global $libGlobal, $libDb;

	$moduleRelativeDirectoryPath = $modulesRelativeDirectoryPath. '/' .$module;
	$moduleObject = $libModuleHandler->getModuleByModuleid($module);

	if(is_object($moduleObject) && $moduleObject->getUninstallScript() != ''){
		echo '<p>Führe Deinstallationsskript des Moduls aus.</p>';
		$scriptAbsolutePath = $libFilesystem->getAbsolutePath($moduleObject->getPath(). '/'. $moduleObject->getUninstallScript());
		include($scriptAbsolutePath);
	}

	//delete module directory
	echo '<p>Lösche den Modulordner ' .$moduleRelativeDirectoryPath. '.</p>';
	$libFilesystem->deleteDirectory($moduleRelativeDirectoryPath);

	refreshModuleHandler();
}

function updateEngine(){
	global $libHttp, $libFilesystem, $libCronJobs, $repoHostname, $engineUpdateScript, $tempRelativeDirectoryPath;

	// globals required for install/update scripts
	global $libGlobal, $libDb;

	$tarRelativeFilePath = $tempRelativeDirectoryPath. '/engine.tar';
	$tarAbsoluteFilePath = $libFilesystem->getAbsolutePath($tarRelativeFilePath);

	$tempEngineRelativeDirectoryPath = $tempRelativeDirectoryPath. '/engine';
	$tempEngineAbsoluteDirectoryPath = $libFilesystem->getAbsolutePath($tempEngineRelativeDirectoryPath);

	//download engine package
	echo '<p>Lade Enginepaket aus dem Repository.</p>';
	$libHttp->get('http://' .$repoHostname. '/packages/engine.tar', $tarAbsoluteFilePath);

	//untar engine package
	$tar = new \pear\Archive\Archive_Tar($tarRelativeFilePath);
	echo '<p>Entpacke Enginepaket in den temp-Ordner.</p>';
	$tar->extract($tempRelativeDirectoryPath. '/');

	if(!is_dir($tempEngineAbsoluteDirectoryPath)){
		echo '<p>Fehler: Das heruntergeladene Enginepaket konnte nicht entpackt werden.</p>';
	} elseif(!is_file($tempEngineAbsoluteDirectoryPath. '/index.php')
			|| !is_file($tempEngineAbsoluteDirectoryPath. '/inc.php')
			|| !is_dir($tempEngineAbsoluteDirectoryPath. '/vendor')) {
		echo '<p>Fehler: Das Enginepaket ist fehlerhaft.</p>';
	} else {
		$libCronJobs->deleteFiles();

		unlink($libFilesystem->getAbsolutePath('inc.php'));
		unlink($libFilesystem->getAbsolutePath('index.php'));

		$libFilesystem->deleteDirectory('vendor');

		echo '<p>Installiere neue Engine.</p>';
		$libFilesystem->copyDirectory($tempEngineRelativeDirectoryPath, '.');

		echo '<p>Führe Aktualisierungsscript der Engine aus.</p>';
		$scriptAbsolutePath = $libFilesystem->getAbsolutePath($engineUpdateScript);
		include($scriptAbsolutePath);
	}

	die('</div><a href="index.php?pid=updater_liste">Klicke hier</a>, um die Modulliste anzuzeigen.');
}

function refreshModuleHandler(){
	global $libModuleHandler;

	$libModuleHandler = new \vcms\LibModuleHandler();
	$libModuleHandler->initModules();
}
?>