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
	var $mitgliedid;
	var $titel;
	var $vorname;
	var $praefix;
	var $nachname;
	var $suffix;
	var $gruppe;

	function __construct($id, $mitgliedid){
		global $libDb;

		$this->id = $id;
		$this->mitgliedid = $mitgliedid;

		$stmt = $libDb->prepare('SELECT leibmitglied, titel, vorname, praefix, name, suffix, gruppe FROM base_person WHERE id=:id');
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		$this->leibvater = $row['leibmitglied'];
		$this->titel = $row['titel'];
		$this->vorname = $row['vorname'];
		$this->praefix = $row['praefix'];
		$this->nachname = $row['name'];
		$this->suffix = $row['suffix'];
		$this->gruppe = $row['gruppe'];
	}

	function searchFirstLeibvater(){
		if($this->leibvater != ''){
			$Leibvater = new LibGenealogyElement($this->leibvater, '');
			return $Leibvater->searchFirstLeibvater();
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

	function getString($tiefe){
		$retstr = '';

		for($i=0; $i < $tiefe-1; $i++){
			$retstr .= '&#124;&nbsp;&nbsp;';
		}

		if($tiefe > 0){
			$retstr .= '&#124;-';
		}

		$retstr .= '<a href="index.php?pid=intranet_person&amp;id=' .$this->id. '">';
		$retstr .= '<span style="';

		if($this->id == $this->mitgliedid){
			$retstr .= 'background-color:red;';
		}

		if($this->gruppe == 'B' || $this->gruppe == 'F'){
			$retstr .= 'color:#0000FF';
		} elseif($this->gruppe == 'P'){
			$retstr .= 'color:#000000';
		} elseif($this->gruppe == 'T'){
			$retstr .= 'color:#660000';
		} elseif($this->gruppe == 'X'){
			$retstr .= 'color:#C0C0C0';
		} else {
			$retstr .= 'color:#669933';
		}

		$retstr .= '">';

		if($this->titel != ''){
			$retstr .= $this->titel. ' ';
		}

		$retstr .= $this->vorname. ' ' .$this->praefix. ' ' .$this->nachname. ' ' .$this->suffix;

		$retstr .= '</span>';
		$retstr .= '</a>';
		$retstr .= '<br />';

		return $retstr;
	}
}
