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


$libDb->connect();

echo '<h1>System-Protokoll</h1>';

echo '<div class="panel panel-default">';
echo '<div class="panel-body">';

echo '<table class="table table-condensed table-striped table-hover">';
echo '<thead>';
echo '<tr><th>Datum</th><th>Meldung</th><th>Person</th><th>IP-Adresse</th></tr>';
echo '</thead>';


$stmt = $libDb->prepare('SELECT aktion, datum, mitglied, ipadresse FROM sys_log_intranet WHERE datum >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND aktion IS NOT NULL ORDER BY datum DESC');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<tr>';
	echo '<td>' .$row['datum']. '</td>';
	echo '<td>';

	switch($row['aktion']){
		case 1:
			echo 'Login erfolgreich';
			break;
		case 2:
			echo 'Passwort falsch';
			break;
		case 10:
			echo 'Cronjobs ausgeführt';
			break;
		case 20:
			echo 'Versionsprüfung für Auto-Update ausgeführt';
			break;
		case 21:
			echo 'Auto-Update ausgeführt';
			break;
	}

	echo '</td>';
	echo '<td>' .$libPerson->getNameString($row['mitglied'], $mode = 4). '</td>';
	echo '<td>' .$row['ipadresse']. '</td>';
	echo '</tr>';
}

echo '</table>';

echo '</div>';
echo '</div>';
