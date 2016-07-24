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

namespace vcms;

use PDO;
use PDOException;

class LibDb{
	var $connection;

	function connect(){
		global $libConfig;

		$mysqlPort = 3306;

		if($libConfig->mysqlPort != ""){
			$mysqlPort = $libConfig->mysqlPort;

			if($libConfig->mysqlServer == 'localhost'){
				// required fix due to http://php.net/manual/de/pdo.connections.php
				$libConfig->mysqlServer = '127.0.0.1';
			}
		}

		$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8',
			$libConfig->mysqlServer,
			$mysqlPort,
			$libConfig->mysqlDb);

		$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");

		try {
			$this->connection = new PDO($dsn, $libConfig->mysqlUser, $libConfig->mysqlPass, $options);
		} catch (PDOException $e) {
			die('Error: the connection to the MySQL database could not be established. Probably the MySQL parameters in custom/systemconfig.php are invalid.');
		}
	}

	function lastInsertId(){
		return $this->connection->lastInsertId();
	}

	function prepare($stmt){
		return $this->connection->prepare($stmt);
	}

	function query($stmt){
		return $this->connection->query($stmt);
	}

	function updateRow($fieldsArray, $valueArray, $table, $idArray){
		global $libString;

		$setString = '';

		// build string of values to set
		foreach($fieldsArray as $feld){
			if($setString != ''){
				$setString .= ',';
			}

			$setString .= $feld.' = :'.$feld;
		}

		// build string of ids
		$idString = '';

		foreach($idArray as $key => $value){
			if($idString != ''){
				$idString .= ' AND ';
			}

			$idString .= $key.'=:id_'.$key;
		}

		// build update command
		$stmt = $this->prepare('UPDATE '.$table.' SET ' .$setString. ' WHERE '.$idString);

		// bind values
		foreach($fieldsArray as $feld){
			if(!isset($valueArray[$feld]) || $valueArray[$feld] == ''){
				$value = null;
			} else {
				$value = $libString->protectXSS($valueArray[$feld]);
			}

			$stmt->bindValue(':'.$feld, $value, $this->determinePdoType($value));
		}

		// bind ids
		foreach($idArray as $key => $value){
			$stmt->bindValue(':id_'.$key, $value, $this->determinePdoType($value));
		}

		// execute
		$stmt->execute();

		// fetch new data
		$stmt = $this->prepare('SELECT * FROM '.$table.' WHERE '.$idString);

		foreach($idArray as $key => $value){
			$stmt->bindValue(':id_'.$key, $value, $this->determinePdoType($value));
		}

		$stmt->execute();

		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	function insertRow($fieldsArray, $valueArray, $table, $idArray){
		global $libString;

		$felderString = implode(',', $fieldsArray);

		$werteString = '';

		// build string of values
		foreach($fieldsArray as $feld){
			if($werteString != ''){
				$werteString .= ', ';
			}

			$werteString .= ':'.$feld;
		}

		// build insert command
		$stmt = $this->prepare('INSERT INTO '.$table.' (' .$felderString. ') VALUES ('.$werteString.')');

		// bind values
		foreach($fieldsArray as $feld){
			if(!isset($valueArray[$feld]) || $valueArray[$feld] == ''){
				$value = null;
			} else {
				$value = $libString->protectXSS($valueArray[$feld]);
			}

			$stmt->bindValue(':'.$feld, $value, $this->determinePdoType($value));
		}

		// execute
		$stmt->execute();

		/*
		* fetch data
		*/

		$valueBased = true;

		//are there values for ids?
		foreach($idArray as $key => $value){
			if($value == ''){
				$valueBased = false;
			}
		}

		if($valueBased){
			$idString = '';

			foreach($idArray as $key => $value){
				if($idString != ''){
					$idString .= ' AND ';
				}

				$idString .= $key.'=:id_'.$key;
			}

			$stmt = $this->prepare('SELECT * FROM '.$table.' WHERE '.$idString);

			foreach($idArray as $key => $value){
				$stmt->bindValue(':id_'.$key, $value, $this->determinePdoType($value));
			}
		} else {
			$keys = array_keys($idArray);
			$lastInsertId = $this->lastInsertId();

			$stmt = $this->prepare('SELECT * FROM '.$table.' WHERE '.$keys[0].'=:lastinsert');
			$stmt->bindValue(':lastinsert', $lastInsertId, PDO::PARAM_INT);
		}

		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	function determinePdoType($value){
		if(is_null($value)){
			return PDO::PARAM_NULL;
		} elseif(is_int($value)){
			return PDO::PARAM_INT;
		} else {
			return PDO::PARAM_STR;
		}
	}

	function setErrModeWarning(){
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	}
}