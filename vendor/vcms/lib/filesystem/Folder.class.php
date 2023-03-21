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

namespace vcms\filesystem;

class Folder extends FolderElement{
	var $isAmtsRootFolder = false;
	var $isOpen = false;
	var $nestedFolderElements = array();

	function __construct($nestingFolder, $name, $fileSystemFileName){
		parent::__construct($nestingFolder, $name, $fileSystemFileName);

		$this->type = 1;

		if(is_object($nestingFolder) && !is_object($nestingFolder->nestingFolder)){
			$this->isAmtsRootFolder = true;
		}

		if($this->isAmtsRootFolder){
			$this->owningAmt = $fileSystemFileName;
		} elseif(is_object($nestingFolder)){
			$this->owningAmt = $nestingFolder->owningAmt;
		}

		if(isset($_SESSION['openFolders']) && is_array($_SESSION['openFolders'])){
			if(isset($_SESSION['openFolders'][$this->getHash()]) && $_SESSION['openFolders'][$this->getHash()] == 1){
				$this->isOpen = true;
			}
		}

		$this->scanFileSystem();
	}

	function friend_addFolderElement($folderElement){
		$this->nestedFolderElements[$folderElement->getHash()] = $folderElement;
	}

	function friend_removeFolderElement($folderElement){
		foreach($this->nestedFolderElements as $key => $value){
			if ($value == $folderElement){
				unset($this->nestedFolderElements[$key]);
			}
		}
	}

	function getNestedFolderElements(){
		return $this->nestedFolderElements;
	}

	function getNestedFolderElementsRec(){
		$array = $this->getNestedFolderElements();

		foreach($this->getNestedFolderElements() as $folderElement){
			if($folderElement->type == 1){
				$array = array_merge($array, $folderElement->getNestedFolderElementsRec());
			}
		}

		return $array;
	}

	function getNestedFolders(){
		$array = array();

		foreach($this->getNestedFolderElements() as $folderElement){
			if($folderElement->type == 1){
				$array[] = $folderElement;
			}
		}

		return $array;
	}

	function getNestedFoldersRec(){
		$array = $this->getNestedFolders();

		foreach($this->getNestedFolders() as $folderElement){
			if($folderElement->type == 1){
				$array = array_merge($array, $folderElement->getNestedFoldersRec());
			}
		}

		return $array;
	}

	function getSize(){
		$size = 0;

		foreach($this->getNestedFolderElements() as $folderElement){
			$size = $size + $folderElement->getSize();
		}

		return $size;
	}

	function getHashMap(){
		$array = array();

		foreach($this->getNestedFolderElementsRec() as $folderElement){
			$array[$folderElement->getHash()] = $folderElement;
		}

		return $array;
	}

	function addFolder($folderName){
		$folderName = trim(preg_replace("/[^a-zA-Z0-9äöüÄÖÜß]/", ' ', $folderName));

		if($folderName != ''){
			@mkdir($this->getFileSystemPath() . '/' . $folderName);
			$folderElement = new Folder($this, $folderName, $folderName);
		}
	}

	function addFile($tmpFileSystemName, $name, $groupArray){
		$name = trim(preg_replace("/[^a-zA-Z0-9\s\.äöüÄÖÜß]/", ' ', $name));
		$name = preg_replace("/[\s]+/", ' ', $name);

		if(strlen($name) > 0){
			$metaFileSystemName = $this->getMetaFileSystemName($name, $groupArray);
			//copy($tmpFileSystemName, $this->getFileSystemPath() . '/' .$metaFileSystemName);
			move_uploaded_file($tmpFileSystemName, $this->getFileSystemPath(). '/' .$metaFileSystemName);
		}
	}

	function delete(){
		if($this->isDeleteable() && @rmdir($this->getFileSystemPath())){
			$this->nestingFolder->friend_removeFolderElement($this);
			$this->nestingFolder = '';
		}
	}

	function hasNestedFolderElements(){
		return count($this->nestedFolderElements) > 0;
	}

	function isAmtsRootFolder(){
		return $this->isAmtsRootFolder;
	}

	function isDeleteable(){
		if($this->isAmtsRootFolder){
			return false;
		}

		if($this->hasNestedFolderElements()){
			return false;
		}

		return true;
	}

	/*
	* initial helper
	*/

	function scanFileSystem(){
		$files = array_diff(scandir($this->getFileSystemPath()), array('.', '..'));

		$fileArray = array();

		foreach($files as $file){
			$fileArray[] = $file;
		}

		sort($fileArray);

		foreach($fileArray as $file){
			$folderElementString = $this->getFileSystemPath(). '/' .$file;

			if(is_file($folderElementString)){
				$folderElement = new File($this, $file);
			} elseif(is_dir($folderElementString)){
				$folderName = $file;
				$folderElement = new Folder($this, $folderName, $file);
			}
		}
	}
}
