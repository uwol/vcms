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

function printMitglieder($stmt, $writeLetter){
	global $libDb, $libMitglied;

	echo '<table style="width:100%">';
	echo '<tr>';

	$lastsetletter = "";

	$stmt->execute();

	$i = 0;
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		if($writeLetter){
			if($lastsetletter != substr(htmlentities($row['name']), 0, 1)){
				echo '<tr><td colspan="3">';
				echo "<h2>- ".substr(htmlentities($row['name']), 0, 1)." -</h2>\n";
				echo '</td></tr>'."\n";

				$lastsetletter = substr(htmlentities($row['name']), 0, 1);
				$i = 0;
			}
		}


		echo '<td style="width:50%">';
		echo '<a id="'. $row['id'] .'">';
		echo $libMitglied->getMitgliedSignature($row['id'], 'left');
		echo '</a>'."\n";
		echo "<b>" .$libMitglied->formatMitgliedNameString($row['anrede'], $row['titel'], $row['rang'], $row['vorname'], $row['praefix'], $row['name'], $row['suffix'], 0) . '</b><br />';

		$string = $libMitglied->getChargenString($row['id'])." ".$libMitglied->getVereineString($row['id']);

		if($row['status'] != ""){
			$string .= ' '.$row['status'];
		}

		if(trim($string) != ''){
			echo $string . '<br />';
		}

		//echo '<br />';
		if($row['beruf'] != ""){
			echo $row['beruf'].'<br />';
		}

		if($row['ort1'] != ""){
			echo $row['ort1'].'<br />';
		}

		if($row['tod_datum'] != "" && $row['tod_datum'] != '0000-00-00'){
			if($row['datum_geburtstag'] != '0000-00-00'){
				echo substr($row['datum_geburtstag'], 0, 4);
			}

			echo ' - '.substr($row['tod_datum'], 0, 4).'<br />';
		}

		if($row['gruppe'] == 'F' || $row['gruppe'] == 'B' || $row['gruppe'] == 'P' || $row['gruppe'] == 'T'){
			if($row['leibmitglied'] > 0){
				echo '➔ <a href="index.php?pid=intranet_person_stammbaum&mitgliedid='.$row['id'].'">Stammbaum</a><br />';
			}
		}

		echo "</td>";

		$i++;

		if($i != 0 && $i % 2 == 0){
			echo '</tr><tr>';
		}
	}

	echo "</tr>";
	echo "</table>\n";
}
?>