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

if(!is_object($libGlobal))
	exit();


/*
* output
*/
if(isset($_GET['verein'])){
	$stmt = $libDb->prepare('SELECT * FROM base_verein WHERE id=:id');
	$stmt->bindValue(':id', $_GET['verein'], PDO::PARAM_INT);
	$stmt->execute();
	$vereinarray = $stmt->fetch(PDO::FETCH_ASSOC);

	echo '<h1>' .$libVerein->getVereinNameString($vereinarray['id']). '</h1>';

	echo '<div class="row">';

	/*
	* images
	*/
	echo '<div class="col-sm-3">';

	$filePathZirkelSvg = 'custom/kvvereine/zirkel/' .$vereinarray['id']. '.svg';
	$filePathZirkelGif = 'custom/kvvereine/zirkel/' .$vereinarray['id']. '.gif';

	if(is_file($filePathZirkelSvg)){
		echo '<p><img src="' .$filePathZirkelSvg. '" alt="Zirkel" class="img-responsive center-block" /></p>';
	} else if(is_file($filePathZirkelGif)){
		echo '<p><img src="' .$filePathZirkelGif. '" alt="Zirkel" class="img-responsive center-block" /></p>';
	}

	$filePathWappenSvg = 'custom/kvvereine/wappen/' .$vereinarray['id']. '.svg';
	$filePathWappenJpg = 'custom/kvvereine/wappen/' .$vereinarray['id']. '.jpg';

	if(is_file($filePathWappenSvg)){
		echo '<p><img src="' .$filePathWappenSvg. '" alt="Wappen" class="img-responsive center-block" /></p>';
	} else if(is_file($filePathWappenJpg)){
		echo '<p><img src="' .$filePathWappenJpg. '" alt="Wappen" class="img-responsive center-block" /></p>';
	}

	$filePathHausJpg = 'custom/kvvereine/haus/' .$vereinarray['id']. '.jpg';

	if(is_file($filePathHausJpg)){
		echo '<p><img src="' .$filePathHausJpg. '" alt="Haus" class="img-responsive center-block" /></p>';
	}

	echo '</div>';

	echo '<div class="col-sm-9">';

	/*
	* association address
	*/
	echo '<address>';

	if($vereinarray['zusatz1'] != ''){
		echo $vereinarray['zusatz1']. '<br />';
	}

	if($vereinarray['strasse1'] != ''){
		echo $vereinarray['strasse1']. '<br />';
	}

	if($vereinarray['ort1'] != ''){
		echo $vereinarray['plz1']. ' ' .$vereinarray['ort1']. '<br />';
	}

	if($vereinarray['land1'] != ''){
		echo $vereinarray['land1']. '<br />';
	}

	if($vereinarray['telefon1'] != ''){
		echo $vereinarray['telefon1']. '<br />';
	}

	if($vereinarray['webseite'] != ''){
		echo '<a href="http://' .$vereinarray['webseite']. '">' .$vereinarray['webseite']. '</a><br />';
	}

	echo '</address>';

	/*
	* association data
	*/
	if($vereinarray['farbe1'] != ''){
		echo '<p>';
		echo '<b>Farben:</b> ' .$vereinarray['farbe1']. ' ' .$vereinarray['farbe2']. ' ' .$vereinarray['farbe3'];
		echo '</p>';

		echo '<p>';
		echo '<table style="border:1px solid black;width:50px;border-collapse:collapse">';

	    if($vereinarray['farbe1'] != ''){
			echo '<tr><td style="height:10px;background-color:' .$libVerein->getFarbe($vereinarray['farbe1']). '"></td></tr>';
		}

		if($vereinarray['farbe2'] != ''){
			echo '<tr><td style="height:10px;background-color:' .$libVerein->getFarbe($vereinarray['farbe2']). '"></td></tr>';
		}

		if($vereinarray['farbe3'] != ''){
			echo '<tr><td style="height:10px;background-color:' .$libVerein->getFarbe($vereinarray['farbe3']). '"></td></tr>';
		}

		if($vereinarray['farbe4'] != ''){
			echo '<tr><td style="height:10px;background-color:' .$libVerein->getFarbe($vereinarray['farbe4']). '"></td></tr>';
		}

		echo '</table>';
		echo '</p>';
	}

	echo '<p>';
	if($vereinarray['datum_gruendung'] != ''){
		echo '<b>Gründungsdatum:</b> ';
		echo $libVerein->getGruendungString($vereinarray['datum_gruendung']);
		echo '<br />';
	}

	if($vereinarray['dachverband'] != ''){
		echo '<b>Dachverband:</b> ' .$vereinarray['dachverband']. '<br />';
	}

	if($vereinarray['dachverbandnr'] != ''){
		echo '<b>KV-Nr.</b>: ' .$vereinarray['dachverbandnr']. '<br />';
	}

	$aktivstring = '';
	if($vereinarray['aktivitas'] == 1){
		$aktivstring = ' !';
	}

	if($vereinarray['kuerzel'] != ''){
		echo '<b>Kürzel</b>: ' .$vereinarray['kuerzel'] . $aktivstring. '<br />';
	}

	if($vereinarray['aktivitas'] == 1){
		echo '<b>Aktivitas</b>: Ja<br />';
	} else {
		echo '<b>Aktivitas</b>: Nein<br />';
	}

	if($vereinarray['ahahschaft'] == 1){
		echo '<b>Altherrenschaft</b>: Ja<br />';
	} else {
		echo '<b>Altherrenschaft</b>: Nein<br />';
	}

	if($vereinarray['mutterverein'] != ''){
		echo '<b>Mutter:</b> ';
		echo '<a href="index.php?pid=vereindetail&amp;verein=' .$vereinarray['mutterverein']. '">';
		echo $libVerein->getVereinNameString($vereinarray['mutterverein']). '</a>';
		echo '<br />';
	}

	if($vereinarray['fusioniertin'] != ''){
		echo '<b>Fusioniert in:</b> ';
		echo '<a href="index.php?pid=vereindetail&amp;verein=' .$vereinarray['fusioniertin']. '">';
		echo $libVerein->getVereinNameString($vereinarray['fusioniertin']). '</a>';
		echo '<br />';
	}

	$toechterstr = $libVerein->getToechterString($vereinarray['id']);

	if($toechterstr != ''){
		echo '<b>Töchter:</b> ' .$toechterstr. '<br />';
	}

	$fusionersstr = $libVerein->getFusionertString($vereinarray['id']);

	if($fusionersstr != ''){
		echo '<b>Fusioniert aus:</b> ' .$fusionersstr. '<br />';
	}

	if($vereinarray['wahlspruch'] != ''){
		echo '<b>Wahlspruch:</b> ' .$vereinarray['wahlspruch']. '<br />';
	}

	echo '</p>';

	if($vereinarray['farbenstrophe'] != ''){
		echo '<p>';
		echo '<b>Farbenstrophe:</b><br />';
		echo nl2br($vereinarray['farbenstrophe']);
		echo '</p>';
	}

	if($vereinarray['farbenstrophe_inoffiziell'] != ''){
		echo '<p>';
		echo '<b>inoffizielle Farbenstrophe:</b><br />';
		echo nl2br($vereinarray['farbenstrophe_inoffiziell']);
		echo '</p>';
	}

	if($vereinarray['fuchsenstrophe'] != ''){
		echo '<p>';
		echo '<b>Fuchsenstrophe:</b><br />';
		echo nl2br($vereinarray['fuchsenstrophe']);
		echo '</p>';
	}

	if($vereinarray['bundeslied'] != ''){
		echo '<p>';
		echo '<b>Bundeslied:</b><br />';
		echo nl2br($vereinarray['bundeslied']);
		echo '</p>';
	}

	if($vereinarray['beschreibung'] != ''){
		echo '<p>';
		echo '<b>weitere Informationen:</b><br />';
		echo nl2br($vereinarray['beschreibung']);
		echo '</p>';
	}

	echo '</div>';
	echo '</div>';

	/*
	* members
	*/
	$stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM base_verein_mitgliedschaft, base_person WHERE base_verein_mitgliedschaft.verein = :verein AND base_verein_mitgliedschaft.mitglied = base_person.id');
	$stmt->bindValue(':verein', $vereinarray['id'], PDO::PARAM_INT);
	$stmt->execute();
	$stmt->bindColumn('number', $anzahl);
	$stmt->fetch();

	if($libAuth->isLoggedin() && $anzahl > 0){
		echo '<h2>Mitglieder</h2>';
		echo '<div class="row">';

  		$stmt = $libDb->prepare('SELECT base_verein_mitgliedschaft.mitglied, base_verein_mitgliedschaft.ehrenmitglied, base_person.gruppe FROM base_verein_mitgliedschaft, base_person WHERE base_verein_mitgliedschaft.verein = :verein AND base_verein_mitgliedschaft.mitglied = base_person.id ORDER BY base_verein_mitgliedschaft.ehrenmitglied DESC, base_person.name ASC');
  		$stmt->bindValue(':verein', $vereinarray['id'], PDO::PARAM_INT);
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			echo '<div class="col-sm-3">';

			echo $libMitglied->getMitgliedSignature($row['mitglied'], '');

			if($row['ehrenmitglied'] == 1){
				echo 'Ehrenmitgl. ';
			}

			echo $libMitglied->getMitgliedNameString($row['mitglied'], 0);
			echo '</div>';
		}

		echo '</div>';
	}
}
?>