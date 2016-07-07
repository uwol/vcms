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

namespace vcms\menu;

class LibMenuFolder extends LibMenuElement{
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

	function setAccessRestrictionOfMenuElement($mid, $accessRestriction){
		//no foreach, as otherwise php4 does copy-by-value
		for($i=0; $i<count($this->elements); $i++){
			$element = $this->elements[$i];

			if($element->getId() == $mid){
				$element->setAccessRestriction($accessRestriction);
			}
		}
	}

	function copy(){
		$menuFolder	= new LibMenuFolder($this->pid, $this->name, $this->position);
		$menuFolder->id = $this->id;
		$menuFolder->type = $this->type;
		$menuFolder->accessRestriction = $this->accessRestriction;

		//no foreach, as otherwise php4 does copy-by-value
		for($i=0; $i<count($this->elements); $i++){
			$element = $this->elements[$i];
			$menuFolder->elements[] = $element->copy();
		}

		return $menuFolder;
	}
}