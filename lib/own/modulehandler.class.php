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

class LibModuleHandler{
	var $modules = array();
	var $pidToModulePointer = array();
	var $iidToModulePointer = array();

	var $menuInternet;
	var $menuIntranet;
	var $menuAdministration;

	function __construct(){
		global $libConfig;

		$this->menuInternet = new LibMenu();
		$this->menuIntranet = new LibMenu();
		$this->menuAdministration = new LibMenu();

		$this->initModules();
	}

	function initModules(){
		$modulespath = 'modules';

		$files = array_diff(scandir($modulespath), array('..', '.'));

		foreach ($files as $file){
			if(is_dir($modulespath .'/'. $file)){
				$this->initModule($file);
			}
	
			$this->checkDependencies();
		} else {
			die('Fehler: Das Modulverzeichnis kann nicht geöffnet werden.');
		}
	}

	function initModule($directory){
		global $libConfig, $libGlobal, $libString, $libTime, $libVerein, $libDb, $libMitglied, $libGenericStorage, $libSecurityManager, $libAuth;

		$modulePath = 'modules/' . $directory;

		//read meta.php of module
		if(file_exists($modulePath. '/meta.php')){
			require($modulePath. '/meta.php');
		} else {
			echo('Fehler: Die Modulinformationsdatei ' .$modulePath. '/meta.php konnte nicht gefunden werden.<br />');
		}

		// validate meta.php; following variables have to be present in meta.php
		if($version == ''){
			echo('Fehler: Keine Variable version in Modul ' .$modulePath. '<br />');
		}

		if(!is_numeric($version)){
			echo('Fehler: Versionsangabe nicht numerisch in Modul ' .$modulePath. '. Korrekt wäre zum Beispiel 1.6<br />');
		}

		if($moduleName == ''){
			echo('Fehler: Keine Variable moduleName in Modul ' .$modulePath. '<br />');
		}

		if(!isset($styleSheet)){
			echo('Fehler: Keine Variable styleSheet in Modul ' .$modulePath. '<br />');
		}

		if(!isset($installScript)){
			echo('Fehler: Keine Variable installScript in Modul ' .$modulePath. '<br />');
		}

		if(!isset($uninstallScript)){
			echo('Fehler: Keine Variable uninstallScript in Modul ' .$modulePath. '<br />');
		}

		if(!isset($updateScript)){
			echo('Fehler: Keine Variable updateScript in Modul ' .$modulePath. '<br />');
		}

		if(!is_array($pages)){
			echo('Fehler: Kein Array pages in Modul ' .$modulePath. '<br />');
		}

		if(!is_array($dependencies)){
			echo('Fehler: Kein Array dependencies in Modul ' .$modulePath. '<br />');
		}

		if(!is_array($includes)){
			echo('Fehler: Kein Array includes in Modul ' .$modulePath. '<br />');
		}

		if(!is_array($headerStrings)){
			echo('Fehler: Kein Array headerStrings in Modul ' .$modulePath. '<br />');
		}

		if(!is_array($menuElementsInternet)){
			echo('Fehler: Kein Array menuElementsInternet in Modul ' .$modulePath. '<br />');
		}

		if(!is_array($menuElementsIntranet)){
			echo('Fehler: Kein Array menuElementsIntranet in Modul ' .$modulePath. '<br />');
		}

		if(!is_array($menuElementsAdministration)){
			echo('Fehler: Kein Array menuElementsAdministration in Modul ' .$modulePath. '<br />');
		}

		//check for colliding module id
		if(array_key_exists($directory, $this->modules)){
			echo('Fehler: Die Modul-Id '. $directory. ' in Modul ' .$modulePath. ' ist bereits in Modul ' .$this->modules[$directory]->getName(). ' im Verzeichnis ' .$this->modules[$directory]->getPath(). ' vergeben.<br />');
		}

		//regenerate page array
		$pagesarray = array();

		foreach($pages as $page){
			// does the page have a restriction?
			if($page->hasAccessRestriction()){
				$accessRestriction = $page->getAccessRestriction();

				//does the page have a function restriction?
				if($accessRestriction->hasAemterRestriction()){
					$impossibleAemter = array_diff($accessRestriction->getAemter(),
						$libSecurityManager->getPossibleAemter());

					if(is_array($impossibleAemter) && count($impossibleAemter) > 0){
						echo('Fehler: Seite ' .$page->getPid(). ' in Modul ' .$modulePath. ' hat eine Restriktion mit den folgenden nicht vorgesehenen Ämtern: ' .implode(', ', $impossibleAemter). '<br />');
					}
				}
			}

			$pagedir = $page->getDirectory();

			if($pagedir != '' && substr($pagedir, strlen($pagedir)-1, 1) != '/'){
				echo('In Modul ' .$modulePath. ' endet der Pfad ' .$pagedir. ' der Seite '.$page->getPid().' nicht mit einem / <br />');
			}

			$page->setDirectory($modulePath. '/' .$page->getDirectory());
			$pagesarray[$page->getPid()] = $page;
		}

		//from now on only pagesarray
		unset($pages);

		//regenerate includes array
		$includesarray = array();

		foreach($includes as $include){
			//does the include have a restriction?
			if($include->hasAccessRestriction()){
				$accessRestriction = $include->getAccessRestriction();

				// does the include haven a function restriction?
				if($accessRestriction->hasAemterRestriction()){
					$impossibleAemter = array_diff($accessRestriction->getAemter(),
						$libSecurityManager->getPossibleAemter());

					if(is_array($impossibleAemter) && count($impossibleAemter) >0){
						echo('Fehler: Include ' .$include->getPid(). ' in Modul ' . $modulePath. ' hat eine Restriktion mit den folgenden nicht vorgesehenen Ämtern: ' .implode(', ', $impossibleAemter). '<br />');
					}
				}
			}

			$includeDir = $include->getDirectory();

			if($includeDir != '' && substr($includeDir,strlen($includeDir)-1,1) != '/'){
				echo('In Modul '. $modulePath. ' endet der Pfad ' .$includeDir. ' des Include '.$include->getIid().' nicht mit einem / <br />');
			}

			$include->setDirectory($modulePath .'/'. $include->getDirectory());
			$includesarray[$include->getIid()] = $include;
		}

		//from now on only includesarray
		unset($includes);

		//instantiate module object
		$module = new LibModule($directory, $moduleName,
			$version, $dependencies, $modulePath, $pagesarray, $styleSheet, $includesarray, $headerStrings, $installScript, $uninstallScript, $updateScript);
		$this->modules[$directory] = $module;

		//reference pages
		foreach($pagesarray as $page){
			//check for colliding pid
			if(array_key_exists($page->getPid(), $this->pidToModulePointer)){
				echo('Fehler: Die Seiten-Id ' .$page->getPid(). ' existiert bereits für eine Seite. Doppelte Seiten-Id-Vergabe ist nicht erlaubt.<br />');
			}

			$this->pidToModulePointer[$page->getPid()] = $module;
		}

		//reference includes
		foreach($includesarray as $include){
			//check for colliding pid
			if(array_key_exists($include->getIid(), $this->iidToModulePointer)){
				echo('Fehler: Die Include-Id ' .$include->getIid(). ' existiert bereits für ein Include. Doppelte Include-Id-Vergabe ist nicht erlaubt.<br />');
			}

			$this->iidToModulePointer[$include->getIid()] = $module;
		}

		//read internet menu elements
		foreach($menuElementsInternet as $menuElement){
			if(!$this->menuElementHasValidPid($menuElement, $pagesarray)){
				echo('Fehler: Die Seiten-Id ' .$menuElement->getPid(). ' in Modul ' .$modulePath. ' existiert nicht für eine Seite, ist aber in einem Menüeintrag angegeben.<br />');
			}

			$this->menuElementAddAccessRestriction($menuElement, $pagesarray);
			$this->menuInternet->addMenuElement($menuElement);
		}

		//read intranet menu element
		foreach($menuElementsIntranet as $menuElement){
			if(!$this->menuElementHasValidPid($menuElement, $pagesarray)){
				echo('Fehler: Die Seiten-Id ' .$menuElement->getPid(). ' in Modul ' .$modulePath. ' existiert nicht für eine Seite, ist aber in einem Menüeintrag angegeben.<br />');
			}

			$this->menuElementAddAccessRestriction($menuElement, $pagesarray);
			$this->menuIntranet->addMenuElement($menuElement);
		}

		//read administration menu elements
		foreach($menuElementsAdministration as $menuElement){
			if(!$this->menuElementHasValidPid($menuElement, $pagesarray)){
				echo('Fehler: Die Seiten-Id ' .$menuElement->getPid(). ' in Modul ' .$modulePath. ' existiert nicht für eine Seite, ist aber in einem Menüeintrag angegeben.<br />');
			}

			$this->menuElementAddAccessRestriction($menuElement, $pagesarray);
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
					echo('Fehler in menuElementAddAccessRestriction(): Seite des Menüelementes ' .$menuElement->getPid(). ' nicht gefunden.<br />');
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

	function checkDependencies(){
		foreach($this->modules as $module){
			$dependencies = $module->getDependencies();

			//for each dependeny
			foreach($dependencies as $dependency){
				$modulPresent = false;
				$rightVersion = false;

				foreach($this->modules as $module2){
					//is the required module present?
					if($dependency->getModuleId() == $module2->getId()){
						$modulPresent = true;

						//min dependeny?
						if($dependency->getDependencyType() == 1){
							//is the module version sufficient?
							if($module2->getVersion() >= $dependency->getModuleVersion()){
								$rightVersion = true;
							}
						}
						//exact dependency?
						elseif($dependency->getDependencyType() == 2){
							//is the module version exactly the required one?
							if($module2->getVersion() == $dependency->getModuleVersion()){
								$rightVersion = true;
							}
						}
					}
				}

				if(!$modulPresent){
					echo('Fehler: Modul ' .$module->getName() .' hat unerfüllte Abhängigkeit: Dependency '. $dependency->getDependencyName(). ' mit Modul-Id '. $dependency->getModuleId(). ' ist nicht erfüllt<br />');
				}

				if(!$rightVersion){
					$masterModule = $this->getModule($dependency->getModuleId());

					//min dependency
					if($dependency->getDependencyType() == 1){
						echo('Fehler: Modul ' .$module->getName() .' hat unerfüllte MinDependency: Modul ' .$masterModule->getName(). ' mit Modul-Id ' .$masterModule->getId(). ' hat Version ' .$masterModule->getVersion(). ', gefordert ist mindestens Version ' .$dependency->getModuleVersion(). '<br />');
					}
					//exakt dependency
					elseif($dependency->getDependencyType() == 2){
						echo('Fehler: Modul ' .$module->getName() .' hat unerfüllte ExaktDependency: Modul ' . $masterModule->getName(). ' mit Modul-Id ' .$masterModule->getName(). ' hat Version ' .$masterModule->getVersion(). ', gefordert ist exakt Version ' .$dependency->getModuleVersion(). '<br />');
					} else {
						echo('Fehler: Der Modulehandler hat bei der Dependency-Fehlerausgabe Probleme');
					}
				}
			}
		}
	}

	function getPage($pid){
		if(!array_key_exists($pid, $this->pidToModulePointer)){
			echo('Fehler in getPage(): Kann Seiteneintrag nicht laden. Die Seiten-Id ' .$pid. ' existiert nicht. Evtl. hat das entsprechende Modul die Seiten-Id nicht registriert.<br />');
		} else {
			$pages = $this->pidToModulePointer[$pid]->getPages();
			return $pages[$pid];
		}
	}

	function pageExists($pid){
		return array_key_exists($pid, $this->pidToModulePointer);
	}

	function getInclude($iid){
		if(!array_key_exists($iid, $this->iidToModulePointer)){
			echo('Fehler in getInclude(): Kann Includeeintrag nicht laden. Die Include-Id ' .$iid. ' existiert nicht. Evtl. hat das entsprechende Modul die Include-Id nicht registriert.<br />');
		} else {
			$includes = $this->iidToModulePointer[$iid]->getIncludes();
			return $includes[$iid];
		}
	}

	function includeExists($iid){
		return array_key_exists($iid, $this->iidToModulePointer);
	}

	function getModuleByPageid($pid){
		if(!array_key_exists($pid, $this->pidToModulePointer)){
			echo('Fehler in getPage(): Kann Seiteneintrag nicht laden. Die Seiten-Id ' .$pid. ' existiert nicht. Evtl. hat das entsprechende Modul die Seiten-Id nicht registriert.<br />');
		} else {
			return $this->pidToModulePointer[$pid];
		}
	}

	function getModuleByIncludeid($iid){
		if(!array_key_exists($iid, $this->iidToModulePointer)){
			echo('Fehler in getModuleByIncludeid(): Kann Includeeintrag nicht laden. Die Include-Id ' .$iid. ' existiert nicht. Evtl. hat das entsprechende Modul die Include-Id nicht registriert.<br />');
		} else {
			return $this->iidToModulePointer[$iid];
		}
	}

	function getModuleByModuleid($moduleid){
		if(!array_key_exists($moduleid, $this->modules)){
			echo('Fehler in getModuleByModuleid(): Kann Module nicht finden. Die Module-Id ' .$moduleid. ' existiert nicht im Modularray.<br />');
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
			echo('Fehler: Weder $libGlobal->pid noch $libGlobal->iid sind mit einem Wert belegt<br />');
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
			echo('Fehler: Weder $libGlobal->pid noch $libGlobal->iid sind mit einem Wert belegt<br />');
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
		$menu->sortElementsByPosition();
		$menu->applyMinAccessRestriction();

		return $menu;
	}

	function getMenuIntranet(){
		$menu = $this->menuIntranet;
		$menu->sortElementsByPosition();
		$menu->applyMinAccessRestriction();

		return $menu;
	}

	function getMenuAdministration(){
		$menu = $this->menuAdministration;
		$menu->sortElementsByPosition();
		$menu->applyMinAccessRestriction();

		return $menu;
	}

}

class LibModule{
	var $id;
	var $version;
	var $name;
	var $dependencies = array();
	var $path;
	var $pages;
	var $styleSheet;
	var $includes;
	var $headerStrings;
	var $installScript;
	var $uninstallScript;
	var $updateScript;

	function __construct($id, $name, $version, &$dependencies, $path, &$pages, $styleSheet, &$includes, $headerStrings, $installScript, $uninstallScript, $updateScript){
		if($id == ''){
			echo('Fehler: Fehlende Module-Id<br />');
		}

		if($version == ''){
			echo('Fehler: Fehlende Versionsangabe<br />');
		}

		if(!is_numeric($version)){
			echo('Fehler: Versionsangabe nicht numerisch. Korrekt wäre zum Beispiel 1.6<br />');
		}

		if($name == ''){
			echo('Fehler: Fehlende Namensangabe<br />');
		}

		if(!is_array($dependencies)){
			echo('Fehler: Fehlendes Array dependencies<br />');
		}

		if($path == ''){
			echo('Fehler: Fehlender Modulpfad<br />');
		}

		if(!is_array($pages)){
			echo('Fehler: Fehlendes Array pages<br />');
		}

		if(!is_array($includes)){
			echo('Fehler: Fehlendes Array includes<br />');
		}

		$this->id = $id;
		$this->version = $version;
		$this->name = $name;
		$this->dependencies = $dependencies;
		$this->path = $path;
		$this->pages = $pages;
		$this->styleSheet = $styleSheet;
		$this->installScript = $installScript;
		$this->uninstallScript = $uninstallScript;
		$this->updateScript = $updateScript;
		$this->includes = $includes;
		$this->headerStrings = $headerStrings;
	}

	function getId(){
		return $this->id;
	}

	function getName(){
		return $this->name;
	}

	function getVersion(){
		return $this->version;
	}

	function getDependencies(){
		return $this->dependencies;
	}

	function getPath(){
		return $this->path;
	}

	function getPages(){
		return $this->pages;
	}

	function getStyleSheet(){
		return $this->styleSheet;
	}

	function getInstallScript(){
		return $this->installScript;
	}

	function getUninstallScript(){
		return $this->uninstallScript;
	}

	function getUpdateScript(){
		return $this->updateScript;
	}

	function getIncludes(){
		return $this->includes;
	}

	function getHeaderStrings(){
		return $this->headerStrings;
	}
}
?>