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

namespace vcms\genealogy;

use PDO;

class LibGenealogyElement{
	var $leibvater;
	var $id;
	var $memberId;
	var $title;
	var $firstName;
	var $prefix;
	var $lastName;
	var $suffix;
	var $group;

	function __construct($id, $memberId){
		global $libDb;

		$this->id = $id;
		$this->memberId = $memberId;

		$stmt = $libDb->prepare('SELECT leibmitglied, titel, vorname, praefix, name, suffix, gruppe FROM base_person WHERE id=:id');
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if(!is_array($row)){
			return;
		}

		$this->leibvater = $row['leibmitglied'];
		$this->title = $row['titel'];
		$this->firstName = $row['vorname'];
		$this->prefix = $row['praefix'];
		$this->lastName = $row['name'];
		$this->suffix = $row['suffix'];
		$this->group = $row['gruppe'];
	}

	function searchFirstLeibvater(){
		if($this->leibvater != ''){
			$leibvaterElement = new LibGenealogyElement($this->leibvater, '');
			return $leibvaterElement->searchFirstLeibvater();
		} else {
			return $this->id;
		}
	}

	function searchLeibSoehne(){
		global $libDb;

		$stmt = $libDb->prepare('SELECT id FROM base_person WHERE leibmitglied=:leibmitglied');
		$stmt->bindValue(':leibmitglied', $this->id, PDO::PARAM_INT);
		$stmt->execute();

		$leibsoehne = array();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$leibsoehne[] = $row['id'];
		}

		return $leibsoehne;
	}

	function getString($depth){
		$retstr = '';

		for($i=0; $i < $depth-1; $i++){
			$retstr .= '&#124;&nbsp;&nbsp;';
		}

		if($depth > 0){
			$retstr .= '&#124;-';
		}

		$retstr .= '<a href="index.php?pid=intranet_person&amp;id=' .$this->id. '">';
		$retstr .= '<span style="';

		if($this->id == $this->memberId){
			$retstr .= 'background-color:red;';
		}

		if($this->group == 'B' || $this->group == 'F'){
			$retstr .= 'color:#0000FF';
		} elseif($this->group == 'P'){
			$retstr .= 'color:#000000';
		} elseif($this->group == 'T'){
			$retstr .= 'color:#660000';
		} elseif($this->group == 'X'){
			$retstr .= 'color:#C0C0C0';
		} else {
			$retstr .= 'color:#669933';
		}

		$retstr .= '">';

		if($this->title != ''){
			$retstr .= $this->title. ' ';
		}

		$retstr .= $this->firstName. ' ' .$this->prefix. ' ' .$this->lastName. ' ' .$this->suffix;

		$retstr .= '</span>';
		$retstr .= '</a>';
		$retstr .= '<br />';

		return $retstr;
	}
}
