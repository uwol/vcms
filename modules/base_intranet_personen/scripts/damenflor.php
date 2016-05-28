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

require('lib/mitglieder.php');

echo '<h1>Der Damenflor</h1>';

$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person WHERE gruppe = 'C'");
$stmt->execute();
$stmt->bindColumn('number', $anzahl);
$stmt->fetch();

if($anzahl > 0){
	echo '<h2>Die Couleurdamen (' .$anzahl. ')</h2>';

	$stmt = $libDb->prepare("SELECT * FROM base_person WHERE gruppe = 'C' ORDER BY name");
	printMitglieder($stmt);
}


$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person WHERE gruppe = 'G' OR gruppe = 'W'");
$stmt->execute();
$stmt->bindColumn('number', $anzahl);
$stmt->fetch();

if($anzahl > 0){
	echo '<h2>Die Gattinnen (' .$anzahl. ')</h2>';

	$stmt = $libDb->prepare("SELECT * FROM base_person WHERE gruppe = 'G' OR gruppe = 'W' ORDER BY name");
	printMitglieder($stmt);
}
?>