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

$stmtCount = $libDb->prepare("SELECT COUNT(*) AS number FROM mod_reservierung_reservierung WHERE DATEDIFF(NOW(), datum) <= 0");
$stmtCount->execute();
$stmtCount->bindColumn('number', $count);
$stmtCount->fetch();

// if there are entries
if($count > 0){
	echo '<h2>Reservierungen</h2>';

	$stmt = $libDb->prepare("SELECT person, datum, beschreibung FROM mod_reservierung_reservierung WHERE DATEDIFF(NOW(), datum) <= 0 ORDER BY datum LIMIT 0,2");
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		echo '<div class="media">';
		echo '<div class="media-body">';

		$date = $libTime->formatDateTimeString($row['datum'], 2);
		echo '<b>' .$date. ' - ' .$libMitglied->getMitgliedNameString($row['person'], 0). '</b><br />';

		if(($row['beschreibung']) != ''){
			echo '<a href="index.php?pid=intranet_reservierung_liste">';
			echo $libString->truncate(trim($row['beschreibung']), 200);
			echo '</a>';
		}

		echo '</div>';

		echo '<div class="media-right">';
		echo $libMitglied->getMitgliedSignature($row['person']);
		echo '</div>';

		echo '</div>';
		echo '<hr />';
	}
}
?>