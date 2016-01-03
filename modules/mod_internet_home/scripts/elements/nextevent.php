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


echo '<hr />';
echo '<p class="aktuell" style="font-weight:bold">Nächste Veranstaltungen:<br /></p>';
echo '<p class="aktuell">';

$stmt = $libDb->prepare("SELECT * FROM base_veranstaltung WHERE datum > NOW() ORDER BY datum LIMIT 0,4");
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$date = substr($row['datum'], 0, 10);
	$datearray = explode('-', $date);

	echo '<br /><b><a href="index.php?pid=semesterprogramm_event&amp;eventid=' .$row['id']. '">' .$row['titel']. '</a></b><br />';

	$date = $datearray[2].".".$datearray[1].'.';
	echo $libTime->wochentag($row['datum']).', '.$date.'<br />';

	$time = substr($row['datum'], 11, 5);

	if($time != '00:00'){
		echo $time.'<br />';
	}

	if($row['ort'] != ''){
		echo $row['ort'].'<br />';
	}
}

echo '</p>';
echo '<hr />';
?>