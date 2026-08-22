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


if(isset($_GET['id'])){
	$stmt = $libDb->prepare('SELECT * FROM base_verein WHERE id=:id');
	$stmt->bindValue(':id', $_GET['id'], PDO::PARAM_INT);
	$stmt->execute();
	$associationRow = $stmt->fetch(PDO::FETCH_ASSOC);

	echo '<h1>' .$libAssociation->getAssociationNameString($associationRow['id']). '</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo '<div class="row">';
	echo '<div class="col-sm-9">';

	echo '<div class="panel panel-default">';
	echo '<div class="panel-body">';
	echo '<address>';

	if($associationRow['zusatz1']){
		echo $associationRow['zusatz1']. '<br />';
	}

	if($associationRow['strasse1']){
		echo $associationRow['strasse1']. '<br />';
	}

	if($associationRow['ort1']){
		echo $associationRow['plz1']. ' ' .$associationRow['ort1']. '<br />';
	}

	if($associationRow['land1']){
		echo $associationRow['land1']. '<br />';
	}

	if($associationRow['telefon1']){
		echo $associationRow['telefon1']. '<br />';
	}

	if($associationRow['webseite']){
		echo '<a href="' .$associationRow['webseite']. '">' .$associationRow['webseite']. '</a><br />';
	}

	echo '</address>';
	echo '</div>';
	echo '</div>';


	echo '<div class="panel panel-default">';
	echo '<div class="panel-body">';

	if($associationRow['farbe1']){
		echo '<div style="width:50px">';

		if($associationRow['farbe1']){
			echo '<div style="height:10px;background-color:' .$libAssociation->getColor($associationRow['farbe1']). '"></div>';
		}

		if($associationRow['farbe2']){
			echo '<div style="height:10px;background-color:' .$libAssociation->getColor($associationRow['farbe2']). '"></div>';
		}

		if($associationRow['farbe3']){
			echo '<div style="height:10px;background-color:' .$libAssociation->getColor($associationRow['farbe3']). '"></div>';
		}

		if($associationRow['farbe4']){
			echo '<div style="height:10px;background-color:' .$libAssociation->getColor($associationRow['farbe4']). '"></div>';
		}

		echo '</div>';

		echo '<p class="mb-4">';
		echo $associationRow['farbe1']. ' ' .$associationRow['farbe2']. ' ' .$associationRow['farbe3']. '<br />';
		echo '</p>';
	}

	echo '<p class="mb-4">';

	if($associationRow['datum_gruendung']){
		echo 'Gründung ';
		echo $libAssociation->getFoundationString($associationRow['datum_gruendung']);
		echo '<br />';
	}

	if($associationRow['dachverband']){
		echo 'Dachverband: ' .$associationRow['dachverband']. '<br />';
	}

	if($associationRow['dachverbandnr']){
		echo 'Nr.: ' .$associationRow['dachverbandnr']. '<br />';
	}

	$activeString = '';

	if($associationRow['aktivitas'] == 1){
		$activeString = ' !';
	}

	if($associationRow['kuerzel']){
		echo 'Kürzel: ' .$associationRow['kuerzel'] . $activeString. '<br />';
	}

	if($associationRow['aktivitas'] == 1){
		echo 'Aktivitas: Ja<br />';
	} else {
		echo 'Aktivitas: Nein<br />';
	}

	if($associationRow['ahahschaft'] == 1){
		echo 'Altherrenschaft: Ja<br />';
	} else {
		echo 'Altherrenschaft: Nein<br />';
	}

	if($associationRow['mutterverein']){
		echo 'Mutter: ';
		echo '<a href="index.php?pid=verein&amp;id=' .$associationRow['mutterverein']. '">';
		echo $libAssociation->getAssociationNameString($associationRow['mutterverein']). '</a>';
		echo '<br />';
	}

	if($associationRow['fusioniertin']){
		echo 'Fusioniert in: ';
		echo '<a href="index.php?pid=verein&amp;id=' .$associationRow['fusioniertin']. '">';
		echo $libAssociation->getAssociationNameString($associationRow['fusioniertin']). '</a>';
		echo '<br />';
	}

	$daughtersString = $libAssociation->getDaughtersString($associationRow['id'], 'verein');

	if($daughtersString){
		echo 'Töchter: ' .$daughtersString. '<br />';
	}

	$mergedString = $libAssociation->getMergedString($associationRow['id'], 'verein');

	if($mergedString){
		echo 'Fusioniert aus: ' .$mergedString. '<br />';
	}

	if($associationRow['wahlspruch']){
		echo 'Wahlspruch: ' .$associationRow['wahlspruch']. '<br />';
	}

	echo '</p>';
	echo '</div>';
	echo '</div>';


	if($associationRow['farbenstrophe']){
		echo '<h3>Farbenstrophe</h3>';

		echo '<div class="panel panel-default">';
		echo '<div class="panel-body">';
		echo '<p class="mb-4">';
		echo nl2br($associationRow['farbenstrophe']);
		echo '</p>';
		echo '</div>';
		echo '</div>';
	}

	if($associationRow['farbenstrophe_inoffiziell']){
		echo '<h3>Inoffizielle Farbenstrophe</h3>';

		echo '<div class="panel panel-default">';
		echo '<div class="panel-body">';
		echo '<p class="mb-4">';
		echo nl2br($associationRow['farbenstrophe_inoffiziell']);
		echo '</p>';
		echo '</div>';
		echo '</div>';
	}

	if($associationRow['fuchsenstrophe']){
		echo '<h3>Fuchsenstrophe</h3>';

		echo '<div class="panel panel-default">';
		echo '<div class="panel-body">';
		echo '<p class="mb-4">';
		echo nl2br($associationRow['fuchsenstrophe']);
		echo '</p>';
		echo '</div>';
		echo '</div>';
	}

	if($associationRow['bundeslied']){
		echo '<h3>Bundeslied</h3>';

		echo '<div class="panel panel-default">';
		echo '<div class="panel-body">';
		echo '<p class="mb-4">';
		echo nl2br($associationRow['bundeslied']);
		echo '</p>';
		echo '</div>';
		echo '</div>';
	}

	if($associationRow['beschreibung']){
		echo '<div class="panel panel-default">';
		echo '<div class="panel-body">';
		echo '<p class="mb-4">';
		echo nl2br($associationRow['beschreibung']);
		echo '</p>';
		echo '</div>';
		echo '</div>';
	}

	echo '</div>';

	echo '<div class="col-sm-3">';
	echo '<div class="panel panel-default">';
	echo '<div class="panel-body">';

	$filePathZirkelSvg = 'custom/vereine/zirkel/' .$associationRow['id']. '.svg';
	$filePathZirkelGif = 'custom/vereine/zirkel/' .$associationRow['id']. '.gif';

	if(is_file($filePathZirkelSvg)){
		echo '<p class="mb-4"><img src="' .$filePathZirkelSvg. '" alt="Zirkel" class="img-responsive center-block" /></p>';
	} else if(is_file($filePathZirkelGif)){
		echo '<p class="mb-4"><img src="' .$filePathZirkelGif. '" alt="Zirkel" class="img-responsive center-block" /></p>';
	}

	$filePathWappenSvg = 'custom/vereine/wappen/' .$associationRow['id']. '.svg';
	$filePathWappenJpg = 'custom/vereine/wappen/' .$associationRow['id']. '.jpg';

	if(is_file($filePathWappenSvg)){
		echo '<p class="mb-4"><img src="' .$filePathWappenSvg. '" alt="Wappen" class="img-responsive center-block" /></p>';
	} else if(is_file($filePathWappenJpg)){
		echo '<p class="mb-4"><img src="' .$filePathWappenJpg. '" alt="Wappen" class="img-responsive center-block" /></p>';
	}

	$filePathHausJpg = 'custom/vereine/haus/' .$associationRow['id']. '.jpg';

	if(is_file($filePathHausJpg)){
		echo '<p class="mb-4"><img src="' .$filePathHausJpg. '" alt="Haus" class="img-responsive center-block" /></p>';
	}

	echo '</div>';
	echo '</div>';
	echo '</div>';

	echo '</div>';


	$stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM base_verein_mitgliedschaft, base_person WHERE base_verein_mitgliedschaft.verein = :verein AND base_verein_mitgliedschaft.mitglied = base_person.id');
	$stmt->bindValue(':verein', $associationRow['id'], PDO::PARAM_INT);
	$stmt->execute();
	$stmt->bindColumn('number', $count);
	$stmt->fetch();

	if($count > 0){
		echo '<h2>Mitglieder</h2>';

		echo '<div class="panel panel-default">';
		echo '<div class="panel-body">';
		echo '<div class="persons-grid">';

		$stmt = $libDb->prepare('SELECT base_verein_mitgliedschaft.mitglied, base_verein_mitgliedschaft.ehrenmitglied, base_person.gruppe FROM base_verein_mitgliedschaft, base_person WHERE base_verein_mitgliedschaft.verein = :verein AND base_verein_mitgliedschaft.mitglied = base_person.id ORDER BY base_verein_mitgliedschaft.ehrenmitglied DESC, base_person.name ASC');
		$stmt->bindValue(':verein', $associationRow['id'], PDO::PARAM_INT);
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			echo '<div class="persons-grid-element">';

			echo '<div>';
			echo $libPerson->getSignature($row['mitglied'], '');
			echo '</div>';

			echo '<div class="persons-grid-description">';
			echo $libPerson->getNameString($row['mitglied'], 0);

			if($row['ehrenmitglied'] == 1){
				echo '<p class="mb-4">Ehrenmitglied</p>';
			}

			echo '</div>';
			echo '</div>';
		}

		echo '</div>';
		echo '</div>';
		echo '</div>';
	}
}
