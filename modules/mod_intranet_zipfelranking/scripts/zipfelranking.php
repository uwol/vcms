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


echo '<h1>Zipfelranking</h1>';

echo '<p>Die Anzahl Zipfel lässt sich im <a href="index.php?pid=intranet_person_daten">Profil</a> eingeben.</p>';

echo '<table>';

$stmt = $libDb->prepare('SELECT * FROM base_person, mod_zipfelranking_anzahl WHERE base_person.id = mod_zipfelranking_anzahl.id AND anzahlzipfel > 0 ORDER BY anzahlzipfel DESC');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<tr>';
	echo '<td>';
	echo '<a id="'. $row['id'] .'">';
	echo $libMitglied->getMitgliedSignature($row['id'], 'left');
	echo '</a>'."\n";
	echo '<b>' .$libMitglied->formatMitgliedNameString($row['anrede'], $row['titel'], $row['rang'], $row['vorname'], $row['praefix'], $row['name'], $row['suffix'], 5) . '</b> - '.$row['anzahlzipfel'] . ' Zipfel<br />';

	for($j=0; $j<$row['anzahlzipfel'] && $j < 50; $j++){
		echo '<img src="' . $libModuleHandler->getModuleDirectory() . 'custom/img/zipfel.png" style="height:60px" />';
	}

	echo "</td>";
	echo "</tr>";
}


echo "</table>\n";