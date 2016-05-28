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

function printMitglieder($stmt){
	global $libDb, $libMitglied;

	$stmt->execute();

	echo '<div class="row">';

	$lastsetletter = '';

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		echo '<div class="col-sm-6 col-md-4 col-lg-3">';

		echo '<a id="' .$row['id']. '">';
		echo $libMitglied->getMitgliedSignature($row['id'], 'left');
		echo '</a>';

		echo '<b>' .$libMitglied->formatMitgliedNameString($row['anrede'], $row['titel'], $row['rang'], $row['vorname'], $row['praefix'], $row['name'], $row['suffix'], 0) . '</b><br />';

		$string = $libMitglied->getChargenString($row['id'])." ".$libMitglied->getVereineString($row['id']);

		if($row['status'] != ''){
			$string .= ' '. $row['status'];
		}

		if(trim($string) != ''){
			echo $string .'<br />';
		}

		if($row['ort1'] != ''){
			echo $row['ort1']. '<br />';
		}

		if($row['tod_datum'] != '' && $row['tod_datum'] != '0000-00-00'){
			if($row['datum_geburtstag'] != '0000-00-00'){
				echo substr($row['datum_geburtstag'], 0, 4);
			}

			echo ' - ' .substr($row['tod_datum'], 0, 4). '<br />';
		}

		echo '</div>';
	}

	echo '</div>';
}
?>