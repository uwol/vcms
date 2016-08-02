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

class File extends FolderElement{
	var $size;
	var $readGroups = array();

	function __construct($nestingFolder, $fileSystemFileName){
		$metaInfos = $this->getFileMetaInfos($fileSystemFileName);
		$name = $metaInfos['name'];
		$this->readGroups = $metaInfos['readgroups'];

		parent::__construct($nestingFolder, $name, $fileSystemFileName);

		$this->type = 2;
		$this->size = filesize($this->getFileSystemPath());

		$this->owningAmt = $nestingFolder->owningAmt;
	}

	function getExtension(){
		$path_parts = pathinfo($this->name);

		if(isset($path_parts['extension'])){
			return strtolower($path_parts['extension']);
		}
	}

	function getFilename(){
		$path_parts = pathinfo($this->name);

		if(isset($path_parts['filename'])){
			return $path_parts['filename'];
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