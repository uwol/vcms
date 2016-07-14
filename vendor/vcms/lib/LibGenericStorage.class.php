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

namespace vcms;

use PDO;

class LibGenericStorage{

	/*
	* scalar attributes/values
	*/

	//load
	function loadValue($moduleId, $attributeName){
		return $this->loadArrayValue($moduleId, $attributeName, 0);
	}

	function loadValueInCurrentModule($attributeName){
		return $this->loadArrayValue($this->getCurrentModuleId(), $attributeName, 0);
	}

	//save
	function saveValue($moduleId, $attributeName, $value){
		return $this->saveArrayValue($moduleId, $attributeName, 0, $value);
	}

	function saveValueInCurrentModule($attributeName, $value){
		return $this->saveArrayValue($this->getCurrentModuleId(), $attributeName, 0, $value);
	}

	//delete
	function deleteAttribute($moduleId, $attributeName){
		return $this->deleteArray($moduleId, $attributeName);
	}

	function deleteAttributeInCurrentModule($attributeName){
		return $this->deleteArray($this->getCurrentModuleId(), $attributeName);
	}

	/*
	* arrays
	*/

	//list
	function listAllArrayValues(){
		global $libDb;

		$result = $libDb->query('SELECT moduleid, array_name, value, position FROM sys_genericstorage');

		$array = array();

		foreach($result as $row){
			$array[$row['moduleid']][$row['array_name']][$row['position']] = $row['value'];

			ksort($array[$row['moduleid']][$row['array_name']]);
			ksort($array[$row['moduleid']]);
		}

		ksort($array);

		return $array;
	}

	function attributeExists($moduleId, $arrayName){
		global $libDb;

		$stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM sys_genericstorage WHERE moduleid=:moduleid AND array_name=:array_name');
		$stmt->bindValue(':moduleid', $moduleId);
		$stmt->bindValue(':array_name', $arrayName);
		$stmt->execute();
		$stmt->bindColumn('number', $anzahl);
		$stmt->fetch();

		if($anzahl > 0){
			return true;
		} else {
			return false;
		}
	}

	function attributeExistsInCurrentModule($arrayName){
		return $this->attributeExists($this->getCurrentModuleId(), $arrayName);
	}

	//load
	function loadArrayValue($moduleId, $arrayName, $position){
		global $libDb;

		$stmt = $libDb->prepare('SELECT value FROM sys_genericstorage WHERE moduleid=:moduleid AND array_name=:array_name AND position=:position');
		$stmt->bindValue(':moduleid', $moduleId);
		$stmt->bindValue(':array_name', $arrayName);
		$stmt->bindValue(':position', $position, PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('value', $value);
		$stmt->fetch();

		return $value;
	}

	function loadArrayValueInCurrentModule($arrayName, $position){
		return $this->loadArrayValue($this->getCurrentModuleId(), $arrayName, $position);
	}

	function loadArray($moduleId, $arrayName){
		global $libDb;

		$stmt = $libDb->prepare('SELECT value, position FROM sys_genericstorage WHERE moduleid=:moduleid AND array_name=:array_name');
		$stmt->bindValue(':moduleid', $moduleId);
		$stmt->bindValue(':array_name', $arrayName);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		$array = array();

		foreach($result as $row){
			$array[$row['position']] = $row['value'];
		}

		ksort($array);

		return $array;
	}

	function loadArrayInCurrentModule($arrayName){
		return $this->loadArray($this->getCurrentModuleId(), $arrayName);
	}

	function loadArraysOfModule($moduleId){
		global $libDb;

		$stmt = $libDb->prepare('SELECT array_name, position, value FROM sys_genericstorage WHERE moduleid=:moduleid');
		$stmt->bindValue(':moduleid', $moduleId);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		$moduleArrays = array();

		foreach($result as $row){
			$moduleArrays[$row['array_name']][$row['position']] = $row['value'];
		}

		ksort($moduleArrays);

		return $moduleArrays;
	}

	function loadArraysOfModuleInCurrentModule(){
		return loadArraysOfModule($this->getCurrentModuleId());
	}

	//save
	function saveArrayValue($moduleId, $arrayName, $position, $value){
		global $libDb, $libString;

		$stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM sys_genericstorage WHERE moduleid=:moduleid AND array_name=:array_name AND position=:position');
		$stmt->bindValue(':moduleid', $moduleId);
		$stmt->bindValue(':array_name', $arrayName);
		$stmt->bindValue(':position', $position, PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('number', $anzahl);
		$stmt->fetch();

		if($anzahl > 0){
			$stmt = $libDb->prepare('UPDATE sys_genericstorage SET value = :value WHERE moduleid=:moduleid AND array_name=:array_name AND position=:position');
			$stmt->bindValue(':value', $libString->protectXss($value));
			$stmt->bindValue(':moduleid', $moduleId);
			$stmt->bindValue(':array_name', $arrayName);
			$stmt->bindValue(':position', $position, PDO::PARAM_INT);
			$stmt->execute();
		} else {
			$stmt = $libDb->prepare('INSERT INTO sys_genericstorage (moduleid, array_name, position, value) VALUES (:moduleid, :array_name, :position, :value)');
			$stmt->bindValue(':value', $libString->protectXss($value));
			$stmt->bindValue(':moduleid', $libString->protectXss($moduleId));
			$stmt->bindValue(':array_name', $libString->protectXss($arrayName));
			$stmt->bindValue(':position', $libString->protectXss($position), PDO::PARAM_INT);
			$stmt->execute();
		}
	}

	function saveArrayValueInCurrentModule($arrayName, $position, $value){
		return $this->saveArrayValue($this->getCurrentModuleId(), $arrayName, $position, $value);
	}

	//delete
	function deleteArrayValue($moduleId, $arrayName, $position){
		global $libDb;

		$stmt = $libDb->prepare('DELETE FROM sys_genericstorage WHERE moduleid=:moduleid AND array_name=:array_name AND position=:position');
		$stmt->bindValue(':moduleid', $moduleId);
		$stmt->bindValue(':array_name', $arrayName);
		$stmt->bindValue(':position', $position, PDO::PARAM_INT);
		$stmt->execute();
	}

	function deleteArrayValueInCurrentModule($arrayName, $position){
		return $this->deleteArrayValue($this->getCurrentModuleId(), $arrayName, $position);
	}

	function deleteArray($moduleId, $arrayName){
		global $libDb;

		$stmt = $libDb->prepare('DELETE FROM sys_genericstorage WHERE moduleid=:moduleid AND array_name=:array_name');
		$stmt->bindValue(':moduleid', $moduleId);
		$stmt->bindValue(':array_name', $arrayName);
		$stmt->execute();
	}

	function deleteArrayInCurrentModule($arrayName){
		return $this->deleteArray($this->getCurrentModuleId(), $arrayName);
	}

	/*
	* helper
	*/
	function getCurrentModuleId(){
		global $libGlobal;

		return $libGlobal->module->getId();
	}
}