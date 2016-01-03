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

class FolderElement{
	var $name;
	var $nestingFolder;
	var $owningAmt;
	var $type;
	var $fileSystemFileName;

	function FolderElement($nestingFolder, $name, $fileSystemFileName){
		$this->name = $name;
		$this->fileSystemFileName = $fileSystemFileName;
		$this->nestingFolder = $nestingFolder;

		if(is_object($nestingFolder)){
			$nestingFolder->friend_addFolderElement($this);
		}
	}

	function getFileSystemPath(){
		if(is_object($this->nestingFolder)){
			return $this->nestingFolder->getFileSystemPath() . '/' . $this->fileSystemFileName;
		} else {
			return $this->fileSystemFileName;
		}
	}

	function getHash(){
		return sha1($this->getFileSystemPath());
	}

	function getFileMetaInfos($fileSystemFileName){
		$retArray = array();

		$parts = explode('-', $fileSystemFileName);

		//group prefix
		if(count($parts) > 1){
			$groupPrefix = $parts[0];
		} else {
			$groupPrefix = '';
		}

		$retArray['readgroups'] = array();

		for($i=0; $i<strlen($groupPrefix); $i++){
			$group = $groupPrefix[$i];
			$retArray['readgroups'][] = $group;
		}

		sort($retArray['readgroups']);

		//filename
		$fileNameParts = array();

		for($i=1; $i<count($parts); $i++){
			$fileNameParts[] = $parts[$i];
		}

		$retArray['name'] = implode('-', $fileNameParts);

		return $retArray;
	}

	function getMetaFileSystemName($name, $groupArray){
		$securityPrefix = implode("", array_unique($groupArray));

		$stringArray = array();
		$stringArray[] = $securityPrefix;
		$stringArray[] = $name;
		return implode('-', $stringArray);
	}
}



class Folder extends FolderElement{
	var $isAmtsRootFolder = false;
	var $isOpen = false;
	var $nestedFolderElements = array();

	function Folder($nestingFolder, $name, $fileSystemFileName){
		parent::FolderElement($nestingFolder, $name, $fileSystemFileName);

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

		if(count($name) > 0){
			$metaFileSystemName = $this->getMetaFileSystemName($name, $groupArray);
			//copy($tmpFileSystemName, $this->getFileSystemPath() . '/' .$metaFileSystemName);
			move_uploaded_file($tmpFileSystemName, $this->getFileSystemPath() . '/' .$metaFileSystemName);
		}
	}

	function delete(){
		if($this->isDeleteAble() && @rmdir($this->getFileSystemPath())){
			$this->nestingFolder->friend_removeFolderElement($this);
			$this->nestingFolder = '';
		}
	}

	function isDeleteAble(){
		if($this->isAmtsRootFolder){
			return false;
		}

		if(count($this->nestedFolderElements) > 0){
			return false;
		}

		return true;
	}

	/*
	* initial helper
	*/

	function scanFileSystem(){
		$fd = opendir($this->getFileSystemPath());

		$partArray = array();

		while (($part = readdir($fd)) == true){
			$partArray[] = $part;
		}

		sort($partArray);

		foreach($partArray as $part){
			if($part != "." && $part != ".."){
				$folderElementString = $this->getFileSystemPath() ."/". $part;

				if(is_file($folderElementString)){
					$folderElement = new File($this, $part);
				} elseif(is_dir($folderElementString)){
					$folderName = $part;
					$folderElement = new Folder($this, $folderName, $part);
				}
			}
		}
	}
}



class File extends FolderElement{
	var $size;
	var $readGroups = array();

	function File($nestingFolder, $fileSystemFileName){
		$metaInfos = $this->getFileMetaInfos($fileSystemFileName);
		$name = $metaInfos['name'];
		$this->readGroups = $metaInfos['readgroups'];

		parent::FolderElement($nestingFolder, $name, $fileSystemFileName);

		$this->type = 2;
		$this->size = filesize($this->getFileSystemPath());

		$this->owningAmt = $nestingFolder->owningAmt;
	}

	function getExtension(){
		$path_parts = pathinfo($this->name);

		if(isset($path_parts["extension"])){
			return strtolower($path_parts["extension"]);
		}
	}

	function getFilename(){
		$path_parts = pathinfo($this->name);

		if(isset($path_parts["filename"])){
			return $path_parts["filename"];
		}
	}

	function delete(){
		unlink($this->getFileSystemPath());

		$this->nestingFolder->friend_removeFolderElement($this);
		$this->nestingFolder = '';
	}

	function getSize(){
		return $this->size;
	}
}
?>