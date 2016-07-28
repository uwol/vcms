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

namespace vcms\module;

class LibModule{
	var $id;
	var $version;
	var $name;
	var $path;
	var $pages;
	var $includes;
	var $headerStrings;
	var $installScript;
	var $uninstallScript;
	var $updateScript;
	var $menuElementsInternet;
	var $menuElementsIntranet;
	var $menuElementsAdministration;

	function __construct($id, $name, $version, $path, $pages, $includes, $headerStrings,
			$installScript, $uninstallScript, $updateScript,
			$menuElementsInternet, $menuElementsIntranet, $menuElementsAdministration){
		global $libGlobal;

		if($id == ''){
			$libGlobal->errorTexts[] = 'Fehlende Module-Id';
		}

		if($version != '' && !is_numeric($version)){
			$libGlobal->errorTexts[] = 'Versionsangabe nicht numerisch';
		}

		if($name == ''){
			$libGlobal->errorTexts[] = 'Fehlende Namensangabe';
		}

		if($path == ''){
			$libGlobal->errorTexts[] = 'Fehlender Modulpfad';
		}

		if(!is_array($pages)){
			$libGlobal->errorTexts[] = 'Fehlendes Array pages';
		}

		if(!is_array($includes)){
			$libGlobal->errorTexts[] = 'Fehlendes Array includes';
		}

		$this->id = $id;
		$this->version = $version;
		$this->name = $name;
		$this->path = $path;
		$this->pages = $pages;
		$this->includes = $includes;

		$this->installScript = $installScript;
		$this->uninstallScript = $uninstallScript;
		$this->updateScript = $updateScript;

		$this->menuElementsInternet = $menuElementsInternet;
		$this->menuElementsIntranet = $menuElementsIntranet;
		$this->menuElementsAdministration = $menuElementsAdministration;

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

	function getPath(){
		return $this->path;
	}

	function getPages(){
		return $this->pages;
	}

	function getIncludes(){
		return $this->includes;
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

	function getMenuElementsInternet(){
		return $this->menuElementsInternet;
	}

	function getMenuElementsIntranet(){
		return $this->menuElementsIntranet;
	}

	function getMenuElementsAdministration(){
		return $this->menuElementsAdministration;
	}

	function getHeaderStrings(){
		return $this->headerStrings;
	}
}