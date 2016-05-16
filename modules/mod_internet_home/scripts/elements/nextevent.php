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

if(!is_object($libGlobal))
	exit();


echo '<h2>Aktuelles</h2>';
echo '<hr />';

echo '<h3 class="title">Nächste Veranstaltungen:</h3>';

$stmt = $libDb->prepare("SELECT * FROM base_veranstaltung WHERE datum > NOW() ORDER BY datum LIMIT 0,4");
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<p>';

	$date = substr($row['datum'], 0, 10);
	$datearray = explode('-', $date);

	echo '<a href="index.php?pid=semesterprogramm_event&amp;eventid=' .$row['id']. '">' .$row['titel']. '</a><br />';

	$date = $datearray[2].".".$datearray[1].'.';
	echo $libTime->wochentag($row['datum']).', '.$date;

	$time = substr($row['datum'], 11, 5);

	if($time != '00:00'){
		echo '<br />' . $time;
	}

	if($row['ort'] != ''){
		echo '<br />' . $row['ort'];
	}

	echo '</p>';
}

echo '<hr />';
?>