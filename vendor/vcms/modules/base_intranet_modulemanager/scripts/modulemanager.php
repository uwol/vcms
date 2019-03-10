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


echo '<h1>Module</h1>';


if(isset($_REQUEST['action'])){
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'updateEngine'){
		$libRepositoryClient->updateEngine();
	}

	if(isset($_REQUEST['modul']) && $_REQUEST['modul'] != '' && $_REQUEST['modul'] != 'engine'){
		$module = $_REQUEST['modul'];

		if($_REQUEST['action'] == 'installModule' && $module != ''){
			$libRepositoryClient->installModule($module);
		} elseif($_REQUEST['action'] == 'uninstallModule' && $module != ''){
			$libRepositoryClient->uninstallModule($module);
		}
	}

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo '<p class="mb-4"><a href="index.php?pid=modules" class="btn btn-default" role="button">Module zeigen</a></p>';
} else {
	echo '<p class="mb-4">Das VCMS besteht aus einer Engine und mehreren Modulen, die auf dieser Seite aktualisiert werden können. Die folgende Liste zeigt die im System installierten sowie die im Repository verfügbaren Versionen.</p>';
	echo '<p class="mb-4">Auto-Update: <a href="index.php?pid=configuration">';

	if($libGenericStorage->loadValue('base_core', 'auto_update')){
		echo 'aktiviert';
	} else {
		echo 'deaktiviert';
	}

	echo '</a></p>';

	echo '<div class="panel panel-default">';
	echo '<div class="panel-body">';

	echo '<table class="table table-condensed table-striped table-hover">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Modulname</th><th>Status</th>';
	echo '<th>Version<br />(installiert)</th>';
	echo '<th>Version<br />(Repository)</th>';
	echo '<th class="tool-column"></th>';
	echo '<th class="tool-column"></th>';
	echo '<th class="tool-column"></th>';
	echo '</tr>';
	echo '</thead>';

	$gitHubRepoUrl = 'https://github.com/uwol/vcms/tree/master';
	$modules = $libRepositoryClient->getModuleVersions();

	$actualEngineVersion = (double) $libGlobal->version;
	$newEngineVersion = (double) $modules['engine'];

	$engineIsOld = false;

	if($newEngineVersion > $actualEngineVersion){
		$engineIsOld = true;
	}

	foreach($modules as $key => $value){
		echo '<tr>';

		//module id
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
		echo '<td class="tool-column">';

		if($key != 'engine'){
			if(!$engineIsOld && !$libModuleHandler->moduleIsAvailable($key)){
				echo '<a href="index.php?pid=modules&amp;modul=' .$key. '&amp;action=installModule" onclick="return confirm(\'Willst Du das Modul wirklich installieren?\')">';
				echo '<i class="fa fa-plus-circle" aria-hidden="true"></i>';
				echo '</a>';
			}
		}

		echo '</td>';

		// update action
		echo '<td class="tool-column">';

		if($engineIsOld && $key == 'engine'){
			echo '<a href="index.php?pid=modules&amp;action=updateEngine" onclick="return confirm(\'Willst Du die Engine wirklich aktualisieren?\')">';
			echo '<i class="fa fa-cloud-download" aria-hidden="true"></i>';
			echo '</a>';
		} else {
			if($libModuleHandler->moduleIsAvailable($key)){
				$module = $libModuleHandler->getModuleByModuleid($key);
				$actualVersion = (double) $module->getVersion();
				$newVersion = (double) $value;

				if(!$engineIsOld && $newVersion > $actualVersion){
					echo '<a href="index.php?pid=modules&amp;modul=' .$key. '&amp;action=installModule" onclick="return confirm(\'Willst Du das Modul wirklich aktualisieren?\')">';
					echo '<i class="fa fa-cloud-download" aria-hidden="true"></i>';
					echo '</a>';
				}
			}
		}

		echo '</td>';


		// delete action
		echo '<td class="tool-column">';

		if($key != 'engine'){
			if(!$engineIsOld && $libModuleHandler->moduleIsAvailable($key)){
				$module = $libModuleHandler->getModuleByModuleid($key);
				$actualVersion = (double) $module->getVersion();
				$newVersion = (double) $value;

				echo '<a href="index.php?pid=modules&amp;modul=' .$key. '&amp;action=uninstallModule" onclick="return confirm(\'Willst Du das Modul wirklich deinstallieren?\')">';
				echo '<i class="fa fa-trash" aria-hidden="true"></i>';
				echo '</a>';
			}
		}

		echo '</td>';
		echo '</tr>';
	}

	echo '</table>';
	echo '</div>';
	echo '</div>';

	$libRepositoryClient->resetTempDirectory();
	$libCronjobs->executeJobs();
}
