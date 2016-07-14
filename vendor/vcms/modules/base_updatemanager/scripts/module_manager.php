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
* clean up
*/
$libFilesystem->deleteDirectory($tempRelativeDirectoryPath);
@mkdir($tempAbsoluteDirectoryPath);

/*
* actions
*/
if(isset($_REQUEST['modul']) && $_REQUEST['modul'] != '' && $_REQUEST['modul'] != 'engine'){
	$module = $_REQUEST['modul'];

	$tarRelativeFilePath = $tempRelativeDirectoryPath. '/' .$module. '.tar';
	$tarAbsoluteFilePath = $libFilesystem->getAbsolutePath($tarRelativeFilePath);

	$tempModuleRelativeDirectoryPath = $tempRelativeDirectoryPath. '/' .$module;
	$tempModuleAbsoluteDirectoryPath = $libFilesystem->getAbsolutePath($tempModuleRelativeDirectoryPath);

	$moduleRelativeDirectoryPath = $modulesRelativeDirectoryPath. '/' .$module;
	$moduleAbsoluteDirectoryPath = $libFilesystem->getAbsolutePath($moduleRelativeDirectoryPath);

	/*
	* install module
	*/
	if($_REQUEST['aktion'] == 'installModule' && $module != ''){
		//module not installed, yet?
		if(!$libModuleHandler->moduleIsAvailable($module)){
			//download module package
			echo '<p>Lade Modulpaket aus dem Repository.</p>';
			downloadContent('http://' .$repoHostname. '/packages/'. $module. '.tar', $tarAbsoluteFilePath);

			//untar module package
			$tar = new pear\Archive\Archive_Tar($tarAbsoluteFilePath);
			echo '<p>Entpacke das Paket in den temp-Ordner.</p>';
			$tar->extract($tempRelativeDirectoryPath. '/');

			if(is_dir($tempModuleAbsoluteDirectoryPath)){
				if(is_file($tempModuleAbsoluteDirectoryPath. '/meta.json')){
					if(!is_dir($moduleAbsoluteDirectoryPath)){
						//copy temporary module folder
						$libFilesystem->copyDirectory($tempModuleRelativeDirectoryPath, $moduleRelativeDirectoryPath);

						//refresh module handler
						$libModuleHandler = new vcms\LibModuleHandler();
						$libModuleHandler->initModules();

						//run installation script
						$moduleObject = $libModuleHandler->getModuleByModuleid($module);

						if($moduleObject->getInstallScript() != ''){
							echo '<p>Führe Installationsskript des Moduls aus.</p>';
							$scriptAbsolutePath = $libFilesystem->getAbsolutePath($moduleObject->getPath(). '/'. $moduleObject->getInstallScript());
							include($scriptAbsolutePath);
						}
					} else {
						echo '<p>Fehler: Das Modul ist bereits installiert.</p>';
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
		} else {
			echo '<p>Fehler: Das Modul ist bereits installiert.</p>';
		}
	}

	/*
	* uninstall module
	*/
	elseif($_REQUEST['aktion'] == 'uninstallModule' && $module != ''){
		//run uninstall script
		$moduleObject = $libModuleHandler->getModuleByModuleid($module);

		if(is_object($moduleObject) && $moduleObject->getUninstallScript() != ''){
			echo '<p>Führe Deinstallationsskript des Moduls aus.</p>';
			$scriptAbsolutePath = $libFilesystem->getAbsolutePath($moduleObject->getPath(). '/'. $moduleObject->getUninstallScript());
			include($scriptAbsolutePath);
		}

		//delete module directory
		echo '<p>Lösche den Modulordner ' .$moduleRelativeDirectoryPath. '.</p>';
		$libFilesystem->deleteDirectory($moduleRelativeDirectoryPath);

		//refresh module handler
		$libModuleHandler = new vcms\LibModuleHandler();
		$libModuleHandler->initModules();
	}

	/*
	* update module
	*/
	elseif($_REQUEST['aktion'] == 'updateModule' && $module != ''){
		//download module package
		echo '<p>Lade Modulpaket aus dem Repository.</p>';
		downloadContent('http://' .$repoHostname. '/packages/'. $module. '.tar',
			$tarAbsoluteFilePath);

		//untar module package
		$tar = new pear\Archive\Archive_Tar($tarRelativeFilePath);
		echo '<p>Entpacke das Paket in den temp-Ordner.</p>';
		$tar->extract($tempRelativeDirectoryPath. '/');

		if(is_dir($tempModuleAbsoluteDirectoryPath)){
			if(is_file($tempModuleAbsoluteDirectoryPath. '/meta.json')){
				if(is_dir($moduleAbsoluteDirectoryPath)){

					if(is_dir($tempModuleAbsoluteDirectoryPath. '/custom')){
						$libFilesystem->deleteDirectory($tempModuleAbsoluteDirectoryPath. '/custom');
					}

					//clean module folder except custom
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

					//copy all temporary files except the custom directory
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

					//refresh module handler
					$libModuleHandler = new vcms\LibModuleHandler();
					$libModuleHandler->initModules();

					//run update script
					$moduleObject = $libModuleHandler->getModuleByModuleid($module);

					if($moduleObject->getUpdateScript() != ''){
						echo '<p>Führe Aktualisierungsscript des Moduls aus.</p>';
						$scriptAbsolutePath = $libFilesystem->getAbsolutePath($moduleObject->getPath(). '/'. $moduleObject->getUpdateScript());
						include($scriptAbsolutePath);
					}
				} else {
					echo '<p>Fehler: Das zu aktualisierende Modul ist nicht installiert.</p>';
				}
			} else {
				echo '<p>Fehler: Das heruntergeladene Modulpaket enthält keine meta.json.</p>';
			}
		} else {
			echo '<p>Fehler: Das heruntergeladene Modulpaket konnte nicht entpackt werden.</p>';
		}

		echo '<p>Lösche das temporäre Modulpaket ' .$tarRelativeFilePath. '.</p>';
		@unlink($tarAbsoluteFilePath);

		echo '<p>Lösche den temporären Modulordner ' .$tempModuleRelativeDirectoryPath. '.</p>';

		if(is_dir($tempModuleAbsoluteDirectoryPath)){
			$libFilesystem->deleteDirectory($tempModuleRelativeDirectoryPath);
		}
	}
}


/*
* update engine
*/
if(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == 'updateEngine'){
	$tarRelativeFilePath = $tempRelativeDirectoryPath. '/engine.tar';
	$tarAbsoluteFilePath = $libFilesystem->getAbsolutePath($tarRelativeFilePath);

	$tempEngineRelativeDirectoryPath = $tempRelativeDirectoryPath. '/engine';
	$tempEngineAbsoluteDirectoryPath = $libFilesystem->getAbsolutePath($tempEngineRelativeDirectoryPath);

	//download engine package
	echo '<p>Lade Enginepaket aus dem Repository.</p>';
	downloadContent('http://' .$repoHostname. '/packages/engine.tar', $tarAbsoluteFilePath);

	//untar engine package
	$tar = new pear\Archive\Archive_Tar($tarRelativeFilePath);
	echo '<p>Entpacke das Enginepaket in den temp-Ordner.</p>';
	$tar->extract($tempRelativeDirectoryPath. '/');

	if(is_dir($tempEngineAbsoluteDirectoryPath)){
		if(is_file($tempEngineAbsoluteDirectoryPath. '/index.php')
				&& is_file($tempEngineAbsoluteDirectoryPath. '/inc.php')
				&& is_dir($tempEngineAbsoluteDirectoryPath. '/vendor')){
			$libCronJobs->deleteFiles();

			unlink($libFilesystem->getAbsolutePath('inc.php'));
			unlink($libFilesystem->getAbsolutePath('index.php'));

			$libFilesystem->deleteDirectory('styles');
			$libFilesystem->deleteDirectory('vendor');

			echo '<p>Installiere die neue Engine.</p>';
			$libFilesystem->copyDirectory($tempEngineRelativeDirectoryPath, '.');

			echo '<p>Führe Aktualisierungsscript der Engine aus.</p>';
			$scriptAbsolutePath = $libFilesystem->getAbsolutePath($engineUpdateScript);
			include($scriptAbsolutePath);
		} else {
			echo '<p>Fehler: Das Enginepaket ist fehlerhaft.</p>';
		}
	} else {
		echo '<p>Fehler: Das heruntergeladene Enginepaket konnte nicht entpackt werden.</p>';
	}

	die('</div><a href="index.php?pid=updater_liste">Klicke hier</a>, um die Modulliste anzuzeigen.');
}

echo 'Lade Paketinformationen aus dem Repository.';
echo '</div>';


/*
* output
*/
echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<p>Das VCMS besteht aus einer Engine und mehreren Modulen, die auf dieser Seite aktualisiert werden können. Die folgende Liste zeigt die im System installierten sowie die im Repository verfügbaren Versionen.</p>';
echo '<table class="table table-condensed">';

echo '<tr>';
echo '<th>Modulname</th><th>Status</th>';
echo '<th>Version<br />(installiert)</th>';
echo '<th>Version<br />(Repository)</th>';
echo '<th class="toolColumn"></th>';
echo '<th class="toolColumn"></th>';
echo '<th class="toolColumn"></th>';
echo '</tr>';

$manifestString = downloadContent('http://' .$repoHostname. '/manifest.php?id=' .$libConfig->sitePath);
$manifestArray = explode("\n", $manifestString);

$modules = array();

foreach($manifestArray as $row){
	$array = explode(' ', $row);

	if(is_array($array) && isset($array[0]) && isset($array[1])){
		$modules[trim($array[0])] = trim($array[1]);
	}
}

$installedModules = array();

foreach($libModuleHandler->getModules() as $module){
	$isBaseModule = substr($module->getId(), 0, 5) == 'base_';

	if(!$isBaseModule){
		if(!array_key_exists($module->getId(), $modules)){
			$modules[$module->getId()] = '';
		}
	}
}

ksort($modules);

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
		if(!$libModuleHandler->moduleIsAvailable($key)){
			if(!$engineIsOld){
				echo '<a href="index.php?pid=updater_liste&amp;modul=' .$key. '&amp;aktion=installModule" onclick="return confirm(\'Willst Du das Modul wirklich installieren?\')">';
				echo '<i class="fa fa-plus-circle" aria-hidden="true"></i>';
				echo '</a>';
			}
		}
	}

	echo '</td>';


	// update action
	echo '<td class="toolColumn">';

	if($key == 'engine'){
		if($engineIsOld){
			echo '<a href="index.php?pid=updater_liste&amp;aktion=updateEngine" onclick="return confirm(\'Willst Du die Engine wirklich aktualisieren?\')">';
			echo '<i class="fa fa-cloud-download" aria-hidden="true"></i>';
			echo '</a>';
		}
	} else {
		if($libModuleHandler->moduleIsAvailable($key)){
			$module = $libModuleHandler->getModuleByModuleid($key);
			$actualversion = (double) $module->getVersion();
			$newversion = (double) $value;

			if($newversion > $actualversion){
				if(!$engineIsOld){
					echo '<a href="index.php?pid=updater_liste&amp;modul=' .$key. '&amp;aktion=updateModule" onclick="return confirm(\'Willst Du das Modul wirklich aktualisieren?\')">';
					echo '<i class="fa fa-cloud-download" aria-hidden="true"></i>';
					echo '</a>';
				}
			}
		}
	}

	echo '</td>';


	// delete action
	echo '<td class="toolColumn">';

	if($key != 'engine'){
		if($libModuleHandler->moduleIsAvailable($key)){
			if(!$engineIsOld){
				$module = $libModuleHandler->getModuleByModuleid($key);
				$actualversion = (double) $module->getVersion();
				$newversion = (double) $value;

				echo '<a href="index.php?pid=updater_liste&amp;modul=' .$key. '&amp;aktion=uninstallModule" onclick="return confirm(\'Willst Du das Modul wirklich deinstallieren?\')">';
				echo '<i class="fa fa-trash" aria-hidden="true"></i>';
				echo '</a>';
			}
		}
	}

	echo '</td>';
	echo '</tr>';
}

echo '</table>';


function downloadContent($url, $destinationFile = false){
	if(ini_get('allow_url_fopen')){
		if(!$destinationFile){
			return file_get_contents($url);
		} else {
			copy($url, $destinationFile);
		}
	} else {
		if(!$destinationFile){
			return \httpclient\HttpClient::quickGet($url);
		} else{
			$contents = \httpclient\HttpClient::quickGet($url);
			file_put_contents($destinationFile, $contents);
		}
	}
}
?>