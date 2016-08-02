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

class FolderElement{
	var $name;
	var $nestingFolder;
	var $owningAmt;
	var $type;
	var $fileSystemFileName;

	function __construct($nestingFolder, $name, $fileSystemFileName){
		$this->name = $name;
		$this->fileSystemFileName = $fileSystemFileName;
		$this->nestingFolder = $nestingFolder;

		if(is_object($nestingFolder)){
			$nestingFolder->friend_addFolderElement($this);
		}
	}

	function getFileSystemPath(){
		if(is_object($this->nestingFolder)){
			return $this->nestingFolder->getFileSystemPath(). '/' .$this->fileSystemFileName;
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
		$securityPrefix = implode('', array_unique($groupArray));

		$stringArray = array();
		$stringArray[] = $securityPrefix;
		$stringArray[] = $name;
		return implode('-', $stringArray);
	}
}