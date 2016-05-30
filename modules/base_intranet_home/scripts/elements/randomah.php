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


/*
* output
*/

echo '<h2>Die AHAH</h2>';
echo '<hr />';

$stmt = $libDb->prepare("SELECT id, anrede, rang, titel, vorname, praefix, name, suffix, ort1, land1, beruf, semester_reception FROM base_person WHERE gruppe='P' ORDER BY RAND() LIMIT 0,1");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if(is_numeric($row['id'])){
	echo '<div class="row">';

	echo '<div class="col-sm-6">';
	echo $libMitglied->getMitgliedSignature($row['id']);
	echo '</div>';

	echo '<div class="col-sm-6">';
	echo '<b>' .wordwrap($libMitglied->formatMitgliedNameString($row['anrede'], $row['titel'], $row['rang'], $row['vorname'], $row['praefix'], $row['name'], $row['suffix'], 0), 12, '<br />', 1);

	$chargen = $libMitglied->getChargenString($row['id']);

	if($chargen != ''){
		echo ' '.$chargen;
	}

	$vereine = $libMitglied->getVereineString($row['id']);

	if($vereine != ''){
		echo ' '.$vereine;
	}

	echo '</b>';

	if($row['semester_reception'] != ''){
		echo '<br />Rc.: ' .substr($row['semester_reception'], 0, 2) .' '.substr($row['semester_reception'], 2, 4);
	}

	if($row['beruf'] != ''){
		echo '<br />' .$libString->silbentrennung($row['beruf'], 12);
	}

	if($row['ort1'] != ''){
		echo '<br />' .$libString->silbentrennung($row['ort1'], 12);
	}

	if($row['land1'] != ''){
		echo '<br />' .$libString->silbentrennung($row['land1'], 12);
	}

	echo '</div>';
	echo '</div>';
} else {
	echo 'In der Datenbank sind keine alten Herren vorhanden.';
}
?>