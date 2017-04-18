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


echo '<h1>Zipfelranking</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

$stmt = $libDb->prepare('SELECT * FROM base_person, mod_zipfelranking_anzahl WHERE base_person.id = mod_zipfelranking_anzahl.id AND anzahlzipfel > 0 ORDER BY anzahlzipfel DESC');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<div class="row">';
	echo '<div class="col-xs-6 col-sm-2">';
	echo $libPerson->getSignature($row['id']);
	echo '</div>';

	echo '<div class="col-xs-6 col-sm-2">';
	echo '<b>' .$libPerson->formatNameString($row['anrede'], $row['titel'], $row['rang'], $row['vorname'], $row['praefix'], $row['name'], $row['suffix'], 5). '</b>';
	echo '<p>' .$row['anzahlzipfel']. ' Zipfel</p>';
	echo '</div>';

	echo '<div class="hidden-xs col-sm-8">';

	for($j=0; $j<$row['anzahlzipfel'] && $j < 50; $j++){
		echo '<img src="' .$libModuleHandler->getModuleDirectory(). '/custom/img/zipfel.png" class="zipfel" style="height:80px" />';
	}

	echo '</div>';
	echo '</div>';
}
