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

echo '<h1>Logs</h1>';


echo '<h2>Erfolglose Intranet-Anmeldungen</h2>';
echo 'Personen mit mindestens fünf erfolglosen Intranet-Anmeldungen in den letzten zwei Monaten.';

echo '<table style="width:100%">';
echo '<tr><th style="width:25%">Häufigkeit</th><th style="width:25%">Meldung</th><th style="width:50%">Person</th></tr>';

$stmt = $libDb->prepare('SELECT COUNT(mitglied) AS numberOfLoginErrors, mitglied FROM sys_log_intranet WHERE aktion = 2 AND DATEDIFF(NOW(), datum) < 63 GROUP BY mitglied HAVING numberOfLoginErrors > 4 ORDER BY numberOfLoginErrors DESC');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<tr>'."\n";
	echo '<td>' .$row['numberOfLoginErrors']. '</td>';
	echo '<td><span style="color:red">Passwort falsch</span></td>';
	echo '<td><a href="index.php?pid=intranet_person_daten&personid=' .$row['mitglied']. '">' .$libMitglied->getMitgliedNameString($row['mitglied'], $mode = 4). '</a></td>';
	echo '</tr>'."\n";
}

echo '</table>';


echo '<h2>Die letzten 500 protokollierten Ereignisse</h2>';

echo '<table style="width:100%">';
echo '<tr><th style="width:30%">Datum</th><th style="width:25%">Meldung</th><th style="width:27%">Person</th><th style="width:18%">IP-Adresse</th></tr>';

$stmt = $libDb->prepare('SELECT aktion, datum, mitglied, ipadresse FROM sys_log_intranet WHERE aktion IS NOT NULL ORDER BY datum DESC LIMIT 0,500');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$time = substr($row['datum'], 11, 5);
	$datum = substr($row['datum'], 8, 2). "." .substr($row['datum'], 5, 2). "." .substr($row['datum'], 0, 4);

	echo '<tr>'."\n";
	echo '<td>' .$libTime->wochentag($row['datum']). " " .$datum. " " .$time. '</td>';
	echo '<td>';

	switch($row['aktion']){
		case 1:
			echo 'Login erfolgreich';
			break;
		case 2:
			echo '<span style="color:red">Passwort falsch</span>';
			break;
		case 10:
			echo 'Cronjobs ausgeführt';
			break;
	}

	echo '</td>';
	echo '<td><a href="index.php?pid=intranet_person_daten&personid=' .$row['mitglied']. '">' .$libMitglied->getMitgliedNameString($row['mitglied'], $mode = 4). '</a></td>';
	echo '<td>' .$row['ipadresse']. '</td>';
	echo '</tr>'."\n";
}

echo '</table>';
?>