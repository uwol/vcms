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

class LibDependency{
	var $moduleId;
	var $dependencyName;
	var $moduleVersion;
	var $dependencyType;

	function __construct($dependencyName, $moduleId, $moduleVersion, $dependencyType){
		if($dependencyName == ''){
			die('Fehler: Fehler bei Dependencyerstellung: Dependencyname fehlt.');
		}

		if($moduleId == ''){
			die('Fehler: Fehler bei Dependencyerstellung: Module-Id fehlt.');
		}

		if(!is_numeric($dependencyType)){
			die('Fehler: Fehler bei Dependencyerstellung: Dependencytyp keine Nummer.');
		}

		if(!is_numeric($moduleVersion)){
			die('Fehler: Fehler bei Dependencyerstellung: Modulversionsangabe keine Nummer.');
		}

		$this->dependencyName = $dependencyName;
		$this->moduleVersion = $moduleVersion;
		$this->dependencyType = $dependencyType;
		$this->moduleId = $moduleId;
	}

	function getDependencyName(){
		return $this->dependencyName;
	}

	function getModuleVersion(){
		return $this->moduleVersion;
	}

	function getDependencyType(){
		return $this->dependencyType;
	}

	function getModuleId(){
		return $this->moduleId;
	}
}

class LibMinDependency extends LibDependency{
	function __construct($dependencyName, $moduleId, $moduleVersion){
		parent::__construct($dependencyName, $moduleId, $moduleVersion, 1);
	}
}

class LibExactDependency extends LibDependency{
	function __construct($dependencyName, $moduleId, $moduleVersion){
		parent::__construct($dependencyName, $moduleId, $moduleVersion, 2);
	}
}
?>