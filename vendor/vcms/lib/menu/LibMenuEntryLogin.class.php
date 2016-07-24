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

namespace vcms\menu;

class LibMenuEntryLogin extends LibMenuElement{
	var $nameLogout;

	function __construct($pid, $name, $nameLogout, $position){
		parent::__construct($pid, $name, $position, 4);

		$this->nameLogout = $nameLogout;
	}

	function copy(){
		$menuEntry = new LibMenuEntryLogin($this->pid, $this->name, $this->nameLogout, $this->position);
		$menuEntry->accessRestriction = $this->accessRestriction;
		$menuEntry->type = $this->type;
		$menuEntry->id = $this->id;
		return $menuEntry;
	}

	function getNameLogout(){
		return $this->nameLogout;
	}
}