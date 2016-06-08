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


echo '<h1>Semesterhistorie</h1>';
echo '<p>Diese Seite bietet einen Überblick über die vergangenen Semester mit ihren Programmcovern, Vorständen, Rezeptionen etc.</p>';

/*
* semester navigation menu
*/
$length = 10;
$counter = 0;
$semesters = array();

$stmt = $libDb->prepare('SELECT * FROM base_semester ORDER BY SUBSTRING(semester,3) DESC');
$stmt->execute();

$semesters = array();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	if(($counter % $length) == 0){
		$semesters[] = $row['semester'];
	}

	$counter++;
}

echo $libTime->getSemesterMenu($semesters, $libGlobal->semester);


/*
* determine semester
*/

//if no semester is given
if(!isset($_GET['semester']) || $_GET['semester'] == ''){
	$stmt = $libDb->prepare('SELECT semester FROM base_semester ORDER BY SUBSTRING(semester,3) DESC LIMIT 0,1');
	$stmt->execute();
	$stmt->bindColumn('semester', $sem);
	$stmt->fetch();
} else {
	$sem = $libGlobal->semester;
}

/*
* output
*/

$stmt = $libDb->prepare('SELECT * FROM base_semester WHERE SUBSTRING(semester,3) <= :semester_substring ORDER BY SUBSTRING(semester,3) DESC LIMIT 0,10');
$stmt->bindValue(':semester_substring', substr($sem, 2, 8));
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<h2><a href="index.php?pid=semesterprogramm_calendar&amp;semester=' .$row['semester']. '">' .$libTime->getSemesterString($row['semester']). '</a></h2>';

	/**
	* semester cover
	*/
	$semesterCoverString = $libTime->getSemesterCoverString($row['semester']);

	if($semesterCoverString != ''){
		echo '<div class="row">';
		echo '<div class="col-xs-12">';

		echo '<div class="semestercoverBox center-block">';
		echo '<a href="index.php?pid=semesterprogramm_calendar&amp;semester=' .$row['semester']. '">';
		echo $semesterCoverString;
		echo '</a>';
		echo '</div>';

		echo '</div>';
		echo '</div>';

		echo '<hr />';
	}

	/**
	* vorstand
	*/
	echo '<div class="row">';
	echo '<div class="col-sm-1"></div>';

	echo '<div class="col-sm-2">';
	if($row['senior']){
		echo $libMitglied->getMitgliedSignature($row['senior'], '');
		echo '<p>Senior: ' .$libMitglied->getMitgliedNameString($row['senior'], 0). '</p>';
	}
	echo '</div>';

	echo '<div class="col-sm-2">';
	if($row['consenior']){
		echo $libMitglied->getMitgliedSignature($row['consenior'], '');
		echo '<p>Consenior: ' .$libMitglied->getMitgliedNameString($row['consenior'], 0). '</p>';
	}
	echo '</div>';

	echo '<div class="col-sm-2">';
	if($row['fuchsmajor']){
		echo $libMitglied->getMitgliedSignature($row['fuchsmajor'], '');
		echo '<p>Fuchsmajor: ' .$libMitglied->getMitgliedNameString($row['fuchsmajor'], 0). '</p>';
	}
	echo '</div>';

	echo '<div class="col-sm-2">';
	if($row['scriptor']){
		echo $libMitglied->getMitgliedSignature($row['scriptor'], '');
		echo '<p>Scriptor: ' .$libMitglied->getMitgliedNameString($row['scriptor'], 0). '</p>';
	}
	echo '</div>';

	echo '<div class="col-sm-2">';
	if($row['quaestor']){
		echo $libMitglied->getMitgliedSignature($row['quaestor'], '');
		echo '<p>Quaestor: ' .$libMitglied->getMitgliedNameString($row['quaestor'], 0). '</p>';
	}
	echo '</div>';

	echo '<div class="col-sm-1"></div>';
	echo '</div>';

	echo '<div>';

	echo '<p>';
	echo printAmt('Jubelsenior', $row['jubelsenior']);
	echo printAmt('Fuchsmajor 2', $row['fuchsmajor2']);
	echo '</p>';

	echo '<p>';

	/**
	* receptionen
	*/
	$stmt2 = $libDb->prepare("SELECT id FROM base_person WHERE semester_reception=:semester");
	$stmt2->bindValue(':semester', $row['semester']);

	echo printVereinsGruppe($stmt2, 'Receptionen');


	/**
	* promotionen
	*/
	$stmt2 = $libDb->prepare("SELECT id FROM base_person WHERE semester_promotion = :semester");
	$stmt2->bindValue(':semester', $row['semester']);

	echo printVereinsGruppe($stmt2, 'Promotionen');


	/**
	* philistrierungen
	*/
	$stmt2 = $libDb->prepare("SELECT id FROM base_person WHERE semester_philistrierung = :semester");
	$stmt2->bindValue(':semester', $row['semester']);

	echo printVereinsGruppe($stmt2, 'Philistrierungen');


	/**
	* aufnahmen
	*/
	$stmt2 = $libDb->prepare("SELECT id FROM base_person WHERE semester_aufnahme = :semester");
	$stmt2->bindValue(':semester', $row['semester']);

	echo printVereinsGruppe($stmt2, 'Aufnahmen');


	/**
	* fusionen
	*/
	$stmt2 = $libDb->prepare("SELECT id FROM base_person WHERE semester_fusion = :semester");
	$stmt2->bindValue(':semester', $row['semester']);

	echo printVereinsGruppe($stmt2, 'Fusionierte');

	echo '</p>';


	/**
	* other functions
	*/
	echo '<p>';
	echo printAmt('VOP', $row['vop']);
	echo printAmt('VVOP', $row['vvop']);
	echo printAmt('VOPxx', $row['vopxx']);
	echo printAmt('VOPxxx', $row['vopxxx']);
	echo printAmt('VOPxxxx', $row['vopxxxx']);
	echo '</p>';

	echo '<p>';
	echo printAmt('Senior Altherrenvorstand', $row['ahv_senior']);
	echo printAmt('Consenior Altherrenvorstand', $row['ahv_consenior']);
	echo printAmt('Keilbeauftragter', $row['ahv_keilbeauftragter']);
	echo printAmt('Scriptor Altherrenvorstand', $row['ahv_scriptor']);
	echo printAmt('Quaestor Altherrenvorstand', $row['ahv_quaestor']);
	echo printAmt('Beisitzer 1 Altherrenvorstand', $row['ahv_beisitzer1']);
	echo printAmt('Beisitzer 2 Altherrenvorstand', $row['ahv_beisitzer2']);
	echo '</p>';

	echo '<p>';
	echo printAmt('Vorsitzender Hausverein', $row['hv_vorsitzender']);
	echo printAmt('Kassierer Hausverein', $row['hv_kassierer']);
	echo printAmt('Beisitzender 1 Hausverein', $row['hv_beisitzer1']);
	echo printAmt('Beisitzender 2 Hausverein', $row['hv_beisitzer2']);
	echo '</p>';

	echo '<p>';
	echo printAmt('Archivar', $row['archivar']);
	echo printAmt('Redaktionswart', $row['redaktionswart']);
	echo printAmt('Hauswart', $row['hauswart']);
	echo printAmt('Bierwart', $row['bierwart']);
	echo printAmt('Kühlschrankwart', $row['kuehlschrankwart']);
	echo printAmt('Thekenwart', $row['thekenwart']);
	echo printAmt('Internetwart', $row['internetwart']);
	echo printAmt('Technikwart', $row['technikwart']);
	echo printAmt('Fotowart', $row['fotowart']);
	echo printAmt('Wirtschaftskassenwart', $row['wirtschaftskassenwart']);
	echo printAmt('Wichswart', $row['wichswart']);
	echo printAmt('Bootshauswart', $row['bootshauswart']);
	echo printAmt('Hüttenwart', $row['huettenwart']);
	echo printAmt('Fechtwart', $row['fechtwart']);
	echo printAmt('Stammtischwart', $row['stammtischwart']);
	echo printAmt('Musikwart', $row['musikwart']);
	echo printAmt('Ausflugswart', $row['ausflugswart']);
	echo printAmt('Sportwart', $row['sportwart']);
	echo printAmt('Couleurartikelwart', $row['couleurartikelwart']);
	echo printAmt('Ferienordner', $row['ferienordner']);
	echo printAmt('Dachverbandsberichterstatter', $row['dachverbandsberichterstatter']);
	echo '</p>';

	echo '</div>';
}

function printVereinsGruppe($stmt, $title){
	global $libMitglied;

	$namensStrings = array();

	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		$namensStrings[] = '<a href="index.php?pid=intranet_person_daten&amp;personid=' .$row['id']. '">' .$libMitglied->getMitgliedNameString($row['id'], 0). '</a>';
	}

	if(count($namensStrings) > 0){
		echo '<div>';
    	echo $title. ': ';
    	echo implode(', ', $namensStrings);
    	echo '</div>';
	}
}

function printAmt($amtsname, $id){
	global $libMitglied;

	if($id != ''){
		echo '<div>';
		echo $amtsname. ': <a href="index.php?pid=intranet_person_daten&amp;personid=' .$id. '">' .$libMitglied->getMitgliedNameString($id, 0). '</a>';
		echo '</div>';
	}
}
?>