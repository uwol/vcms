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

echo '<div class="panel panel-default">';
echo '<div class="panel-heading">';
echo '<h2 class="panel-title">Geburtstage</h2>';
echo '</div>';
echo '<div class="panel-body">';


//komplexe Abfrage, um im Dezember Geburtstage aus dem folgenden Jahr zu ermitteln
//Monate werden dazu normalisiert auf Raum 0 1 2 ... 10 11 relativ zum aktuellen Monat mit: (x_1 + 12 - x) % 12 = y
//z.B. im Dezember x = 12 => für Dezember: (12 + 12 - 12) % 12 = 0, für Januar: (1 + 12 - 12) % 12 = 1

$stmt = $libDb->prepare("SELECT id, datum_geburtstag FROM base_person WHERE (gruppe='P' OR gruppe='B' OR gruppe='F') AND datum_geburtstag != '' AND datum_geburtstag != '0000-00-00' AND (MOD(DATE_FORMAT(datum_geburtstag, '%m') + 12 - DATE_FORMAT(NOW(), '%m'), 12) * 100 + DATE_FORMAT(datum_geburtstag, '%d')) >= (MOD(DATE_FORMAT(NOW(), '%m') + 12 - DATE_FORMAT(NOW(), '%m'), 12) * 100 + DATE_FORMAT(NOW(), '%d')) ORDER BY (MOD(DATE_FORMAT(datum_geburtstag, '%m') + 12 - DATE_FORMAT(NOW(), '%m'), 12) * 100 + DATE_FORMAT(datum_geburtstag, '%d')) LIMIT 0,5");
$stmt->execute();

$i = 0;

echo '<dl>';

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<dt>';
	echo $libTime->formatDateTimeString($row['datum_geburtstag'], 2)

	if($libTime->checkrundergeburtstag(substr($row['datum_geburtstag'], 0, 4), @date('Y')) != false){
		echo ' '.$libTime->checkrundergeburtstag(substr($row['datum_geburtstag'], 0, 4), @date('Y')).' Jahre';
	}

	echo '</dt>';
	echo '<dd><a href="index.php?pid=intranet_person_daten&amp;personid=' .$row['id']. '">'. $libMitglied->getMitgliedNameString($row['id'], 0) .'</a></dd>';

	$i++;
}

echo '</dl>';

if($i==0){
	echo '<p>In der Datenbank sind bei den Mitgliedern keine Geburtstage angegeben.</p>';
}

echo '<hr />';

echo '<p>Als iCalendar-Dateien:<br />';
echo '&nbsp;&nbsp;<a href="webcal://' .$libConfig->sitePath. '/inc.php?iid=intranet_kalender_geburtstageaktivitas&amp;user='. $libGenericStorage->loadValueInCurrentModule("userNameICalendar"). '&amp;pass='. $libGenericStorage->loadValueInCurrentModule("passwordICalendar"). '">Geburtstage Aktive</a><br />';
echo '&nbsp;&nbsp;<a href="webcal://' .$libConfig->sitePath. '/inc.php?iid=intranet_kalender_todestage&amp;user=' .$libGenericStorage->loadValueInCurrentModule("userNameICalendar") .'&amp;pass=' .$libGenericStorage->loadValueInCurrentModule("passwordICalendar") .'">Nekrolog</a><br />';
echo '</p>';

echo '</div>';
echo '</div>';
?>