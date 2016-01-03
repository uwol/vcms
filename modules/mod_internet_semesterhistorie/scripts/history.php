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

?>
<h1>Semesterhistorie</h1>
<p>Diese Seite bietet einen Überblick über die vergangenen Semester mit ihren Programmcovern, Vorständen, Rezeptionen etc.</p>

<?php
/*
* semester navigation menu
*/
$length = 10;
$counter = 0;
$semesters = array();

$stmt = $libDb->prepare("SELECT * FROM base_semester ORDER BY SUBSTRING(semester,3) DESC");
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
	$stmt = $libDb->prepare("SELECT semester FROM base_semester ORDER BY SUBSTRING(semester,3) DESC LIMIT 0,1");
	$stmt->execute();
	$stmt->bindColumn('semester', $sem);
	$stmt->fetch();
} else {
	$sem = $libGlobal->semester;
}

/*
* output
*/

$stmt = $libDb->prepare("SELECT * FROM base_semester WHERE SUBSTRING(semester,3) <= :semester_substring ORDER BY SUBSTRING(semester,3) DESC LIMIT 0,10");
$stmt->bindValue(':semester_substring', substr($sem, 2, 8));
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<h2><a href="index.php?pid=semesterprogramm_calendar&amp;semester=' .$row['semester']. '">' .$libTime->getSemesterString($row['semester']) .'</a></h2>'."\n";

	/**
	* semester cover
	*/
	$semesterCoverString = $libTime->getSemesterCoverString($row['semester']);

	if($semesterCoverString != ''){
		echo '<div style="text-align:center;width:100%;margin-top:10px">';
		echo '<a href="index.php?pid=semesterprogramm_calendar&amp;semester=' .$row['semester']. '">';
		echo $semesterCoverString;
		echo '</a>';
		echo '</div>' ."\n";
	}

	/**
	* vorstand
	*/
	echo '<table style="width:100%">'."\n";
	echo '<tr>'."\n";

	echo '<td style="width:20%;padding-right:5px;padding-bottom:20px;padding-top:20px">';
	if($row['senior']){
		echo $libMitglied->getMitgliedSignature($row['senior'], '').'<b>Senior:</b><br /> '. $libMitglied->getMitgliedNameString($row['senior'], 0);
	}
	echo '</td>';

	echo '<td style="width:20%;padding-right:5px;padding-left:5px;padding-bottom:20px;padding-top:20px">';
	if($row['consenior']){
		echo $libMitglied->getMitgliedSignature($row['consenior'], '').'<b>Consenior:</b><br /> '. $libMitglied->getMitgliedNameString($row['consenior'], 0);
	}
	echo '</td>';

	echo '<td style="width:20%;padding-right:5px;padding-left:5px;padding-bottom:20px;padding-top:20px">';
	if($row['fuchsmajor']){
		echo $libMitglied->getMitgliedSignature($row['fuchsmajor'], '').'<b>Fuchsmajor:</b><br /> '. $libMitglied->getMitgliedNameString($row['fuchsmajor'], 0);
	}
	echo '</td>';

	echo '<td style="width:20%;padding-right:5px;padding-left:5px;padding-bottom:20px;padding-top:20px">';
	if($row['scriptor']){
		echo $libMitglied->getMitgliedSignature($row['scriptor'], '').'<b>Scriptor:</b><br /> '. $libMitglied->getMitgliedNameString($row['scriptor'], 0);
	}
	echo '</td>';

	echo '<td style="width:20%;padding-left:5px;padding-bottom:20px;padding-top:20px">';
	if($row['quaestor']){
		echo $libMitglied->getMitgliedSignature($row['quaestor'], '').'<b>Quaestor:</b><br /> '. $libMitglied->getMitgliedNameString($row['quaestor'], 0);
	}
	echo '</td>';

	echo '</tr>'."\n";
	echo '</table>'."\n";

	echo getAmt('Jubelsenior', $row['jubelsenior']);
	echo getAmt('Fuchsmajor 2', $row['fuchsmajor2']);

	echo '<div style="margin-bottom:80px">';

	/**
	* receptionen
	*/
	$stmt2 = $libDb->prepare("SELECT id FROM base_person WHERE semester_reception=:semester");
	$stmt2->bindValue(':semester', $row['semester']);

	echo getVereinsGruppe($stmt2, 'Receptionen');


	/**
	* promotionen
	*/
	$stmt2 = $libDb->prepare("SELECT id FROM base_person WHERE semester_promotion = :semester");
	$stmt2->bindValue(':semester', $row['semester']);

	echo getVereinsGruppe($stmt2, 'Promotionen');


	/**
	* philistrierungen
	*/
	$stmt2 = $libDb->prepare("SELECT id FROM base_person WHERE semester_philistrierung = :semester");
	$stmt2->bindValue(':semester', $row['semester']);

	echo getVereinsGruppe($stmt2, 'Philistrierungen');


	/**
	* aufnahmen
	*/
	$stmt2 = $libDb->prepare("SELECT id FROM base_person WHERE semester_aufnahme = :semester");
	$stmt2->bindValue(':semester', $row['semester']);

	echo getVereinsGruppe($stmt2, 'Aufnahmen');


	/**
	* fusionen
	*/
	$stmt2 = $libDb->prepare("SELECT id FROM base_person WHERE semester_fusion = :semester");
	$stmt2->bindValue(':semester', $row['semester']);

	echo getVereinsGruppe($stmt2, 'Fusionierte');


	/**
	* other functions
	*/
	echo '<br />';
	echo getAmt('VOP', $row['vop']);
	echo getAmt('VVOP', $row['vvop']);
	echo getAmt('VOPxx', $row['vopxx']);
	echo getAmt('VOPxxx', $row['vopxxx']);
	echo getAmt('VOPxxxx', $row['vopxxxx']);
	echo '<br />';
	echo getAmt('Senior Altherrenvorstand', $row['ahv_senior']);
	echo getAmt('Consenior Altherrenvorstand', $row['ahv_consenior']);
	echo getAmt('Keilbeauftragter', $row['ahv_keilbeauftragter']);
	echo getAmt('Scriptor Altherrenvorstand', $row['ahv_scriptor']);
	echo getAmt('Quaestor Altherrenvorstand', $row['ahv_quaestor']);
	echo getAmt('Beisitzer 1 Altherrenvorstand', $row['ahv_beisitzer1']);
	echo getAmt('Beisitzer 2 Altherrenvorstand', $row['ahv_beisitzer2']);
	echo '<br />';
	echo getAmt('Vorsitzender Hausverein', $row['hv_vorsitzender']);
	echo getAmt('Kassierer Hausverein', $row['hv_kassierer']);
	echo getAmt('Beisitzender 1 Hausverein', $row['hv_beisitzer1']);
	echo getAmt('Beisitzender 2 Hausverein', $row['hv_beisitzer2']);
	echo '<br />';
	echo getAmt('Archivar', $row['archivar']);
	echo getAmt('Redaktionswart', $row['redaktionswart']);
	echo getAmt('Hauswart', $row['hauswart']);
	echo getAmt('Bierwart', $row['bierwart']);
	echo getAmt('Kühlschrankwart', $row['kuehlschrankwart']);
	echo getAmt('Thekenwart', $row['thekenwart']);
	echo getAmt('Internetwart', $row['internetwart']);
	echo getAmt('Technikwart', $row['technikwart']);
	echo getAmt('Fotowart', $row['fotowart']);
	echo getAmt('Wirtschaftskassenwart', $row['wirtschaftskassenwart']);
	echo getAmt('Wichswart', $row['wichswart']);
	echo getAmt('Bootshauswart', $row['bootshauswart']);
	echo getAmt('Hüttenwart', $row['huettenwart']);
	echo getAmt('Fechtwart', $row['fechtwart']);
	echo getAmt('Stammtischwart', $row['stammtischwart']);
	echo getAmt('Musikwart', $row['musikwart']);
	echo getAmt('Ausflugswart', $row['ausflugswart']);
	echo getAmt('Sportwart', $row['sportwart']);
	echo getAmt('Couleurartikelwart', $row['couleurartikelwart']);
	echo getAmt('Ferienordner', $row['ferienordner']);
	echo getAmt('Dachverbandsberichterstatter', $row['dachverbandsberichterstatter']);

	echo '</div>';
}

function getVereinsGruppe($stmt, $title){
	global $libDb, $libMitglied;

	$namensStrings = array();

	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		$namensStrings[] = '<a href="index.php?pid=intranet_person_daten&amp;personid=' .$row['id']. '">'. $libMitglied->getMitgliedNameString($row['id'], 0) .'</a>';
	}

	if(count($namensStrings) > 0){
    	return '<b>' .$title. ':</b> ' .implode(', ', $namensStrings). '<br />';
	}
}

function getAmt($amtsname, $id){
	global $libMitglied;

	if($id != ''){
		return '<b>'.$amtsname. ':</b> <a href="index.php?pid=intranet_person_daten&amp;personid=' .$id. '">'. $libMitglied->getMitgliedNameString($id, 0) .'</a><br />';
	}
}
?>