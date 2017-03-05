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


$gitHubRepoUrl = 'https://github.com/uwol/vcms/tree/master';

/*
* actions
*/
$libRepositoryClient->resetTempDirectory();

if(isset($_REQUEST['modul']) && $_REQUEST['modul'] != '' && $_REQUEST['modul'] != 'engine'){
	$module = $_REQUEST['modul'];

	if($_REQUEST['aktion'] == 'installModule' && $module != ''){
		$libRepositoryClient->installModule($module);
	} elseif($_REQUEST['aktion'] == 'uninstallModule' && $module != ''){
		$libRepositoryClient->uninstallModule($module);
	}
}

if(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == 'updateEngine'){
	$libRepositoryClient->updateEngine();
}

$libCronjobs->executeJobs();
$modules = $libRepositoryClient->getModules();


/*
* output
*/
echo '<h1>Module</h1>';

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
			echo '<a href="index.php?pid=modules&amp;modul=' .$key. '&amp;aktion=installModule" onclick="return confirm(\'Willst Du das Modul wirklich installieren?\')">';
			echo '<i class="fa fa-plus-circle" aria-hidden="true"></i>';
			echo '</a>';
		}
	}

	echo '</td>';


	// update action
	echo '<td class="toolColumn">';

	if($engineIsOld && $key == 'engine'){
		echo '<a href="index.php?pid=modules&amp;aktion=updateEngine" onclick="return confirm(\'Willst Du die Engine wirklich aktualisieren?\')">';
		echo '<i class="fa fa-cloud-download" aria-hidden="true"></i>';
		echo '</a>';
	} else {
		if($libModuleHandler->moduleIsAvailable($key)){
			$module = $libModuleHandler->getModuleByModuleid($key);
			$actualversion = (double) $module->getVersion();
			$newversion = (double) $value;

			if(!$engineIsOld && $newversion > $actualversion){
				echo '<a href="index.php?pid=modules&amp;modul=' .$key. '&amp;aktion=installModule" onclick="return confirm(\'Willst Du das Modul wirklich aktualisieren?\')">';
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

			echo '<a href="index.php?pid=modules&amp;modul=' .$key. '&amp;aktion=uninstallModule" onclick="return confirm(\'Willst Du das Modul wirklich deinstallieren?\')">';
			echo '<i class="fa fa-trash" aria-hidden="true"></i>';
			echo '</a>';
		}
	}

	echo '</td>';
	echo '</tr>';
}

echo '</table>';
