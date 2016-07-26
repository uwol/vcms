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

namespace vcms\menu;

class LibMenu{
	var $rootMenuFolder;

	function __construct(){
		$this->rootMenuFolder = new LibMenuFolder('', '', 0);
	}

	function addMenuElement($menuElement){
		$this->rootMenuFolder->addElement($menuElement);
	}

	function applyMinAccessRestriction(){
		$this->rootMenuFolder->applyMinAccessRestriction();
		//public main folder
		$this->rootMenuFolder->setAccessRestriction('');
	}

	function copy(){
		$menu = new LibMenu();
		$menu->setRootMenuFolder($this->rootMenuFolder->copy());
		return $menu;
	}

	function canonizeElements(){
		$this->rootMenuFolder->canonizeElements();
	}

	function reduceByAccessRestriction($gruppe, $aemter){
		$this->rootMenuFolder->reduceByAccessRestriction($gruppe, $aemter);
	}

	function sortElementsByPosition(){
		$this->rootMenuFolder->sortElementsByPosition();
	}

	function getRootMenuFolder(){
		return $this->rootMenuFolder;
	}

	function setRootMenuFolder($rootMenuFolder){
		$this->rootMenuFolder = $rootMenuFolder;
	}
}