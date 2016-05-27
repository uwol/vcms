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


/*
* determine semester
*/
if(!isset($_REQUEST['semester']) || !$libTime->isValidSemesterString($_REQUEST['semester'])){
	$stmt = $libDb->prepare("SELECT DATE_FORMAT(startdatum, '%Y-%m-01') AS datum FROM mod_internethome_nachricht ORDER BY startdatum DESC LIMIT 0,1");
	$stmt->execute();
	$stmt->bindColumn('datum', $datum);
	$stmt->fetch();

	$semester = $libTime->getSemesterEinesDatums($datum);

	if($libTime->isValidSemesterString($semester)){
		$libGlobal->semester = $semester;
	}
}


/*
* output
*/

echo '<h1>Ankündigungen ' . $libTime->getSemesterString($libGlobal->semester) . '</h1>';


/*
* semester selection
*/
$stmt = $libDb->prepare("SELECT DATE_FORMAT(startdatum,'%Y-%m-01') AS datum FROM mod_internethome_nachricht GROUP BY startdatum ORDER BY startdatum DESC");
$stmt->execute();

$daten = array();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$daten[] = $row['datum'];
}

echo $libTime->getSemesterMenu($libTime->getSemestersFromDates($daten), $libGlobal->semester);
echo '<br />';


/*
* announcements
*/
$zeitraum = $libTime->getZeitraum($libGlobal->semester);

$stmt = $libDb->prepare("SELECT * FROM mod_internethome_nachricht WHERE startdatum = :startdatum_equal OR (DATEDIFF(startdatum, :startdatum) >= 0 AND DATEDIFF(startdatum, :enddatum) < 0) ORDER BY startdatum DESC");
$stmt->bindValue(':startdatum_equal', $zeitraum[0]);
$stmt->bindValue(':startdatum', $zeitraum[0]);
$stmt->bindValue(':enddatum', $zeitraum[1]);
$stmt->execute();

$lastsetmonth = '';

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	if($lastsetmonth != substr($row['startdatum'], 0, 7)){
		if(substr($row['startdatum'], 5, 2) == '00'){ //-> 0000-00-00
			echo '<h2>- Jahr 0000 -</h2>';
		} else {
			echo '<h2>- ' .$libTime->getMonthName(substr($row['startdatum'], 5, 2)). ' ' .substr($row['startdatum'], 0, 4). ' -</h2>';
		}

		echo '<hr />';

		$lastsetmonth = substr($row["startdatum"], 0, 7);
	}

	echo '<div class="row" id="' .$row['id']. '">';
	echo '<aside class="col-xs-3">';

	$posssibleImage = $libModuleHandler->getModuleDirectory(). 'custom/bilder/' .$row['id']. '.jpg';

	if(is_file($posssibleImage)){
		echo '<img src="' .$posssibleImage. '" class="img-responsive center-block" alt="" />';
	}

	echo '</aside>';
	echo '<div class="col-xs-9">';

	if ($row['text'] != ''){
		echo $libString->parseBBCode(nl2br(trim($row['text'])));
	}

	echo '</div>';
	echo '</div>';

	echo '<hr />';
}
?>