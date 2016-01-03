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

class LibMenu{
	var $upperMenuFolder;
	var $menuIntranetFolderId;

	function __construct(){
		$this->upperMenuFolder = new LibMenuFolder('', '', 0);
		$this->upperMenuFolder->setAlwaysOpen(true);
	}

	function addMenuElement($menuElement){
		$this->upperMenuFolder->addElement($menuElement);
	}

	function addMenuIntranetFolder($menuIntranetFolder){
		$this->upperMenuFolder->addElement($menuIntranetFolder);
		$this->menuIntranetFolderId = $menuIntranetFolder->getId();
	}

	function applyMinAccessRestriction(){
		$this->upperMenuFolder->applyMinAccessRestriction();
		//public main folder
		$this->upperMenuFolder->setAccessRestriction('');
		//private intranet folder
		$this->upperMenuFolder->setAccessRestrictionOfMenuElement($this->menuIntranetFolderId, '');
	}

	function &copy(){
		$menu = new LibMenu();
		$menu->setUpperMenuFolder($this->upperMenuFolder->copy());
		return $menu;
	}

	function reduceByAccessRestriction($gruppe, $aemter){
		$this->upperMenuFolder->reduceByAccessRestriction($gruppe, $aemter);
	}

	function sortElementsByPosition(){
		$this->upperMenuFolder->sortElementsByPosition();
	}

	function configure($menuConfig){
		$this->upperMenuFolder->configure($menuConfig);
	}

	function getUpperMenuFolder(){
		return $this->upperMenuFolder;
	}

	function setUpperMenuFolder($upperMenuFolder){
		$this->upperMenuFolder = $upperMenuFolder;
	}
}


class LibMenuElement{
	var $id;
	var $name;
	var $pid;
	var $position;
	var $type;
	var $accessRestriction;

	function __construct($pid, $name, $position, $type){
		$this->name = $name;
		$this->position = $position;
		$this->type = $type;
		$this->pid = $pid;
		$this->id = substr(sha1($name.$position.$type.$pid), 0, 8);
	}

	function setAccessRestriction($accessRestriction){
		$this->accessRestriction = $accessRestriction;
	}

	function hasAccessRestriction(){
		if(is_object($this->accessRestriction)){
			return true;
		} else {
			return false;
		}
	}

	function getId(){
		return $this->id;
	}

	function getPid(){
		return $this->pid;
	}

	function getName(){
		return $this->name;
	}

	function getPosition(){
		return $this->position;
	}

	function getType(){
		return $this->type;
	}

	function getAccessRestriction(){
		return $this->accessRestriction;
	}
}


class LibMenuEntry extends LibMenuElement{
	function __construct($pid, $name, $position){
		parent::__construct($pid, $name, $position, 1);
	}

	function copy(){
		$menuEntry = new LibMenuEntry($this->pid, $this->name, $this->position);
		$menuEntry->accessRestriction = $this->accessRestriction;
		$menuEntry->type = $this->type;
		$menuEntry->id = $this->id;
		return $menuEntry;
	}
}


class LibMenuEntryExternalLink extends LibMenuElement{
	function __construct($pid, $name, $position){
		parent::__construct($pid, $name, $position, 3);
	}

	function copy(){
		$menuEntryExternalLink = new LibMenuEntryExternalLink($this->pid, $this->name, $this->position);
		$menuEntryExternalLink->accessRestriction = $this->accessRestriction;
		$menuEntryExternalLink->type = $this->type;
		$menuEntryExternalLink->id = $this->id;
		return $menuEntryExternalLink;
	}
}


class LibMenuFolder extends LibMenuElement{
	var $open = false;
	var $alwaysOpen = false;
	var $elements = array();

	function __construct($pid, $name, $position){
		parent::__construct($pid, $name, $position, 2);
	}

	function addElement($element){
		$this->elements[] = $element;
		$this->id = substr(sha1($element->getId().$this->id), 0, 8);
	}

	function setElements($menuElements){
		$this->elements = $menuElements;
	}

	function &getElements(){
		return $this->elements;
	}

	function open(){
		$this->open = true;
	}

	function setAlwaysOpen($boolean){
		$this->alwaysOpen = $boolean;
	}

	function close(){
		$this->open = false;
	}

	function invertOpen(){
		if($this->open){
			$this->open = false;
		} else {
			$this->open = true;
		}
	}

	function isOpen(){
		return $this->open;
	}

	function isAlwaysOpen(){
		return $this->alwaysOpen;
	}

	function hasElements(){
		return count($this->elements) > 0;
	}

	function sortElementsByPosition(){
		$elementsNew = array();

		$minPositionValue = -1;
		$minPosition = -1;

		$temp = array_values($this->elements);

		while(count($temp) > 0){
			for($i=0; $i<count($temp); $i++){
				$position = $temp[$i]->getPosition();

				if($minPositionValue == -1 || $position < $minPositionValue){
					$minPositionValue = $position;
					$minPosition = $i;
				}
			}

			$elementsNew[] = $temp[$minPosition];

			//clean up
			unset($temp[$minPosition]);
			$minPosition = -1;
			$minPositionValue = -1;

			//build temp array for correct indices
			$temp = array_values($temp);
		}

		$this->elements = $elementsNew;

		//no foreach, as otherwise php4 does copy-by-value
		for($i=0; $i<count($this->elements); $i++){
			$element = $this->elements[$i];

			if($element->getType() == 2){
				$element->sortElementsByPosition();
			}
		}
	}

	function reduceByAccessRestriction($gruppe, $aemter){
		//no foreach, as otherwise php4 does copy-by-value
		for($i=0; $i<count($this->elements); $i++){
			$element = $this->elements[$i];

			//menu folder?
			if($element->getType() == 2){
				$element->reduceByAccessRestriction($gruppe, $aemter);
			}
		}

		$elementsNew = array();

		//no foreach, as otherwise php4 does copy-by-value
		for($i=0; $i<count($this->elements); $i++){
			$element = $this->elements[$i];

			//menu entry?
			if(!$element->hasAccessRestriction()){
				$elementsNew[] = $element;
			} else {
				$accessRestriction = $element->getAccessRestriction();

				//restrict
				if($accessRestriction->isFulfilledBy($gruppe, $aemter)){
					$elementsNew[] = $element;
				}
			}
		}

		$this->elements = $elementsNew;
	}

	function applyMinAccessRestriction(){
		global $libSecurityManager;

		//no foreach, as otherwise php4 does copy-by-value
		//apply min access restriction recursively
		for($i=0; $i<count($this->elements); $i++){
			$element = $this->elements[$i];

			//a folder?
			if($element->getType() == 2){
				//without access restriction?
				if(!$element->hasAccessRestriction()){
					//generate access restriction
					$element->applyMinAccessRestriction();
				}
			}
		}

		$accessRestrictions = array();
		//no foreach, as otherwise php4 does copy-by-value
		//collect access restrictions of a folder
		for($i=0; $i<count($this->elements); $i++){
			$element = $this->elements[$i];

			//element with access restriction?
			if($element->hasAccessRestriction()){
				$accessRestrictions[] = $element->getAccessRestriction();
			}
		}

		//aggregate access restrictions
		$accessRestriction = $libSecurityManager->
			generateAggregatedAccessRestriction($accessRestrictions);

		if($accessRestriction->hasGruppenRestriction() ||
			$accessRestriction->hasAemterRestriction()){
			$this->accessRestriction = $accessRestriction;
		}
	}

	function configure($menuConfig){
		//no foreach, as otherwise php4 does copy-by-value
		for($i=0; $i<count($this->elements); $i++){
			if(isset($this->elements[$i])){
				$element = $this->elements[$i];

				//a menu folder?
				if($element->getType() == 2){
					$element->configure($menuConfig);

					if(isset($menuConfig[$element->getId()]) && $menuConfig[$element->getId()] == 1){
						$element->open();
					} else { //close all folders
						$element->close();
					}
				}
			}
		}
	}

	function setAccessRestrictionOfMenuElement($mid, $accessRestriction){
		//no foreach, as otherwise php4 does copy-by-value
		for($i=0; $i<count($this->elements); $i++){
			$element = $this->elements[$i];

			if($element->getId() == $mid){
				$element->setAccessRestriction('');
			}
		}
	}

	function copy(){
		$menuFolder	= new LibMenuFolder($this->pid, $this->name, $this->position);
		$menuFolder->id = $this->id;
		$menuFolder->type = $this->type;
		$menuFolder->accessRestriction = $this->accessRestriction;
		$menuFolder->open = $this->open;
		$menuFolder->alwaysOpen = $this->alwaysOpen;

		//no foreach, as otherwise php4 does copy-by-value
		for($i=0; $i<count($this->elements); $i++){
			$element = $this->elements[$i];
			$menuFolder->elements[] = $element->copy();
		}

		return $menuFolder;
	}
}
?>