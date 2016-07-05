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
	$sql = '';
	$header = '';

	if($_GET['datenart'] == 'mitglieder_anschreiben'){
		$sql = "SELECT anrede, titel, rang, vorname, praefix, name, suffix, zusatz1, strasse1, ort1, plz1, land1, datum_adresse1_stand, zusatz2, strasse2, ort2, plz2, land2, datum_adresse2_stand, email, gruppe, status, base_region1.bezeichnung, base_region2.bezeichnung FROM base_person LEFT JOIN base_region AS base_region1 ON base_region1.id = base_person.region1 LEFT JOIN base_region AS base_region2 ON base_region2.id = base_person.region2 WHERE (gruppe = 'F' OR gruppe = 'B' OR gruppe = 'P') AND anschreiben_zusenden=1 ORDER BY plz1, name";
		$header = array('anrede', 'titel', 'rang', 'vorname', 'praefix', 'name', 'suffix', 'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1', 'stand1', 'zusatz2', 'strasse2', 'ort2', 'plz2', 'land2', 'stand2', 'email', 'gruppe', 'status', 'region1', 'region2');
	} elseif($_GET['datenart'] == 'mitglieder_spendenquittung'){
		$sql = "SELECT anrede, titel, rang, vorname, praefix, name, suffix, zusatz1, strasse1, ort1, plz1, land1, datum_adresse1_stand, zusatz2, strasse2, ort2, plz2, land2, datum_adresse2_stand, email, gruppe, status, base_region1.bezeichnung, base_region2.bezeichnung FROM base_person LEFT JOIN base_region AS base_region1 ON base_region1.id = base_person.region1 LEFT JOIN base_region AS base_region2 ON base_region2.id = base_person.region2 WHERE (gruppe = 'F' OR gruppe = 'B' OR gruppe = 'P') AND spendenquittung_zusenden=1 ORDER BY plz1, name";
		$header = array('anrede', 'titel', 'rang', 'vorname', 'praefix', 'name', 'suffix', 'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1', 'stand1', 'zusatz2', 'strasse2', 'ort2', 'plz2', 'land2', 'stand2', 'email', 'gruppe', 'status', 'region1', 'region2');
	} elseif($_GET['datenart'] == 'damenflor_anschreiben'){
		$sql = "SELECT anrede, titel, rang, vorname, praefix, name, suffix, zusatz1, strasse1, ort1, plz1, land1, datum_adresse1_stand, zusatz2, strasse2, ort2, plz2, land2, datum_adresse2_stand, email, gruppe, status, base_region1.bezeichnung, base_region2.bezeichnung FROM base_person LEFT JOIN base_region AS base_region1 ON base_region1.id = base_person.region1 LEFT JOIN base_region AS base_region2 ON base_region2.id = base_person.region2 WHERE (gruppe = 'C' OR gruppe = 'G' OR gruppe = 'W') AND anschreiben_zusenden=1 ORDER BY plz1, name";
		$header = array('anrede', 'titel', 'rang', 'vorname', 'praefix', 'name', 'suffix', 'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1', 'stand1', 'zusatz2', 'strasse2', 'ort2', 'plz2', 'land2', 'stand2', 'email', 'gruppe', 'status', 'region1', 'region2');
	} elseif($_GET['datenart'] == 'damenflor_spendenquittung'){
		$sql = "SELECT anrede, titel, rang, vorname, praefix, name, suffix, zusatz1, strasse1, ort1, plz1, land1, datum_adresse1_stand, zusatz2, strasse2, ort2, plz2, land2, datum_adresse2_stand, email, gruppe, status, base_region1.bezeichnung, base_region2.bezeichnung FROM base_person LEFT JOIN base_region AS base_region1 ON base_region1.id = base_person.region1 LEFT JOIN base_region AS base_region2 ON base_region2.id = base_person.region2 WHERE (gruppe = 'C' OR gruppe = 'G' OR gruppe = 'W') AND spendenquittung_zusenden=1 ORDER BY plz1, name";
		$header = array('anrede', 'titel', 'rang', 'vorname', 'praefix', 'name', 'suffix', 'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1', 'stand1', 'zusatz2', 'strasse2', 'ort2', 'plz2', 'land2', 'stand2', 'email', 'gruppe', 'status', 'region1', 'region2');
	} elseif($_GET['datenart'] == 'vereine'){
		$sql = "SELECT name, titel, rang, dachverband, zusatz1, strasse1, ort1, plz1, land1, aktivitas, ahahschaft FROM base_verein WHERE anschreiben_zusenden=1 ORDER BY plz1";
		$header = array('name', 'titel', 'rang', 'dachverband', 'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1', 'aktivitas', 'ahahschaft');
	} elseif($_GET['datenart'] == 'vips'){
		$sql = "SELECT anrede, titel, rang, vorname, praefix, name, suffix, zusatz1, strasse1, ort1, plz1, land1, datum_adresse1_stand FROM base_vip ORDER BY plz1, name";
		$header = array('anrede', 'titel', 'rang', 'vorname', 'praefix', 'name', 'suffix', 'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1', 'stand1');
	}

	if($sql != '' && is_array($header)){
		$stmt = $libDb->prepare($sql);

		$table = new LibTable($libDb);
		$table->addHeader($header);
		$table->addTableByStatement($stmt);

		if(isset($_GET['type']) && $_GET['type'] == 'csv'){
			$table->writeContentAsCSV($_GET['datenart']. '.csv');
		} else {
			$table->writeContentAsHtmlTable($_GET['datenart']. '.html');
		}
	}
}
?>