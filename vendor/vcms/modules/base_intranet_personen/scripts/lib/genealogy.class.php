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

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();

class StammbaumElement{
	var $leibvater;
	var $id;
	var $mitgliedid;
	var $titel;
	var $vorname;
	var $praefix;
	var $nachname;
	var $suffix;
	var $gruppe;

	function StammbaumElement($id, $mitgliedid){
		global $libDb;

		$this->id = $id;
		$this->mitgliedid = $mitgliedid;

		$stmt = $libDb->prepare("SELECT leibmitglied,titel,vorname,praefix,name,suffix,gruppe FROM base_person WHERE id=:id");
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		$this->leibvater = $row["leibmitglied"];
		$this->titel = $row["titel"];
		$this->vorname = $row["vorname"];
		$this->praefix = $row["praefix"];
		$this->nachname = $row["name"];
		$this->suffix = $row["suffix"];
		$this->gruppe = $row["gruppe"];
	}

	/**
	* sucht rekursiv den obersten Leibvater, für den kein weiterer Leibvater mehr angegeben ist, also die Wurzel des Baumes
	* @return Datensatzid des obersten Leibvaters
	*/
	function searchFirstLeibvater(){
		//wurde ein Leibvater für den Datensatz angegeben?
		if($this->leibvater != ""){
			//Leibvater in Objekt packen
			$Leibvater = new StammbaumElement($this->leibvater, "");
			return $Leibvater->searchFirstLeibvater();
		} else {
			return $this->id;
		}
	}

	/**
	* Gibt ein Array der Leibsöhne des Bb aus
	* @return Array mit den Datensatznummern der Leibsöhne
	*/
	function searchLeibSoehne(){
		global $libDb;

		$stmt = $libDb->prepare("SELECT id FROM base_person WHERE leibmitglied=:leibmitglied");
		$stmt->bindValue(':leibmitglied', $this->id, PDO::PARAM_INT);
		$stmt->execute();

		$leibsoehne = array();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$leibsoehne[] = $row["id"];
		}

		return $leibsoehne;
	}

	/**
	* Gibt die Daten zum Bb aus
	* @param tiefe Einrücktiefe
	* @return String der Daten zum Bb
	*/
	function getString($tiefe){
		$retstr = "";

		for($i=0; $i < $tiefe-1; $i++){
			$retstr .= "&#124;&nbsp;&nbsp;";
		}

		if($tiefe > 0){
			$retstr .= "&#124;-";
		}

		$retstr .= '<a href="index.php?pid=intranet_person_daten&amp;personid=' .$this->id. '">';
		$retstr .= '<span style="';

		if($this->id == $this->mitgliedid){
			$retstr .= 'background-color:red;';
		}

		if($this->gruppe == 'B' || $this->gruppe == 'F'){
			$retstr .= "color:#0000FF";
		} elseif($this->gruppe == 'P'){
			$retstr .= "color:#000000";
		} elseif($this->gruppe == 'T'){
			$retstr .= "color:#660000";
		} elseif($this->gruppe == 'X'){
			$retstr .= "color:#C0C0C0";
		} else {
			$retstr .= "color:#669933";
		}

		$retstr .= '">';

		if($this->titel != ''){
			$retstr .= $this->titel." ";
		}

		$retstr .= $this->vorname." ".$this->praefix." ".$this->nachname." ".$this->suffix;

		$retstr .= "</span>";
		$retstr .= "</a>";
		$retstr .= '<br />';

		return $retstr;
	}
}

class Stammbaum{
	var $retstr;

	function Stammbaum($root, $tiefe, $mitgliedid){
		$retstr = "";
		$stammbaumWurzel = new StammbaumElement($root, $mitgliedid); //Wurzel-Bb als Objekt anlegen
		$retstr .= $stammbaumWurzel->getString($tiefe);
		$leibsoehne = $stammbaumWurzel->searchLeibSoehne(); //Leibsöhne der Wurzel ermitteln

		//für alle Leibsöhne
		for($i=0; $i<count($leibsoehne); $i++){
			$stammbaum = new Stammbaum($leibsoehne[$i], $tiefe + 1, $mitgliedid);
			$retstr .= $stammbaum->getString();
		}

		$this->retstr = $retstr;
	}

	function getString(){
		return $this->retstr;
	}
}
?>