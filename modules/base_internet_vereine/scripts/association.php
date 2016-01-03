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
if(isset($_GET['verein'])){
	$stmt = $libDb->prepare("SELECT * FROM base_verein WHERE id=:id");
	$stmt->bindValue(':id', $_GET['verein'], PDO::PARAM_INT);
	$stmt->execute();
	$vereinarray = $stmt->fetch(PDO::FETCH_ASSOC);


	echo "<h1>". $libVerein->getVereinNameString($vereinarray['id']) ."</h1>\n";

	/*
	* images
	*/
	$imgString = '';

	if(is_file('custom/kvvereine/zirkel/' .$vereinarray['id']. '.gif')){
		$imgString .= '<img src="custom/kvvereine/zirkel/' .$vereinarray['id']. '.gif" style="border-width:1px;border-style:solid;border-color:#000000;margin-bottom:10px;width:150px" alt="Zirkel" /><br />';
	}

	if(is_file('custom/kvvereine/wappen/' .$vereinarray['id']. '.jpg')){
		$imgString .= '<img src="custom/kvvereine/wappen/' .$vereinarray['id']. '.jpg" style="margin-bottom:10px;width:150px" alt="Wappen" /><br />';
	}

	if(is_file('custom/kvvereine/haus/' .$vereinarray['id']. '.jpg')){
		$imgString .= '<img src="custom/kvvereine/haus/' .$vereinarray['id']. '.jpg" style="margin-bottom:10px;width:150px;" alt="Haus" />';
	}

	if($imgString != ""){
		echo '<div style="float:left;margin-left:10px">' . $imgString . '</div>';
	}

	/*
	* association description
	*/
	if($imgString != ""){
		echo '<div style="margin:0 0 30px 175px" class="text">';
	} else {
		echo '<div style="margin:0 0 30px 0" class="text">';
	}

	/*
	* association address
	*/
	if($vereinarray['zusatz1'] != ''){
		echo $vereinarray['zusatz1'] ."<br />\n";
	}

	if($vereinarray['strasse1'] != ''){
		echo $vereinarray['strasse1'];
	}

	if($vereinarray['ort1'] != ''){
		echo "<br />\n" .$vereinarray['plz1']. " " .$vereinarray['ort1'];
	}

	if($vereinarray['land1'] != ''){
		echo "<br />\n" .$vereinarray['land1'];
	}

	if($vereinarray['telefon1'] != ''){
		echo "<br />\nTel.: " .$vereinarray['telefon1'];
	}

	if($vereinarray['webseite'] != ''){
		echo "<br />\n".'<a href="http://' .$vereinarray['webseite']. '" target="_blank">' .$vereinarray['webseite']. '</a>';
	}

	/*
	* association data
	*/
	echo "<br />\n";

	if($vereinarray['farbe1'] != ''){
		echo "<br /><b>Farben:</b> ". $vereinarray['farbe1'] ." ". $vereinarray['farbe2'] ." ". $vereinarray['farbe3'];
		echo '<table style="margin-top:5px;margin-bottom:5px;border-width:1px;border-style:solid;border-color:#000000;height:30px;border-collapse:collapse">';

	    if($vereinarray['farbe1'] != ""){
			echo '<tr><td style="width:50px;background-color:' .$libVerein->getFarbe($vereinarray['farbe1']). '"></td></tr>';
		}

		if($vereinarray['farbe2'] != ""){
			echo '<tr><td style="width:50px;background-color:' .$libVerein->getFarbe($vereinarray['farbe2']). '"></td></tr>';
		}

		if($vereinarray['farbe3'] != ""){
			echo '<tr><td style="width:50px;background-color:' .$libVerein->getFarbe($vereinarray['farbe3']). '"></td></tr>';
		}

		if($vereinarray['farbe4'] != ""){
			echo '<tr><td style="width:50px;background-color:' .$libVerein->getFarbe($vereinarray['farbe4']). '"></td></tr>';
		}

		echo "</table>";
	}

	if($vereinarray['datum_gruendung'] != ''){
		echo "<br />\n<b>Gründungsdatum:</b> ";
		echo $libVerein->getGruendungString($vereinarray['datum_gruendung']);
	}

	if($vereinarray['dachverband'] != ''){
		echo "<br />\n<b>Dachverband:</b> " .$vereinarray['dachverband'];
	}

	if($vereinarray['dachverbandnr'] != ''){
		echo "<br />\n<b>KV-Nr.</b>: " .$vereinarray['dachverbandnr'];
	}

	$aktivstring = '';

	if($vereinarray['aktivitas'] == 1){
		$aktivstring = " !";
	}

	if($vereinarray['kuerzel'] != ''){
		echo "<br />\n<b>Kürzel</b>: " .$vereinarray['kuerzel'].$aktivstring;
	}

	if($vereinarray['aktivitas'] == 1){
		echo "<br />\n<b>Aktivitas</b>: Ja";
	} else {
		echo "<br />\n<b>Aktivitas</b>: Nein";
	}

	if($vereinarray['ahahschaft'] == 1){
		echo "<br />\n<b>Altherrenschaft</b>: Ja";
	} else {
		echo "<br />\n<b>Altherrenschaft</b>: Nein";
	}

	if($vereinarray['mutterverein'] != ''){
		echo "<br />\n<b>Mutter:</b> ";
		echo '<a href="index.php?pid=dachverband_vereindetail&amp;verein=' .$vereinarray['mutterverein']. '">';
		echo $libVerein->getVereinNameString($vereinarray['mutterverein']) ."</a>";
	}

	if($vereinarray['fusioniertin'] != ''){
		echo "<br />\n<b>Fusioniert in:</b> ";
		echo '<a href="index.php?pid=dachverband_vereindetail&amp;verein=' .$vereinarray['fusioniertin']. '">';
		echo $libVerein->getVereinNameString($vereinarray['fusioniertin']) ."</a>";
	}

	$toechterstr = $libVerein->getToechterString($vereinarray['id'], 'dachverband_vereindetail');

	if($toechterstr != ''){
		echo "<br />\n<b>Töchter:</b> " .$toechterstr;
	}

	$fusionersstr = $libVerein->getFusionertString($vereinarray['id'], 'dachverband_vereindetail');

	if($fusionersstr != ''){
		echo "<br />\n<b>Fusioniert aus:</b> " .$fusionersstr;
	}

	if($vereinarray['wahlspruch'] != ''){
		echo "<br /><br />\n<b>Wahlspruch:</b> " .$vereinarray['wahlspruch'];
	}

	if($vereinarray['farbenstrophe'] != ''){
		echo "<br /><br />\n<b>Farbenstrophe:</b><br />";
		echo nl2br($vereinarray['farbenstrophe']);
	}

	if($vereinarray['farbenstrophe_inoffiziell'] != ''){
		echo "<br /><br />\n<b>inoffizielle Farbenstrophe:</b><br />";
		echo nl2br($vereinarray['farbenstrophe_inoffiziell']);
	}

	if($vereinarray['fuchsenstrophe'] != ''){
		echo "<br /><br />\n<b>Fuchsenstrophe:</b><br />";
		echo nl2br($vereinarray['fuchsenstrophe']);
	}

	if($vereinarray['bundeslied'] != ''){
		echo "<br /><br />\n<b>Bundeslied:</b><br />";
		echo nl2br($vereinarray['bundeslied']);
	}

	if($vereinarray['beschreibung'] != ''){
		echo "<br /><br />\n<b>weitere Informationen:</b><br />";
		echo nl2br($vereinarray['beschreibung']);
	}

	echo "</div>\n";


	/*
	* members
	*/
	$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_verein_mitgliedschaft,base_person WHERE base_verein_mitgliedschaft.verein = :verein AND base_verein_mitgliedschaft.mitglied = base_person.id");
	$stmt->bindValue(':verein', $vereinarray['id'], PDO::PARAM_INT);
	$stmt->execute();
	$stmt->bindColumn('number', $anzahl);
	$stmt->fetch();


	if($libAuth->isLoggedin() && $anzahl > 0){
		echo '<div style="text-align:center"><img src="img/hr.gif" alt="" style="width:100%;height:1px;margin:5px;" /></div>'."\n";
		echo '<table style="margin-left:auto;margin-right:auto">'."\n";

		$column = 0;
		$counter = 0;

  		$stmt = $libDb->prepare("SELECT base_verein_mitgliedschaft.mitglied,base_verein_mitgliedschaft.ehrenmitglied,base_person.gruppe FROM base_verein_mitgliedschaft,base_person WHERE base_verein_mitgliedschaft.verein = :verein AND base_verein_mitgliedschaft.mitglied = base_person.id ORDER BY base_verein_mitgliedschaft.ehrenmitglied DESC, base_person.name ASC");
  		$stmt->bindValue(':verein', $vereinarray['id'], PDO::PARAM_INT);
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$counter++;

			if($column == 0){
				echo "<tr>\n";
			}

			echo '<td style="width:80px;">'."\n";
			echo $libMitglied->getMitgliedSignature($row['mitglied'],"");

			if($row['gruppe'] == "T"){
				echo '<span style="color:#990000">';
			}

			if($row['ehrenmitglied'] == 1){
				echo "<b>Ehrenmitgl.</b> ";
			}

			echo $libMitglied->getMitgliedNameString($row['mitglied'],0);

			if($row['gruppe'] == "T"){
				echo "</span>";
			}

			echo "</td>\n";

			if($column == 4 || $anzahl == $counter){
				echo "</tr>\n";
			}

			$column++;

			if($column == 5){
				$column = 0;
			}
		}

		echo "</table>\n";
	}
}
?>