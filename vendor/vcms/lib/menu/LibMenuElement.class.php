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