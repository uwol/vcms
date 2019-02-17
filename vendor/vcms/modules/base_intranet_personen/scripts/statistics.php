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

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();


echo '<h1>Statistik</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

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

echo '<h2>Struktur der Aktivitas</h2>';
echo '<p>Füchse sind <span style="background-color: #66FF66">hellgrün</span> markiert, Burschen <span style="background-color: #33DD33">dunkelgrün</span>, ex loco <span style="background-color: #F5A9A9">rot</span>. Die Zahlen hinter den Namen geben das Alter und die Anzahl geleisteter Chargen an.</p>';


echo '<div class="row mb-4">';

$stmt = $libDb->prepare("SELECT COUNT(id) AS number FROM base_person WHERE gruppe='F' OR gruppe='B'");
$stmt->execute();
$stmt->bindColumn('number', $aktive);
$stmt->fetch();

echo '<div class="col-xs-12 col-sm-4">';
echo '<p>';
echo '<span class="label label-default">' .$aktive. '</span> Aktive';
echo '</p>';
echo '</div>';


$stmt = $libDb->prepare("SELECT COUNT(id) AS number FROM base_person WHERE (gruppe='F' OR gruppe='B') AND (status IS NULL OR status NOT LIKE '%ex loco%')");
$stmt->execute();
$stmt->bindColumn('number', $inLoco);
$stmt->fetch();

echo '<div class="col-xs-12 col-sm-4">';
echo '<p>';
echo '<span class="label label-default">' .$inLoco. '</span> in loco';
echo '</p>';
echo '</div>';


$stmt = $libDb->prepare("SELECT COUNT(id) AS number FROM base_person WHERE (gruppe='F' OR gruppe='B') AND (status LIKE '%ex loco%' OR status LIKE '%Inaktiv%')");
$stmt->execute();
$stmt->bindColumn('number', $inaktive);
$stmt->fetch();

echo '<div class="col-xs-12 col-sm-4">';
echo '<p>';
echo '<span class="label label-default">' .$inaktive. '</span> ex loco oder inaktiv';
echo '</p>';
echo '</div>';

echo '</div>';


echo '<div class="panel panel-default">';
echo '<div class="panel-body">';
echo '<table class="table table-bordered table-condensed">';

//for all semesters
foreach($tArray as $key1 => $value1){
	if($key1 == $physikumSemester){
		echo '<tr><td colspan="20" style="text-align: center"><i class="fa fa-graduation-cap" aria-hidden="true"></i> 1. Staatsexamen Medizin</td></tr>';
	}

	if($key1 == $bachelorSemester){
		echo '<tr><td colspan="20" style="text-align: center"><i class="fa fa-graduation-cap" aria-hidden="true"></i> Bachelor</td></tr>';
	} elseif($key1 == $masterSemester){
		echo '<tr><td colspan="20" style="text-align: center"><i class="fa fa-graduation-cap" aria-hidden="true"></i> Master / 1. Staatsexamen Jura / 2. Staatsexamen Medizin</td></tr>';
	}

	echo '<tr>';
	$rowspan = max(1, ceil(count($value1) / $personsPerRow));
	echo '<td rowspan=' .$rowspan. '>';
	echo '<a href="index.php?pid=intranet_home&amp;semester=' .$key1. '">' .$key1. '</a>';
	echo '</td>';

	$i = 0;

	//for all members in that semester
	foreach($value1 as $key2 => $value2){
		if($i != 0 && $i % $personsPerRow == 0){
			echo '</tr><tr>';
		}

		echo '<td style="';

		if(strstr(strtolower($value2['status']), 'ex loco')){
			echo 'background-color: #F5A9A9">';
 		} elseif($value2['gruppe'] == 'F'){
			echo 'background-color: #66FF66">';
		} else {
			echo 'background-color: #33DD33">';
		}

		echo '<a href="index.php?pid=intranet_person&amp;id=' .$key2. '">';
		echo $value2['vorname'];

		if($value2['praefix'] != ''){
			echo ' ' .substr($value2['praefix'], 0, 1). '.';
		}

		echo ' ' .substr($value2['name'], 0, 1). '.';
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
echo '</div>';
echo '</div>';


/*
* age structure of AHAH
*/

echo '<h2>Altersstruktur der Altherrenschaft</h2>';

$classWidth = 5;
$agesAhAh = fetchAges('P');
$ageClassesAhAh = calculateAgeClasses($agesAhAh, $classWidth);


echo '<div class="panel panel-default">';
echo '<div class="panel-body">';

if(empty($ageClassesAhAh)){
	echo '<p>Bei den alten Herren sind keine Geburtstage hinterlegt.</p>';
} else {
	echo '<canvas id="age_structure" style="width:100%;height:300px"></canvas>' . PHP_EOL;
	echo '<script>' . PHP_EOL;
	echo 'var ageStructureContext = document.getElementById(\'age_structure\').getContext(\'2d\');' . PHP_EOL;
	echo 'var ahahLabels = [' .implode(', ', array_keys($ageClassesAhAh)). '];' . PHP_EOL;
	echo 'var ahahData = [' .implode(', ', array_values($ageClassesAhAh)). '];' . PHP_EOL;

	echo 'var data = {' . PHP_EOL;
	echo '  labels: ahahLabels, ' . PHP_EOL;
	echo '  datasets: [' . PHP_EOL;
	echo '    {' . PHP_EOL;
	echo '      label: "AHAH", ' . PHP_EOL;
	echo '      data: ahahData' . PHP_EOL;
	echo '    }' . PHP_EOL;
	echo '  ]' . PHP_EOL;
	echo '};' . PHP_EOL;

	echo 'var myBarChart = new Chart(ageStructureContext, { type: \'bar\', data: data, options: {} });' . PHP_EOL;
	echo '</script>' . PHP_EOL;
}

echo '</div>';
echo '</div>';


function fetchAges($gruppe){
	global $libDb;

	$stmt = $libDb->prepare("SELECT YEAR(FROM_DAYS(TO_DAYS(NOW()) - TO_DAYS(datum_geburtstag) + 1)) AS age FROM base_person WHERE gruppe=:gruppe HAVING age > 0 ORDER BY datum_geburtstag DESC");
	$stmt->bindValue(':gruppe', $gruppe);
	$stmt->execute();

	$ageArray = array();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		if(!isset($ageArray[$row['age']])){
			$ageArray[$row['age']] = 0;
		}

		$ageArray[$row['age']] = $ageArray[$row['age']] + 1;
	}

	return $ageArray;
}

function calculateAgeClasses($ageArray, $classWidth){
	$ageClasses = array();

	foreach($ageArray as $key => $value){
		$ageClass = $key - ($key % $classWidth);

		if(!isset($ageClasses[$ageClass])){
			$ageClasses[$ageClass] = 0;
		}

		$ageClasses[$ageClass] = $ageClasses[$ageClass] + $value;
	}

	return $ageClasses;
}
