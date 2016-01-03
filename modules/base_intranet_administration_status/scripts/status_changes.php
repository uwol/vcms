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

echo '<h1>Statusänderungen</h1>';
echo '<p>Das VCMS speichert den Zeitpunkt von Änderungen an Adressen und Gruppenzugehörigkeiten. Die folgenden Tabellen listen die letzten Änderungen an diesen Daten auf. Dies kann z. B. dazu dienen, ein parallel geführtes Mitgliederverzeichnis aktuell zu halten.</p>';

echo '<h2>Gruppenänderungen</h2>';
echo '<table style="width:100%">';
echo '<tr><th style="20%">Person</th><th style="60%">Gruppe</th><th style="20%">Änderungsdatum</th></tr>';

$stmt = $libDb->prepare('SELECT *,base_gruppe.beschreibung AS gruppenbeschreibung FROM base_person,base_gruppe WHERE base_person.gruppe = base_gruppe.bezeichnung AND datum_gruppe_stand IS NOT NULL AND datum_gruppe_stand != "" ORDER BY datum_gruppe_stand DESC LIMIT 0,100');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<tr>';
	echo '<td>' .$libMitglied->formatMitgliedNameString($row['anrede'],$row['titel'],$row['rang'],$row['vorname'],$row['praefix'],$row['name'],$row['suffix'],0). '</td>';
	echo '<td>'.$row['gruppenbeschreibung'].'</td>';
	echo '<td>'.$row['datum_gruppe_stand'].'</td>';
	echo '</tr>';
}

echo '</table>';


echo '<h2>Änderungen an Adresse 1</h2>';
echo '<table style="width:100%">';
echo '<tr><th style="20%">Person</th><th style="60%">Adresse</th><th style="20%">Änderungsdatum</th></tr>';

$stmt = $libDb->prepare('SELECT * FROM base_person WHERE datum_adresse1_stand IS NOT NULL AND datum_adresse1_stand != "" ORDER BY datum_adresse1_stand DESC LIMIT 0,100');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<tr>';
	echo '<td>' .$libMitglied->formatMitgliedNameString($row['anrede'],$row['titel'],$row['rang'],$row['vorname'],$row['praefix'],$row['name'],$row['suffix'],0). '</td>';

	$adrStr = "";

	if($row['zusatz1'] != ""){
		$adrStr .= $row['zusatz1']."<br />";
	}

	if($row['strasse1'] != ""){
		$adrStr .= $row['strasse1']."<br />";
	}

	if($row['plz1'] != "" || $row['ort1'] != ""){
		$adrStr .= $row['plz1']." " .$row['ort1']. "<br />";
	}

	if($row['land1'] != ""){
		$adrStr .= $row['land1']."<br />";
	}

	if($row['telefon1'] != ""){
		$adrStr .= $row['telefon1']."<br />";
	}

	echo '<td>'.$adrStr.'</td>';
	echo '<td>'.$row['datum_adresse1_stand'].'</td>';
	echo '</tr>';
}

echo '</table>';


echo '<h2>Änderungen an Adresse 2</h2>';
echo '<table style="width:100%">';
echo '<tr><th style="20%">Person</th><th style="60%">Adresse</th><th style="20%">Änderungsdatum</th></tr>';

$stmt = $libDb->prepare('SELECT * FROM base_person WHERE datum_adresse2_stand IS NOT NULL AND datum_adresse1_stand != "" ORDER BY datum_adresse2_stand DESC LIMIT 0,100');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<tr>';
	echo '<td>' .$libMitglied->formatMitgliedNameString($row['anrede'],$row['titel'],$row['rang'],$row['vorname'],$row['praefix'],$row['name'],$row['suffix'],0). '</td>';

	$adrStr = "";

	if($row['zusatz2'] != ""){
		$adrStr .= $row['zusatz2']."<br />";
	}

	if($row['strasse2'] != ""){
		$adrStr .= $row['strasse2']."<br />";
	}

	if($row['plz2'] != "" || $row['ort2'] != ""){
		$adrStr .= $row['plz2']." " .$row['ort2']. "<br />";
	}

	if($row['land2'] != ""){
		$adrStr .= $row['land2']."<br />";
	}

	if($row['telefon2'] != ""){
		$adrStr .= $row['telefon2']."<br />";
	}

	echo '<td>'.$adrStr.'</td>';
	echo '<td>'.$row['datum_adresse2_stand'].'</td>';
	echo '</tr>';
}

echo '</table>';
?>