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

echo '<h1>Alterstrukturen</h1>';


$personsPerRow = 4;

$tArray = array();

$stmt = $libDb->prepare("SELECT semester FROM base_semester ORDER BY SUBSTRING(semester, 3) DESC LIMIT 0,25");
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$tArray[$row['semester']] = array();
}


$stmt = $libDb->prepare("SELECT id, vorname, praefix, name, status, gruppe, semester_reception, YEAR(FROM_DAYS(TO_DAYS(NOW()) - TO_DAYS(datum_geburtstag) + 1)) AS age FROM base_person WHERE (gruppe='B' OR gruppe='F') AND (YEAR(CURDATE()) - SUBSTRING(semester_reception, 3, 4) < 40) ORDER BY SUBSTRING(semester_reception, 3) DESC, status ASC");
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	if(isset($row['semester_reception']) && isset($tArray[$row['semester_reception']]) && is_array($tArray[$row['semester_reception']])){
		$vornameArray = explode(" ", $row['vorname']);
		$vorname = $vornameArray[0];

		$stmt2 = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person, base_semester WHERE id = :id AND (base_semester.senior = base_person.id OR base_semester.consenior = base_person.id OR base_semester.fuchsmajor = base_person.id OR base_semester.fuchsmajor2 = base_person.id OR base_semester.scriptor = base_person.id OR base_semester.quaestor = base_person.id OR base_semester.jubelsenior = base_person.id OR base_semester.vop = base_person.id OR base_semester.vvop = base_person.id OR base_semester.vopxx = base_person.id OR base_semester.vopxxx = base_person.id OR base_semester.vopxxxx = base_person.id)");
		$stmt2->bindValue(':id', $row['id'], PDO::PARAM_INT);
		$stmt2->execute();
		$stmt2->bindColumn('number', $anzahlChargen);
		$stmt2->fetch();

		$tArray[$row['semester_reception']][$row['id']]['gruppe'] = $row['gruppe'];
		$tArray[$row['semester_reception']][$row['id']]['status'] = $row['status'];

		if($row['age'] > 0 && $row['age'] < 200){
			$tArray[$row['semester_reception']][$row['id']]['alter'] = $row['age'];
		}

		$tArray[$row['semester_reception']][$row['id']]['vorname'] = trim($vorname);
		$tArray[$row['semester_reception']][$row['id']]['name'] = $row['name'];
		$tArray[$row['semester_reception']][$row['id']]['praefix'] = $row['praefix'];
		$tArray[$row['semester_reception']][$row['id']]['anzahlChargen'] = $anzahlChargen;
	}
}

$physikumSemester = $libTime->getSemesterName();
for($i = 0; $i < 4; $i++){
	$physikumSemester = $libTime->getPreviousSemesterNameOfSemester($physikumSemester);
}

$bachelorSemester = $libTime->getSemesterName();
for($i = 0; $i < 6; $i++){
	$bachelorSemester = $libTime->getPreviousSemesterNameOfSemester($bachelorSemester);
}

$masterSemester = $libTime->getSemesterName();
for($i = 0; $i < 10; $i++){
	$masterSemester = $libTime->getPreviousSemesterNameOfSemester($masterSemester);
}

/*
* output age structure of Aktivitas
*/

echo '<h2>Altersstruktur der Aktivitas</h2>';

echo '<p>Diese Tabelle strukturiert die Aktivitas anhand der Receptionssemester und soll helfen, Überalterungseffekte in der Aktivitas zu verhindern. Füchse sind <span style="background-color: #66FF66">hellgrün</span> markiert, Burschen <span style="background-color: #33DD33">dunkelgrün</span>, Inaktive und Aktive ex loco <span style="background-color: #F5A9A9">rot</span>. Die Zahlen hinter den Vornamen geben das Alter und die Anzahl geleisteter Chargen an.</p>';

echo '<p>Die Studienabschlüsse dienen der groben zeitlichen Orientierung. Eine Darstellung der Studienstände der einzelnen BbBb ist damit nicht verbunden. Bei Aktiven, die im höheren Fachsemester recipiert wurden, ist dies zu beachten. </p>';

echo '<p>';

$stmt = $libDb->prepare("SELECT COUNT(id) AS number FROM base_person WHERE gruppe='F' OR gruppe='B'");
$stmt->execute();
$stmt->bindColumn('number', $aktive);
$stmt->fetch();

echo 'Anzahl Aktive: ' . $aktive . '<br />';


$stmt = $libDb->prepare("SELECT COUNT(id) AS number FROM base_person WHERE (gruppe='F' OR gruppe='B') AND (status LIKE '%ex loco%' OR status LIKE '%Inaktiv%')");
$stmt->execute();
$stmt->bindColumn('number', $inaktive);
$stmt->fetch();

echo 'Anzahl Aktive ex loco oder inaktiv: ' . $inaktive . '<br />';

echo '</p>';

echo '<table style="border:1px solid black; width:100%">';

//for all semesters
foreach($tArray as $key1 => $value1){
	if($key1 == $physikumSemester){
		echo '<tr><td colspan="20" style="text-align: center">1. Staatsexamen Medizin</td></tr>';
	}

	if($key1 == $bachelorSemester){
		echo '<tr><td colspan="20" style="text-align: center">Bachelor</td></tr>';
	} elseif($key1 == $masterSemester){
		echo '<tr><td colspan="20" style="text-align: center">Master / 1. Staatsexamen Jura / 2. Staatsexamen Medizin</td></tr>';
	}

	echo '<tr>';
	$rowspan = max(1, ceil(count($value1) / $personsPerRow));
	echo '<td style="border:1px solid black" rowspan=' .$rowspan. '>';
	echo '<a href="index.php?pid=semesterhistorie_liste&amp;semester=' .$key1. '">' . $key1 . '</a>';
	echo '</td>';

	$i = 0;

	//for all members in that semester
	foreach($value1 as $key2 => $value2){
		if($i != 0 && $i % $personsPerRow == 0){
			echo '</tr><tr>';
		}

		echo '<td style="';
		//if($i < $personsPerRow)
			//echo 'border-top:1px solid black;';
		if(strstr(strtolower($value2['status']), 'ex loco')){
			echo 'background-color: #F5A9A9">';
		} elseif(strstr(strtolower($value2['status']), 'inaktiv')){
			echo 'background-color: #F5A9A9">';
 		} elseif($value2['gruppe'] == 'F'){
			echo 'background-color: #66FF66">';
		} else {
			echo 'background-color: #33DD33">';
		}

		echo '<a href="index.php?pid=intranet_person_daten&amp;personid=' . $key2 . '">';
		echo $value2['vorname'];

		if($value2['praefix'] != ''){
			echo ' ' . substr($value2['praefix'], 0, 1) . '.';
		}

		echo ' ' . substr($value2['name'], 0, 1) . '.';
		echo '</a>';

		if(isset($value2['alter']) && is_numeric($value2['alter'])){
			echo ' (' .$value2['alter']. ')';
		}

		echo ' (' .$value2['anzahlChargen']. ')';
		echo '</td>';

		$i++;
	}

	echo '</tr>';
}

echo '</table>';


/*
* age structure of AHAH
*/

$klassenBreite = 10;
$klassenPixelBreite = 50;
$maxBalkenPixelHeight = 300;

$ageArray = array();


$stmt = $libDb->prepare("SELECT YEAR(FROM_DAYS(TO_DAYS(NOW()) - TO_DAYS(datum_geburtstag) + 1)) AS age FROM base_person WHERE gruppe='P' HAVING age > 0 ORDER BY datum_geburtstag DESC");
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	if(!isset($ageArray[$row['age']])){
		$ageArray[$row['age']] = 0;
	}

	$ageArray[$row['age']] = $ageArray[$row['age']] + 1;
}

$ageClasses = array();

foreach($ageArray as $key => $value){
	$ageClass = $key - ($key % $klassenBreite);

	if(!isset($ageClasses[$ageClass])){
		$ageClasses[$ageClass] = 0;
	}

	$ageClasses[$ageClass] = $ageClasses[$ageClass] + $value;
}

/*
* output
*/
echo '<h2>Altersstruktur der Altherrenschaft</h2>';

echo '<p>Dieses Säulendiagramm stellt die Altherrenschaft klassifiziert nach Altersgruppen dar. In jeder Säule steht die Anzahl alter Herren der entsprechenden Altersklasse. Die Verteilung sollte annähernd gleichverteilt oder linkslastig sein, um langfristig die Stabilität des Vereins sicherzustellen.</p>';


$maxClass = 0;
foreach($ageClasses as $key => $value){
	if($value > $maxClass){
		$maxClass = $value;
	}
}

$koeff = 0;

if($maxClass > 0){
	$koeff = $maxBalkenPixelHeight / $maxClass;

	echo '<div style="position:relative;height:' .($maxBalkenPixelHeight + 50). 'px">';
	$i = 0;

	foreach($ageClasses as $key => $value){
		$left = $i * $klassenPixelBreite;
		echo '<div style="width:' .$klassenPixelBreite. 'px;position:absolute;bottom:0;left:' .$left. 'px;text-align:center;">';

		$height = max(16, floor($value * $koeff));
		echo '<div style="height:' .$height. 'px;background-color:#FF8000">';
		echo $value;
		echo '</div>';

		echo '<br />';

		echo $key . '-' . ($key + $klassenBreite - 1);
		echo '</div>';

		$i++;
	}

	echo '</div>';
} else {
	echo 'Problem: Bei den alten Herren sind keine Geburtstage eingetragen.';
}
?>