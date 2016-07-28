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

class LibModuleHandler{
	var $modModulesRelativePath = 'modules';
	var $baseModulesRelativePath = 'vendor/vcms/modules';

	var $modules = array();
	var $pidToModulePointer = array();
	var $iidToModulePointer = array();

	var $menuInternet;
	var $menuIntranet;
	var $menuAdministration;

	function __construct(){
		$this->menuInternet = new \vcms\menu\LibMenu();
		$this->menuIntranet = new \vcms\menu\LibMenu();
		$this->menuAdministration = new \vcms\menu\LibMenu();
	}

	function getModModuleFiles(){
		global $libFilesystem;

		$modModulesAbsolutePath = $libFilesystem->getAbsolutePath($this->modModulesRelativePath);
		$result = array_diff(scandir($modModulesAbsolutePath), array('..', '.'));
		sort($result);
		return $result;
	}

	function getBaseModuleFiles(){
		global $libFilesystem;

		$baseModulesAbsolutePath = $libFilesystem->getAbsolutePath($this->baseModulesRelativePath);
		$result = array_diff(scandir($baseModulesAbsolutePath), array('..', '.'));
		sort($result);
		return $result; 
	}

	function initModules(){
		global $libFilesystem;

		$modModuleFiles = $this->getModModuleFiles();
		$baseModuleFiles = $this->getBaseModuleFiles();
		$moduleAbsolutePaths = array();

		foreach($modModuleFiles as $moduleFile){
			$moduleRelativePaths[$moduleFile] = $this->modModulesRelativePath. '/' .$moduleFile;
		}

		foreach($baseModuleFiles as $moduleFile){
			$moduleRelativePaths[$moduleFile] = $this->baseModulesRelativePath. '/' .$moduleFile;
		}

		foreach($moduleRelativePaths as $moduleFile => $moduleRelativePath){
			$moduleAbsolutePath = $libFilesystem->getAbsolutePath($moduleRelativePath);

			if(is_dir($moduleAbsolutePath)){
				$this->initModule($moduleFile, $moduleRelativePath);
			}
		}
	}

	function initModule($moduleDirectory, $moduleRelativePath){
		global $libGlobal, $libFilesystem, $libModuleParser;

		$moduleAbsolutePath = $libFilesystem->getAbsolutePath($moduleRelativePath);

		if(file_exists($moduleAbsolutePath. '/meta.json')){
			$module = $libModuleParser->parseMetaJson($moduleDirectory, $moduleRelativePath);

			$this->modules[$moduleDirectory] = $module;
			$this->validateModule($module);
			$this->registerModule($module, $moduleRelativePath);
		}
	}

	function validateModule($module){
		global $libGlobal, $libSecurityManager;

		foreach($module->pages as $page){
			// does the page have a restriction?
			if($page->hasAccessRestriction()){
				$accessRestriction = $page->getAccessRestriction();

				//does the page have a function restriction?
				if($accessRestriction->hasAemterRestriction()){
					$impossibleAemter = array_diff($accessRestriction->getAemter(),
						$libSecurityManager->getPossibleAemter());

					if(is_array($impossibleAemter) && count($impossibleAemter) > 0){
						$libGlobal->errorTexts[] = 'Seite ' .$page->getPid(). ' in Modul ' .$module->name. ' hat eine Restriktion mit den folgenden nicht vorgesehenen Ämtern: ' .implode(', ', $impossibleAemter);
					}
				}
			}
		}

		foreach($module->includes as $include){
			//does the include have a restriction?
			if($include->hasAccessRestriction()){
				$accessRestriction = $include->getAccessRestriction();

				// does the include haven a function restriction?
				if($accessRestriction->hasAemterRestriction()){
					$impossibleAemter = array_diff($accessRestriction->getAemter(),
						$libSecurityManager->getPossibleAemter());

					if(is_array($impossibleAemter) && count($impossibleAemter) > 0){
						$libGlobal->errorTexts[] = 'Include ' .$include->getPid(). ' in Modul ' . $module->name. ' hat eine Restriktion mit den folgenden nicht vorgesehenen Ämtern: ' .implode(', ', $impossibleAemter);
					}
				}
			}
		}

		foreach($module->pages as $page){
			//check for colliding pid
			if(array_key_exists($page->getPid(), $this->pidToModulePointer)){
				$libGlobal->errorTexts[] = 'Die Seiten-Id ' .$page->getPid(). ' existiert bereits für eine Seite. Doppelte Seiten-Id-Vergabe ist nicht erlaubt.';
			}
		}

		foreach($module->includes as $include){
			//check for colliding pid
			if(array_key_exists($include->getIid(), $this->iidToModulePointer)){
				$libGlobal->errorTexts[] = 'Die Include-Id ' .$include->getIid(). ' existiert bereits für einen Include. Doppelte Include-Id-Vergabe ist nicht erlaubt.';
			}
		}

		foreach($module->menuElementsInternet as $menuElement){
			if(!$this->menuElementHasValidPid($menuElement, $module->pages)){
				$libGlobal->errorTexts[] = 'Die Seiten-Id ' .$menuElement->getPid(). ' in Modul ' .$module->name. ' existiert nicht für eine Seite, ist aber in einem Menüeintrag angegeben.';
			}
		}

		foreach($module->menuElementsIntranet as $menuElement){
			if(!$this->menuElementHasValidPid($menuElement, $module->pages)){
				$libGlobal->errorTexts[] = 'Die Seiten-Id ' .$menuElement->getPid(). ' in Modul ' .$module->name. ' existiert nicht für eine Seite, ist aber in einem Menüeintrag angegeben.';
			}
		}

		foreach($module->menuElementsAdministration as $menuElement){
			if(!$this->menuElementHasValidPid($menuElement, $module->pages)){
				$libGlobal->errorTexts[] = 'Die Seiten-Id ' .$menuElement->getPid(). ' in Modul ' .$module->name. ' existiert nicht für eine Seite, ist aber in einem Menüeintrag angegeben.';
			}
		}
	}

	function registerModule($module, $moduleRelativePath){
		foreach($module->pages as $page){
			$page->setDirectory($moduleRelativePath. '/' .$page->getDirectory());
		}

		foreach($module->includes as $include){
			$include->setDirectory($moduleRelativePath .'/'. $include->getDirectory());
		}

		foreach($module->pages as $page){
			$this->pidToModulePointer[$page->getPid()] = $module;
		}

		foreach($module->includes as $include){
			$this->iidToModulePointer[$include->getIid()] = $module;
		}

		foreach($module->menuElementsInternet as $menuElement){
			$this->menuElementAddAccessRestriction($menuElement, $module->pages);
			$this->menuInternet->addMenuElement($menuElement);
		}

		foreach($module->menuElementsIntranet as $menuElement){
			$this->menuElementAddAccessRestriction($menuElement, $module->pages);
			$this->menuIntranet->addMenuElement($menuElement);
		}

		foreach($module->menuElementsAdministration as $menuElement){
			$this->menuElementAddAccessRestriction($menuElement, $module->pages);
			$this->menuAdministration->addMenuElement($menuElement);
		}
	}

	function menuElementHasValidPid($menuElement, $pages){
		if($menuElement->getPid() != '' && ($menuElement->getType() == 1 || $menuElement->getType() == 2)){
			$pidPresent = false;

			foreach($pages as $page){
				if($page->getPid() == $menuElement->getPid()){
					$pidPresent = true;
				}
			}
		} else {
			$pidPresent = true;
		}

		return $pidPresent;
	}

	function menuElementAddAccessRestriction($menuElement, $pages){
		global $libGlobal;

		//for all menu entries except external links
		if($menuElement->getType() != 3){
			if($menuElement->getPid() != ''){
				$pageFound = false;

				foreach($pages as $page){
					//select the page for the pid
					if($page->getPid() == $menuElement->getPid()){
						$menuElement->setAccessRestriction($page->getAccessRestriction());
						$pageFound = true;
					}
				}

				if(!$pageFound){
					$libGlobal->errorTexts[] = 'Für das Menüelement ' .$menuElement->getPid(). ' existiert keine Seite.';
				}
			}
		}

		//a menu folder?
		if($menuElement->getType() == 2){
			$elements = $menuElement->getElements();

			for($i=0;$i<count($elements);$i++){
				$subMenuElement = $elements[$i];
				$this->menuElementAddAccessRestriction($subMenuElement, $pages);
			}
		}
	}

	function getPage($pid){
		global $libGlobal;

		if(!array_key_exists($pid, $this->pidToModulePointer)){
			$libGlobal->errorTexts[] = 'Angeforderte Page-Id ' .$pid. ' unbekannt.';
		} else {
			$pages = $this->pidToModulePointer[$pid]->getPages();
			return $pages[$pid];
		}
	}

	function pageExists($pid){
		return array_key_exists($pid, $this->pidToModulePointer);
	}

	function getInclude($iid){
		global $libGlobal;

		if(!array_key_exists($iid, $this->iidToModulePointer)){
			$libGlobal->errorTexts[] = 'Angeforderte Include-Id ' .$iid. ' unbekannt.';
		} else {
			$includes = $this->iidToModulePointer[$iid]->getIncludes();
			return $includes[$iid];
		}
	}

	function includeExists($iid){
		return array_key_exists($iid, $this->iidToModulePointer);
	}

	function getModuleByPageid($pid){
		global $libGlobal;

		if(!array_key_exists($pid, $this->pidToModulePointer)){
			$libGlobal->errorTexts[] = 'Angeforderte Page-Id ' .$pid. ' unbekannt.';
		} else {
			return $this->pidToModulePointer[$pid];
		}
	}

	function getModuleByIncludeid($iid){
		global $libGlobal;

		if(!array_key_exists($iid, $this->iidToModulePointer)){
			$libGlobal->errorTexts[] = 'Angeforderte Include-Id ' .$iid. ' unbekannt.';
		} else {
			return $this->iidToModulePointer[$iid];
		}
	}

	function getModuleByModuleid($moduleid){
		global $libGlobal;

		if(!array_key_exists($moduleid, $this->modules)){
			$libGlobal->errorTexts[] = 'Angeforderte Modul-Id ' .$moduleid. ' unbekannt.';
		} else {
			return $this->modules[$moduleid];
		}
	}

	function getModule(){
		global $libGlobal;

		if($libGlobal->pid != ''){
			return $this->getModuleByPageid($libGlobal->pid);
		} elseif($libGlobal->iid != ''){
			return $this->getModuleByIncludeid($libGlobal->iid);
		} else {
			$libGlobal->errorTexts[] = 'Weder $libGlobal->pid noch $libGlobal->iid sind mit einem Wert belegt';
		}
	}

	function getModuleDirectoryByPageid($pid){
		$module = $this->getModuleByPageid($pid);
		return $module->getPath();
	}

	function getModuleDirectoryByIncludeid($iid){
		$module = $this->getModuleByIncludeid($iid);
		return $module->getPath();
	}

	function getModuleDirectoryByModuleid($moduleid){
		$module = $this->getModuleByModuleid($moduleid);
		return $module->getPath();
	}

	function getModuleDirectory(){
		global $libGlobal;

		if($libGlobal->pid != ''){
			return $this->getModuleDirectoryByPageid($libGlobal->pid);
		} elseif($libGlobal->iid != ''){
			return $this->getModuleDirectoryByIncludeid($libGlobal->iid);
		} else {
			$libGlobal->errorTexts[] = 'Weder $libGlobal->pid noch $libGlobal->iid sind mit einem Wert belegt';
		}
	}

	function moduleIsAvailable($moduleId){
		return array_key_exists($moduleId, $this->modules);
	}

	function getModules(){
		return $this->modules;
	}

	function getMenuInternet(){
		$menu = $this->menuInternet;

		$menu->canonizeElements();
		$menu->sortElementsByPosition();
		$menu->applyMinAccessRestriction();

		return $menu;
	}

	function getMenuIntranet(){
		$menu = $this->menuIntranet;

		$menu->canonizeElements();
		$menu->sortElementsByPosition();
		$menu->applyMinAccessRestriction();

		return $menu;
	}

	function getMenuAdministration(){
		$menu = $this->menuAdministration;

		$menu->canonizeElements();
		$menu->sortElementsByPosition();
		$menu->applyMinAccessRestriction();

		return $menu;
	}
}