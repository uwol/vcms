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

class LibModuleParser{

	function parseMetaJson($moduleDirectory, $moduleRelativePath){
		global $libFilesystem;

		$moduleAbsolutePath = $libFilesystem->getAbsolutePath($moduleRelativePath);
		$jsonFileContents = file_get_contents($moduleAbsolutePath. '/meta.json');
		$json = json_decode($jsonFileContents, true);

		if(isset($json['version']) && !is_numeric($json['version'])){
			echo('Fehler: Versionsangabe nicht numerisch in Modul ' .$moduleRelativePath. '<br />');
		}

		if(!isset($json['moduleName']) || $json['moduleName'] == ''){
			echo('Fehler: Kein moduleName in Modul ' .$moduleRelativePath. '<br />');
		}

		/*
		* determine module parameters
		*/
		$version = isset($json['version']) ? $json['version'] : '';
		$moduleName = isset($json['moduleName']) ? $json['moduleName'] : '';
		$installScript = isset($json['installScript']) ? $json['installScript'] : '';
		$uninstallScript = isset($json['uninstallScript']) ? $json['uninstallScript'] : '';
		$updateScript = isset($json['updateScript']) ? $json['updateScript'] : '';
		$headerStrings = isset($json['headerStrings']) ? $json['headerStrings'] : '';

		$pages = array();
		$includes = array();
		$menuElementsInternet = array();
		$menuElementsIntranet = array();
		$menuElementsAdministration = array();

		if(isset($json['pages'])){
			foreach($json['pages'] as $pageJson) {
				$page = $this->parsePageJson($pageJson);
				$pages[$page->getPid()] = $page;
			}
		}

		if(isset($json['includes'])){
			foreach($json['includes'] as $includeJson) {
				$include = $this->parseIncludeJson($includeJson);
				$includes[$include->getIid()] = $include;
			}
		}

		if(isset($json['menuElementsInternet'])){
			foreach($json['menuElementsInternet'] as $menuElementInternetJson) {
				$menuElementsInternet[] = $this->parseMenuElement($menuElementInternetJson);
			}
		}

		if(isset($json['menuElementsIntranet'])){
			foreach($json['menuElementsIntranet'] as $menuElementsIntranetJson) {
				$menuElementsIntranet[] = $this->parseMenuElement($menuElementsIntranetJson);
			}
		}

		if(isset($json['menuElementsAdministration'])){
			foreach($json['menuElementsAdministration'] as $menuElementsAdministrationJson) {
				$menuElementsAdministration[] = $this->parseMenuElement($menuElementsAdministrationJson);
			}
		}

		// instantiate new module
		$module = new \vcms\module\LibModule($moduleDirectory, $moduleName,	$version,
			$moduleRelativePath, $pages, $includes, $headerStrings,
			$installScript, $uninstallScript, $updateScript,
			$menuElementsInternet, $menuElementsIntranet, $menuElementsAdministration);
		return $module;
	}

	function parsePageJson($pageJson){
		$pid = isset($pageJson['pid']) ? $pageJson['pid'] : '';
		$file = isset($pageJson['file']) ? $pageJson['file'] : '';
		$directory = isset($pageJson['directory']) ? $pageJson['directory'] : '';
		$accessRestriction = isset($pageJson['accessRestriction']) ? $this->parseAccessRestrictionJson($pageJson['accessRestriction']) : '';
		$title = isset($pageJson['title']) ? $pageJson['title'] : '';

		$page = new \vcms\module\LibPage($pid, $directory, $file, $accessRestriction, $title);
		return $page;
	}

	function parseIncludeJson($includeJson){
		$iid = isset($includeJson['iid']) ? $includeJson['iid'] : '';
		$file = isset($includeJson['file']) ? $includeJson['file'] : '';
		$directory = isset($includeJson['directory']) ? $includeJson['directory'] : '';
		$accessRestriction = isset($includeJson['accessRestriction']) ? $this->parseAccessRestrictionJson($includeJson['accessRestriction']) : '';

		$include = new \vcms\module\LibInclude($iid, $directory, $file, $accessRestriction);
		return $include;
	}

	function parseAccessRestrictionJson($accessRestrictionJson){
		$aemter = isset($accessRestrictionJson['aemter']) ? $accessRestrictionJson['aemter'] : '';
		$gruppen = isset($accessRestrictionJson['gruppen']) ? $accessRestrictionJson['gruppen'] : '';

		$accessRestriction = new \vcms\module\LibAccessRestriction($gruppen, $aemter);
		return $accessRestriction;
	}

	function parseMenuElement($menuElementJson){
		$pid = isset($menuElementJson['pid']) ? $menuElementJson['pid'] : '';
		$name = isset($menuElementJson['name']) ? $menuElementJson['name'] : '';
		$type = isset($menuElementJson['type']) ? $menuElementJson['type'] : '';
		$position = isset($menuElementJson['position']) ? $menuElementJson['position'] : '';

		switch($type){
			case 'menu_entry':
				$menuElement = new \vcms\menu\LibMenuEntry($pid, $name, $position);
				break;
			case 'menu_folder':
				$menuElement = new \vcms\menu\LibMenuFolder($pid, $name, $position);
				break;
			case 'menu_entry_login':
				$nameLogout = isset($menuElementJson['nameLogout']) ? $menuElementJson['nameLogout'] : '';
				$menuElement = new \vcms\menu\LibMenuEntryLogin($pid, $name, $nameLogout, $position);
				break;
			case 'menu_entry_login':
				$menuElement = new \vcms\menu\LibMenuEntryExternalLink($pid, $name, $position);
				break;
		}

		return $menuElement;
	}

	// @Deprecated
	function parseMetaPhp($moduleDirectory, $moduleRelativePath){
		global $libFilesystem;

		$moduleAbsolutePath = $libFilesystem->getAbsolutePath($moduleRelativePath);
		require($moduleAbsolutePath. '/meta.php');

		if($version != '' && !is_numeric($version)){
			echo('Fehler: Versionsangabe nicht numerisch in Modul ' .$moduleRelativePath. '<br />');
		}

		if($moduleName == ''){
			echo('Fehler: Keine Variable moduleName in Modul ' .$moduleRelativePath. '<br />');
		}

		if(!isset($installScript)){
			echo('Fehler: Keine Variable installScript in Modul ' .$moduleRelativePath. '<br />');
		}

		if(!isset($uninstallScript)){
			echo('Fehler: Keine Variable uninstallScript in Modul ' .$moduleRelativePath. '<br />');
		}

		if(!isset($updateScript)){
			echo('Fehler: Keine Variable updateScript in Modul ' .$moduleRelativePath. '<br />');
		}

		if(!is_array($pages)){
			echo('Fehler: Kein Array pages in Modul ' .$moduleRelativePath. '<br />');
		}

		if(!is_array($includes)){
			echo('Fehler: Kein Array includes in Modul ' .$moduleRelativePath. '<br />');
		}

		if(!is_array($headerStrings)){
			echo('Fehler: Kein Array headerStrings in Modul ' .$moduleRelativePath. '<br />');
		}

		if(!is_array($menuElementsInternet)){
			echo('Fehler: Kein Array menuElementsInternet in Modul ' .$moduleRelativePath. '<br />');
		}

		if(!is_array($menuElementsIntranet)){
			echo('Fehler: Kein Array menuElementsIntranet in Modul ' .$moduleRelativePath. '<br />');
		}

		if(!is_array($menuElementsAdministration)){
			echo('Fehler: Kein Array menuElementsAdministration in Modul ' .$moduleRelativePath. '<br />');
		}

		/*
		* determine module parameters
		*/
		$pagesarray = array();
		$includesarray = array();

		foreach($pages as $page){
			$pagesarray[$page->getPid()] = $page;
		}

		foreach($includes as $include){
			$includesarray[$include->getIid()] = $include;
		}

		unset($pages);
		unset($includes);

		// instantiate new module
		$module = new \vcms\module\LibModule($moduleDirectory, $moduleName,	$version,
			$moduleRelativePath, $pagesarray, $includesarray, $headerStrings,
			$installScript, $uninstallScript, $updateScript,
			$menuElementsInternet, $menuElementsIntranet, $menuElementsAdministration);
		return $module;
	}
}