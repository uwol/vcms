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


if(!$libGenericStorage->attributeExistsInCurrentModule('show_group_y')){
	$libGenericStorage->saveValueInCurrentModule('show_group_y', 1);
}

require('lib/persons.php');


echo '<h1>' .$libConfig->verbindungName. ' - Die Mitglieder</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<div class="panel panel-default">';
echo '<div class="panel-body">';
echo '<form action="index.php?pid=intranet_mitglied_listelebende" method="post" class="form-inline">';
echo '<fieldset>';
echo '<div class="form-group">';
echo '<label for="searchterm" class="sr-only">Suchbegriff</label>';
echo '<input type="text" id="searchterm" name="searchterm" class="form-control" placeholder="Suchbegriff" />';
echo '</div> ';
echo '<button type="submit" class="btn btn-default"><i class="fa fa-search" aria-hidden="true"></i> Suchen</button>';
echo '</fieldset>';
echo '</form>';
echo '</div>';
echo '</div>';


// search term given?
if(isset($_POST['searchterm']) && $_POST['searchterm'] != ''){
	echo '<h2>Gefundene Personen</h2>';

	$stmt = $libDb->prepare('SELECT * FROM base_person WHERE gruppe != "X" AND gruppe != "T" AND gruppe != "C" AND (
		anrede LIKE :anrede OR titel LIKE :titel OR rang LIKE :rang OR name LIKE :name OR vorname LIKE :vorname OR
		zusatz1 LIKE :zusatz1 OR strasse1 LIKE :strasse1 OR ort1 LIKE :ort1 OR plz1 LIKE :plz1 OR land1 LIKE :land1 OR
		zusatz2 LIKE :zusatz2 OR strasse2 LIKE :strasse2 OR ort2 LIKE :ort2 OR plz2 LIKE :plz2 OR land2 LIKE :land2 OR
		telefon1 LIKE :telefon1 OR telefon2 LIKE :telefon2 OR mobiltelefon LIKE :mobiltelefon OR email LIKE :email OR
		webseite LIKE :webseite OR status LIKE :status OR beruf LIKE :beruf OR vita LIKE :vita OR
		semester_reception LIKE :semester_reception OR semester_promotion LIKE :semester_promotion OR
		semester_philistrierung LIKE :semester_philistrierung OR semester_aufnahme LIKE :semester_aufnahme OR
		semester_fusion LIKE :semester_fusion OR spitzname LIKE :spitzname) ORDER BY name, vorname');
	$stmt->bindValue(':anrede', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':titel', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':rang', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':name', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':vorname', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':zusatz1', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':strasse1', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':ort1', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':plz1', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':land1', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':zusatz2', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':strasse2', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':ort2', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':plz2', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':land2', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':telefon1', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':telefon2', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':mobiltelefon', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':email', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':webseite', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':status', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':beruf', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':vita', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':semester_reception', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':semester_promotion', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':semester_philistrierung', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':semester_aufnahme', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':semester_fusion', '%'.$_POST['searchterm'].'%');
	$stmt->bindValue(':spitzname', '%'.$_POST['searchterm'].'%');

	printPersons($stmt);
}
//no search term
else {
	//without group
	$stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM base_person WHERE gruppe = "" OR gruppe IS NULL');
	$stmt->execute();
	$stmt->bindColumn('number', $anzahl);
	$stmt->fetch();

	if($anzahl > 0){
		echo '<h2>Mitglieder ohne Zuordnung</h2>';
		echo '<p class="mb-4">Die folgenden Mitglieder sind keiner Gruppe zugeordnet. Die Zuordnung kann von einem Internetwart vorgenommen werden.</p>';

		$stmt = $libDb->prepare('SELECT * FROM base_person WHERE gruppe = "" OR gruppe IS NULL ORDER BY name');
		printPersons($stmt);
	}

	//Füchse
	$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person WHERE gruppe = 'F'");
	$stmt->execute();
	$stmt->bindColumn('number', $anzahl);
	$stmt->fetch();

	if($anzahl > 0){
		echo '<h2>Die Fuchsia (' .$anzahl. ')</h2>';

		$stmt = $libDb->prepare("SELECT * FROM base_person WHERE gruppe = 'F' ORDER BY name");
		printPersons($stmt);
	}

	//Burschen
	$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person WHERE gruppe = 'B'");
	$stmt->execute();
	$stmt->bindColumn('number', $anzahl);
	$stmt->fetch();

	if($anzahl > 0){
		echo '<h2>Die Burschen (' .$anzahl. ')</h2>';

		//aktive Burschen
		$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person WHERE gruppe = 'B' AND (status IS NULL OR (status NOT LIKE '%ex loco%' AND status NOT LIKE '%Inaktiv%'))");
		$stmt->execute();
		$stmt->bindColumn('number', $anzahlAktiv);
		$stmt->fetch();

		if($anzahlAktiv > 0){
			$stmt = $libDb->prepare("SELECT * FROM base_person WHERE gruppe = 'B' AND (status IS NULL OR (status NOT LIKE '%ex loco%' AND status NOT LIKE '%Inaktiv%')) ORDER BY name");
			printPersons($stmt);
		}

		//inaktive Burschen
		$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person WHERE gruppe = 'B' AND (status LIKE '%ex loco%' OR status LIKE '%Inaktiv%')");
		$stmt->execute();
		$stmt->bindColumn('number', $anzahlInaktivExLoco);
		$stmt->fetch();

		if($anzahlInaktivExLoco > 0){
			$stmt = $libDb->prepare("SELECT * FROM base_person WHERE gruppe = 'B' AND (status LIKE '%ex loco%' OR status LIKE '%Inaktiv%') ORDER BY name");
			printPersons($stmt);
		}
	}

	//Philister
	$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person WHERE gruppe = 'P'");
	$stmt->execute();
	$stmt->bindColumn('number', $anzahl);
	$stmt->fetch();

	if($anzahl > 0){
		echo '<h2>Die alten Herren (' .$anzahl. ')</h2>';

		$stmt = $libDb->prepare("SELECT * FROM base_person WHERE gruppe = 'P' ORDER BY name");
		printPersons($stmt);
	}

	if($libGenericStorage->loadValueInCurrentModule('show_group_y')){
		//Vereinsfreunde
		$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person WHERE gruppe = 'Y'");
		$stmt->execute();
		$stmt->bindColumn('number', $anzahl);
		$stmt->fetch();

		if($anzahl > 0){
			echo '<h2>Vereinsfreunde (' .$anzahl. ')</h2>';

			$stmt = $libDb->prepare("SELECT * FROM base_person WHERE gruppe = 'Y' ORDER BY name");
			printPersons($stmt);
		}
	}
}
