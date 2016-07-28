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
	var $directory;
	var $containerEnabled;
	var $file;
	var $pid;
	var $title;

	function __construct($pid, $directory, $file, $accessRestriction, $title, $containerEnabled){
		parent::__construct($accessRestriction);

		$this->containerEnabled = $containerEnabled;
		$this->directory = $directory;
		$this->file = $file;
		$this->pid = $pid;
		$this->title = $title;
	}

	function isContainerEnabled(){
		return $this->containerEnabled;
	}

	function getDirectory(){
		return $this->directory;
	}

	function getFile(){
		return $this->file;
	}

	function getPath(){
		return $this->directory. '/' .$this->file;
	}

	function getPid(){
		return $this->pid;
	}

	function getTitle(){
		return $this->title;
	}

	function setDirectory($directory){
		$this->directory = $directory;
	}
}