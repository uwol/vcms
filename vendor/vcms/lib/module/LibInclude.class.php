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

class LibInclude extends LibRestrictableElement{

	var $iid;
	var $file;
	var $directory;

	function __construct($iid, $directory, $file, $accessRestriction){
		parent::__construct($accessRestriction);

		$this->iid = $iid;
		$this->directory = $directory;
		$this->file = $file;
	}

	function setDirectory($directory){
		$this->directory = $directory;
	}

	function getIid(){
		return $this->iid;
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
}