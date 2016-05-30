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


echo '<div class="col-md-4">';
echo '<h2>Nächste Veranstaltungen</h2>';
echo '<hr />';

echo '<div class="row">';
echo '<div class="hidden-xs col-sm-4 col-md-12">';

$semesterCoverString = $libTime->getSemesterCoverString($libGlobal->semester);

if($semesterCoverString != ''){
	echo '<a href="index.php?pid=semesterprogramm_calendar&amp;semester=' .$libGlobal->semester. '">';
	echo $semesterCoverString;
	echo '</a>';
}

echo '</div>';
echo '<div class="col-xs-12 col-sm-8 col-md-12">';

$stmt = $libDb->prepare("SELECT * FROM base_veranstaltung WHERE datum > NOW() ORDER BY datum LIMIT 0,4");
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<p>';
	echo '<a href="index.php?pid=semesterprogramm_event&amp;eventid=' .$row['id']. '">' .$row['titel']. '</a><br />';

	echo '<time>';

	$date = substr($row['datum'], 0, 10);
	$datearray = explode('-', $date);
	$dateString = $datearray[2]. '.' .$datearray[1]. '.';
	$timeString = substr($row['datum'], 11, 5);

	echo $libTime->wochentag($row['datum']). ', ' .$dateString;
	echo '<br />';

	if($timeString != '00:00'){
		echo $timeString;
	}

	echo '</time>';

	if($row['ort'] != ''){
		echo '<address>' .$row['ort']. '</address>';
	}

	echo '</p>';
}

echo '</div>';
echo '</div>';
echo '</div>';
?>