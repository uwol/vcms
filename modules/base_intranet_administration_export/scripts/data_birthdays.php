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

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();

if($libAuth->isLoggedin()){
	if(!is_numeric($_GET['jahr']))
		die('Das angegebene Jahr ist keine Zahl.');

	$stmt = $libDb->prepare("SELECT * FROM base_person WHERE (gruppe = 'P' OR gruppe = 'B' OR gruppe = 'F' OR gruppe = 'C' OR gruppe = 'W' OR gruppe = 'G') AND datum_geburtstag != '' AND datum_geburtstag IS NOT NULL AND datum_geburtstag != '0000-00-00' ORDER BY DATE_FORMAT(datum_geburtstag, '%m%d')");

	$table = new LibTable($libDb);
	$table->addHeader(array("datum_geburtstag", "alter", "anrede", "rang", "titel", "vorname", "praefix", "name", "suffix", "zusatz1", "strasse1", "ort1", "plz1", "land1", "telefon1", "email", "status", "gruppe"));

	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		$table->addRowByArray(array($row["datum_geburtstag"], $_GET['jahr']-$row["datum_geburtstag"], $row["anrede"], $row["rang"], $row["titel"], $row["vorname"], $row["praefix"], $row["name"], $row["suffix"], $row["zusatz1"], $row["strasse1"], $row["ort1"], $row["plz1"], $row["land1"], $row["telefon1"], $row["email"], $row["status"], $row["gruppe"]));
	}

	if(isset($_GET['type']) && $_GET['type'] == "csv"){
		$table->writeContentAsCSV('geburtstage' .$_GET['jahr']. '.csv');
	} else {
		$table->writeContentAsHtmlTable('geburtstage' .$_GET['jahr']. '.html');
	}
}
?>