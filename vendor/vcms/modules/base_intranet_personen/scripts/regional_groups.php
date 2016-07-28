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


require('lib/mitglieder.php');

echo '<h1>Regionalzirkel</h1>';


$stmt = $libDb->prepare("SELECT id, bezeichnung FROM base_region ORDER BY bezeichnung");
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$stmt2 = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person WHERE (base_person.region1 = :region1 OR base_person.region2 = :region2)");
	$stmt2->bindValue(':region1', $row['id'], PDO::PARAM_INT);
	$stmt2->bindValue(':region2', $row['id'], PDO::PARAM_INT);
	$stmt2->execute();
	$stmt2->bindColumn('number', $anzahl);
	$stmt2->fetch();

	if($anzahl > 0){
		echo '<h2>'.$row['bezeichnung'].'</h2>';

		$stmt2 = $libDb->prepare("SELECT * FROM base_person WHERE (base_person.region1 = :region1 OR base_person.region2 = :region2) AND gruppe != 'X' AND gruppe != 'T' AND gruppe != 'V' ORDER BY name");
		$stmt2->bindValue(':region1', $row['id'], PDO::PARAM_INT);
		$stmt2->bindValue(':region2', $row['id'], PDO::PARAM_INT);

		printMitglieder($stmt2);
	}
}
?>