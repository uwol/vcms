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


require_once('lib/thirdparty/PEAR/Archive/Tar.php');

$repoHostname = 'repository.' . $libGlobal->vcmsHostname;
$gitHubRepoUrl = 'https://github.com/uwol/vcms/tree/master';

echo '<h1>Module</h1>';
echo '<div class="alert alert-info" role="alert">';

/*
* clean up
*/
deleteDirectory('temp');
@mkdir('temp');
$libCronJobs->repairHtaccessFiles();


/*
* actions
*/
if(isset($_REQUEST['modul']) && $_REQUEST['modul'] != '' && $_REQUEST['modul'] != 'engine'){
	/*
	* install module
	*/
	if($_REQUEST['aktion'] == 'installModule' && $_REQUEST['modul'] != ''){
		//module not installed, yet?
		if(!$libModuleHandler->moduleIsAvailable($_REQUEST['modul'])){

			//download module package
			echo '<p>Lade Modulpaket aus dem Repository.</p>';
			downloadContent('http://' .$repoHostname. '/packages/'. $_REQUEST['modul']. '.tar', './temp/' .$_REQUEST['modul']. '.tar');

			//untar module package
			$tar = new Archive_Tar('./temp/' .$_REQUEST['modul']. '.tar');
			echo '<p>Entpacke das Paket in den temp-Ordner.</p>';
			$tar->extract('temp/');

			if(is_dir('temp/' .$_REQUEST['modul'])){
				if(is_file('temp/' .$_REQUEST['modul']. '/meta.php')){
					if(!is_dir('modules/' .$_REQUEST['modul'])){

						//copy temporary module folder
						copyFolder('temp/' .$_REQUEST['modul']. '/', 'modules/' .$_REQUEST['modul']);

						//refresh module handler
						$libModuleHandler = new LibModuleHandler();

						//run installation script
						$module = $libModuleHandler->getModuleByModuleid($_REQUEST['modul']);

						if($module->getInstallScript() != ''){
							echo '<p>Führe Installationsskript des Moduls aus.</p>';
							include($module->getPath(). '/'. $module->getInstallScript());
						}
					} else {
						echo '<p>Fehler: Das Modul ist bereits installiert.</p>';
					}
				} else {
					echo '<p>Fehler: Das heruntergeladene Modulpaket enthält keine meta.php</p>';
				}
			} else {
				echo '<p>Fehler: Das heruntergeladene Modulpaket konnte nicht entpackt werden.</p>';
			}

			//delete temporary module folder
			echo '<p>Lösche das temporäre Modulpaket aus dem Ordner temp.</p>';
			@unlink('./temp/' .$_REQUEST['modul']. '.tar');

			echo '<p>Lösche den temporären Modulordner aus dem Ordner temp.</p>';

			if(is_dir('./temp/' .$_REQUEST['modul'])){
				deleteDirectory('./temp/' .$_REQUEST['modul']);
			}
		} else {
			echo '<p>Fehler: Das Modul ist bereits installiert.</p>';
		}
	}

	/*
	* uninstall module
	*/
	elseif($_REQUEST['aktion'] == 'uninstallModule' && $_REQUEST['modul'] != ''){
		if($_REQUEST['modul'] != 'engine'){

			//run uninstall script
			$module = $libModuleHandler->getModuleByModuleid($_REQUEST['modul']);

			if(is_object($module) && $module->getUninstallScript() != ''){
				echo '<p>Führe Deinstallationsskript des Moduls aus.</p>';
				include($module->getPath(). '/' .$module->getUninstallScript());
			}

			//delete module directory
			echo '<p>Lösche den Modulordner aus dem Ordner modules/.</p>';
			deleteDirectory('./modules/' .$_REQUEST['modul']);

			//refresh module handler
			$libModuleHandler = new LibModuleHandler();
		} else {
			echo 'Fehler: Das Modul darf nicht deinstalliert werden.';
		}
	}

	/*
	* update module
	*/
	elseif(($_REQUEST['aktion'] == 'updateModule' || $_REQUEST['aktion'] == 'reinstallModule') && $_REQUEST['modul'] != ''){
		//download module package
		echo '<p>Lade Modulpaket aus dem Repository.</p>';
		downloadContent('http://' .$repoHostname. '/packages/'. $_REQUEST['modul']. '.tar',
			'./temp/' .$_REQUEST['modul']. '.tar');

		//untar module package
		$tar = new Archive_Tar('./temp/' .$_REQUEST['modul']. '.tar');
		echo '<p>Entpacke das Paket in den temp-Ordner.</p>';
		$tar->extract('temp/');

		if(is_dir('temp/' .$_REQUEST['modul'])){
			if(is_file('temp/' .$_REQUEST['modul']. '/meta.php')){
				if(is_dir('modules/' .$_REQUEST['modul'])){

					if(is_dir('temp/' .$_REQUEST['modul']. '/custom')){
						deleteDirectory('./temp/' .$_REQUEST['modul']. '/custom');
					}

					//clean module folder except custom
					$files = array_diff(scandir('modules/' .$_REQUEST['modul']), array('..', '.', 'custom'));

					foreach ($files as $file){
						if(is_dir('modules/' .$_REQUEST['modul']. '/' .$file)){
							echo 'Lösche '.'modules/' .$_REQUEST['modul']. '/' .$file. '<br />';
							deleteDirectory('modules/' .$_REQUEST['modul']. '/' .$file);
						} elseif(is_file('modules/' .$_REQUEST['modul']. '/' .$file)){
							echo 'Lösche modules/' .$_REQUEST['modul']. '/' .$file. '<br />';
							unlink('modules/' .$_REQUEST['modul']. '/' .$file);
						}
					}

					//copy all temporary files except the custom directory
					echo '<p>Kopiere aktualisiertes Modul in den Modulordner modules/' .$_REQUEST['modul']. '</p>';

					$files = array_diff(scandir('temp/' .$_REQUEST['modul']), array('..', '.', 'custom'));

					foreach ($files as $file){
						if(is_dir('temp/' .$_REQUEST['modul']. '/' .$file)){
							copyFolder('temp/' .$_REQUEST['modul']. '/' .$file, 'modules/' .$_REQUEST['modul']. '/' .$file);
						} elseif(is_file('temp/' .$_REQUEST['modul']. '/' .$file)){
							copy('temp/' .$_REQUEST['modul']. '/' .$file, 'modules/' .$_REQUEST['modul']. '/' .$file);
						}
					}

					//refresh module handler
					$libModuleHandler = new LibModuleHandler();

					//run update script
					$module = $libModuleHandler->getModuleByModuleid($_REQUEST['modul']);

					if($module->getUpdateScript() != ''){
						echo '<p>Führe Aktualisierungsscript des Moduls aus.</p>';
						include($module->getPath(). '/' .$module->getUpdateScript());
					}
				} else {
					echo '<p>Fehler: Das zu aktualisierende Modul ist nicht installiert.</p>';
				}
			} else {
				echo '<p>Fehler: Das heruntergeladene Modulpaket enthält keine meta.php.</p>';
			}
		} else {
			echo '<p>Fehler: Das heruntergeladene Modulpaket konnte nicht entpackt werden.</p>';
		}

		echo '<p>Lösche das temporäre Modulpaket aus dem Ordner temp.</p>';
		@unlink('./temp/' .$_REQUEST['modul']. '.tar');

		echo '<p>Lösche den temporären Modulordner aus dem Ordner temp.</p>';

		if(is_dir('./temp/' .$_REQUEST['modul'])){
			deleteDirectory('./temp/' .$_REQUEST['modul']);
		}
	}
}


/*
* update engine
*/
if(isset($_REQUEST['aktion']) && ($_REQUEST['aktion'] == 'updateEngine' || $_REQUEST['aktion'] == 'reinstallEngine')){
	//download engine package
	echo '<p>Lade Enginepaket aus dem Repository.</p>';
	downloadContent('http://' .$repoHostname. '/packages/engine.tar',
		'./temp/engine.tar');

	//untar engine package
	$tar = new Archive_Tar('./temp/engine.tar');
	echo '<p>Entpacke das Enginepaket in den temp-Ordner.</p>';
	$tar->extract('temp/');

	if(is_dir('temp/engine')){
		if(is_file('temp/engine/index.php') && is_file('temp/engine/inc.php') && is_dir('temp/engine/lib')){
			$libCronJobs->deleteFiles();

			@unlink('inc.php');
			@unlink('index.php');

			deleteDirectory('design');
			deleteDirectory('js');
			deleteDirectory('lib');
			deleteDirectory('styles');

			echo '<p>Installiere die neue Engine.</p>';

			copyFolder('temp/engine', '.');
		} else {
			echo '<p>Fehler: Das Enginepaket ist fehlerhaft.</p>';
		}
	} else {
		echo '<p>Fehler: Das heruntergeladene Enginepaket konnte nicht entpackt werden.</p>';
	}

	echo '<p>Lösche das temporäre Enginepaket aus dem Ordner temp.</p>';
	@unlink('./temp/engine.tar');

	echo '<p>Lösche den temporären Engineordner aus dem Ordner temp.</p>';

	if(is_dir('./temp/engine')){
		deleteDirectory('./temp/engine');
	}

	die('</div><a href="index.php?pid=updater_liste">Klicke hier</a>, um die Modulliste anzuzeigen.');
}

echo '<p>Lade Paketinformationen aus dem Repository.</p>';
echo '</div>';


/*
* output
*/
echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<p>Das VCMS besteht aus einer Engine und mehreren Modulen, die auf dieser Seite aktualisiert werden können. Die folgende Liste zeigt die im System installierten sowie die im Repository verfügbaren Versionen. Mit base_ markierte Module können nicht deinstalliert werden, weil sie grundlegende Funktionen im VCMS erfüllen.</p>';
echo '<p>Während einer Installation, Deinstallation und Aktualisierung darf der Vorgang nicht abgebrochen werden. Generell sollten regelmäßig von den Verzeichnissen auf dem Webserver per FTP und der Datenbank per Datenbankbackupmodul Backups angefertigt werden, insbesondere vor Updates.</p>';

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
	if(!array_key_exists($module->getId(), $modules)){
		$modules[$module->getId()] = '';
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

				if(substr($key, 0, 5) != 'base_'){
					echo '<a href="index.php?pid=updater_liste&amp;modul=' .$key. '&amp;aktion=uninstallModule" onclick="return confirm(\'Willst Du das Modul wirklich deinstallieren?\')">';
					echo '<i class="fa fa-trash" aria-hidden="true"></i>';
					echo '</a>';
				}
			}
		}
	}

	echo '</td>';
	echo '</tr>';
}

//delete installer and recreate htaccess files
$libCronJobs->deleteFiles();
$libCronJobs->repairHtaccessFiles();

echo '</table>';


function deleteDirectory($directory){
	if(is_dir($directory)){
		$files = array_diff(scandir($directory), array('..', '.'));

		foreach ($files as $file){
			if(is_dir($directory. '/' .$file)){
				deleteDirectory($directory. '/' .$file);
			} elseif(is_file($directory. '/' .$file)){
				unlink($directory. '/' .$file);
			}
		}

		if(is_dir($directory)){
			rmdir($directory);
		}
	}
}

function copyFolder($source, $dest){
	if(!is_dir($dest)){
		mkdir($dest);
	}

	$files = array_diff(scandir($source), array('..', '.'));

    foreach ($files as $file){
        if(is_dir($source. '/' .$file)){
            copyFolder($source. '/' .$file, $dest. '/' .$file);
        } else {
            copy($source. '/' .$file, $dest. '/' .$file);
        }
    }
}

function downloadContent($url, $destinationFile = false){
	if(ini_get('allow_url_fopen')){
		if(!$destinationFile){
			return file_get_contents($url);
		} else {
			copy($url, $destinationFile);
		}
	} else {
		require_once('lib/thirdparty/HttpClient.class.php');

		if(!$destinationFile){
			return HttpClient::quickGet($url);
		} else{
			$contents = HttpClient::quickGet($url);
			file_put_contents($destinationFile, $contents);
		}
	}
}
?>