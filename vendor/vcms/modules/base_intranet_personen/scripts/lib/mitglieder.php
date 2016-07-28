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

function printMitglieder($stmt){
	global $libDb, $libMitglied;

	$stmt->execute();

	echo '<div class="row">';

	$lastsetletter = '';

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		echo '<div class="col-sm-6 col-md-4 col-lg-3">';

		echo '<div class="row">';
		echo '<div class="col-xs-6">';
		echo $libMitglied->getMitgliedSignature($row['id']);
		echo '</div>';
		echo '<div class="col-xs-6">';
		echo '<b>' .$libMitglied->formatMitgliedNameString($row['anrede'], $row['titel'], $row['rang'], $row['vorname'], $row['praefix'], $row['name'], $row['suffix'], 0) . '</b><br />';
		echo $libMitglied->getChargenString($row['id']). ' ' .$libMitglied->getVereineString($row['id']);

		if($row['status'] != ''){
			echo ' ' .$row['status'];
		}

		if($row['ort1'] != ''){
			echo '<br />' .$row['ort1'];
		}

		if($row['tod_datum'] != '' && $row['tod_datum'] != '0000-00-00'){
			echo '<br />';

			if($row['datum_geburtstag'] != '0000-00-00'){
				echo substr($row['datum_geburtstag'], 0, 4);
			}

			echo ' - ' .substr($row['tod_datum'], 0, 4);
		}

		echo '</div>';
		echo '</div>';
		echo '</div>';
	}

	echo '</div>';
}
?>