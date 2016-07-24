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

class LibPage extends LibRestrictableElement{
	var $pid;
	var $file;
	var $directory;
	var $title;

	function __construct($pid, $directory, $file, $accessRestriction, $title = ''){
		parent::__construct($accessRestriction);

		$this->pid = $pid;
		$this->directory = $directory;
		$this->file = $file;

		if($title != ''){
			$this->title = $title;
		} else {
			$this->title = $pid;
		}
	}

	function setDirectory($directory){
		$this->directory = $directory;
	}

	function getPid(){
		return $this->pid;
	}

	function getFile(){
		return $this->file;
	}

	function getPath(){
		return $this->directory. '/' .$this->file;
	}

	function getDirectory(){
		return $this->directory;
	}

	function getTitle(){
		return $this->title;
	}
}