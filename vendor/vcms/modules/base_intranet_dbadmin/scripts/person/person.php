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


if($libAuth->isLoggedin()){

	$id = '';
	if(isset($_REQUEST['id'])){
		$id = $_REQUEST['id'];
	}

	$aktion = '';
	if(isset($_REQUEST['aktion'])){
		$aktion = $_REQUEST['aktion'];
	}

	$mgarray = array();
	$mgarray['id'] = '';
	//Felder in der Personentabelle angeben -> Metadaten
	$felder = array('anrede', 'titel', 'rang', 'vorname', 'praefix', 'name', 'suffix', 'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1', 'zusatz2', 'strasse2', 'ort2', 'plz2', 'land2', 'region1', 'region2', 'telefon1', 'telefon2', 'mobiltelefon', 'email', 'skype', 'jabber', 'webseite', 'datum_geburtstag', 'beruf', 'heirat_datum', 'heirat_partner', 'tod_datum', 'tod_ort', 'status', 'semester_reception', 'semester_promotion', 'semester_philistrierung', 'semester_aufnahme', 'semester_fusion', 'austritt_datum', 'spitzname', 'leibmitglied', 'anschreiben_zusenden', 'spendenquittung_zusenden', 'bemerkung', 'vita');

	//Ist der Bearbeiter ein Internetwart?
	if(in_array('internetwart', $libAuth->getAemter())){
		//dann auch die sensiblen Felder bearbeiten
		$felder = array_merge($felder, array('gruppe', 'password_hash'));
	}

	/**
	*
	* Verschiedene Aktionen auf der Datenbank durchführen, je nach Kontext
	* der durch aktion definiert wird
	*
	*/

	//neue Person, leerer Datensatz
	if($aktion == 'blank'){
		foreach($felder as $feld){
			$mgarray[$feld] = '';
		}

		$mgarray['anrede'] = 'Anrede angeben!';
		$mgarray['vorname'] = 'Vornamen angeben!';
		$mgarray['name'] = 'Namen angeben!';
		$mgarray['anschreiben_zusenden'] = '1';
		$mgarray['spendenquittung_zusenden'] = '1';
		$mgarray['datum_adresse1_stand'] = '';
		$mgarray['datum_adresse2_stand'] = '';
		$mgarray['gruppe'] = '';
		$mgarray['datum_gruppe_stand'] = '';
		$mgarray['password_hash'] = '';
	}
	//Daten wurden mit blank eingegeben, werden nun gespeichert: INSERT
	elseif($aktion == 'insert'){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
			die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
		}

		//Ist der Bearbeiter kein Internetwart?
		if(!in_array('internetwart', $libAuth->getAemter())){
			die('Fehler: Diese Aktion darf nur von einem Internetwart ausgeführt werden.');
		}

		$valueArray = $_REQUEST;
		$valueArray['email'] = strtolower($valueArray['email']);
		$valueArray['jabber'] = strtolower($valueArray['jabber']);
		$valueArray['webseite'] = strtolower($valueArray['webseite']);
		$valueArray['datum_geburtstag'] = $libTime->assureMysqlDate($valueArray['datum_geburtstag']);
		$valueArray['heirat_datum'] = $libTime->assureMysqlDate($valueArray['heirat_datum']);
		$valueArray['tod_datum'] = $libTime->assureMysqlDate($valueArray['tod_datum']);
		$valueArray['austritt_datum'] = $libTime->assureMysqlDate($valueArray['austritt_datum']);
		$mgarray = $libDb->insertRow($felder, $valueArray, 'base_person', array('id' => ''));

		updateAdresseStand('base_person', 'datum_adresse1_stand', $mgarray['id']);
		updateAdresseStand('base_person', 'datum_adresse2_stand', $mgarray['id']);
		updateGruppeStand($mgarray['id']);

		//wenn ein Ehepartner angegeben wird, muss bei diesem dieses Mitglied auch als Ehepartner eingetragen werden
		updateCorrespondingEhepartner($_REQUEST['heirat_partner'], $mgarray['id']);
	}
	//bestehende Mitgliedsdaten werden modifiziert: UPDATE
	elseif($aktion == 'update'){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
			die('Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.');
		}

		//aktuelle Daten holen
		$stmt = $libDb->prepare('SELECT * FROM base_person WHERE id=:id');
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$mgarray = $stmt->fetch(PDO::FETCH_ASSOC);

		//ist das zu bearbeitende Mitglied jemals Internetwart gewesen?
		if($mgarray['id'] != '' && $libMitglied->hasBeenInternetWartAnyTime($mgarray['id'])){
			//soll die Gruppe modifiziert werden? => Drohende Eintragung als tot oder entlassen
			$gruppeWirdKritischModifiziert = false;

			if(in_array('gruppe', $felder) && $_REQUEST['gruppe'] != $mgarray['gruppe']){
				//ist eine kritische Gruppe angeben?
				if($_REQUEST['gruppe'] == 'X' || $_REQUEST['gruppe'] == 'T'){
					$gruppeWirdKritischModifiziert = true;
				}
			}

			//soll die E-Mail-Adresse modifiziert werden? => Drohende Eintragung einer leeren Adresse
			$emailIstLeer = false;
			if(in_array('email', $felder) && $_REQUEST['email'] == ''){
				$emailIstLeer = true;
			}

			//soll der password_hash modifiziert werden? => Drohende Eintragung eines leeren password_hash
			$password_hashIstLeer = false;
			if(in_array('password_hash', $felder) && $_REQUEST['password_hash'] == ''){
				$password_hashIstLeer = true;
			}

			//sollen kritische Daten modifiziert werden?
			if($gruppeWirdKritischModifiziert || $emailIstLeer || $password_hashIstLeer){
				//ist das Mitglied ein valider Intranetwart?
				if($libMitglied->couldBeValidInternetWart($mgarray['id'])){
					//dann ist das Ändern evtl ein Problem, wenn nämlich damit der letzte valide Internetwart gekillt wird
					$valideInternetWarte = $libVerein->getValideInternetWarte();

					//ist dies der letzte valide Internetwart?
					if(count($valideInternetWarte) < 2){
						//STOPP, DRAMA ahead, dann gibt es keinen validen Intranetwart mehr
						die('Fehler: Der bisherige Intranetwart ist der einzige valide, mit der Änderung gibt es keinen validen Intranetwart mehr!');
					}
				}
			}
		}


		//Adressänderungen prüfen und vermerken im Stand
		if($_REQUEST['strasse1'] != $mgarray['strasse1'] || $_REQUEST['ort1'] != $mgarray['ort1'] || $_REQUEST['plz1'] != $mgarray['plz1'] || $_REQUEST['land1'] != $mgarray['land1'] || $_REQUEST['telefon1'] != $mgarray['telefon1']){
			updateAdresseStand('base_person', 'datum_adresse1_stand', $mgarray['id']);
		}

		if($_REQUEST['strasse2'] != $mgarray['strasse2'] || $_REQUEST['ort2'] != $mgarray['ort2'] || $_REQUEST['plz2'] != $mgarray['plz2'] || $_REQUEST['land2'] != $mgarray['land2'] || $_REQUEST['telefon2'] != $mgarray['telefon2']){
			updateAdresseStand('base_person', 'datum_adresse2_stand', $mgarray['id']);
		}

		if(isset($_REQUEST['gruppe']) && $_REQUEST['gruppe'] != $mgarray['gruppe']){
			updateGruppeStand($mgarray['id']);
		}

		//wenn ein Ehepartner angegeben wird, muss bei diesem dieses Mitglied auch als Ehepartner eingetragen werden
		if($_REQUEST['heirat_partner'] != $mgarray['heirat_partner']){
			updateCorrespondingEhepartner($_REQUEST['heirat_partner'], $mgarray['id']);
		}

		$valueArray = $_REQUEST;
		$valueArray['email'] = strtolower($valueArray['email']);
		$valueArray['jabber'] = strtolower($valueArray['jabber']);
		$valueArray['webseite'] = strtolower($valueArray['webseite']);
		$valueArray['datum_geburtstag'] = $libTime->assureMysqlDate($valueArray['datum_geburtstag']);
		$valueArray['heirat_datum'] = $libTime->assureMysqlDate($valueArray['heirat_datum']);
		$valueArray['tod_datum'] = $libTime->assureMysqlDate($valueArray['tod_datum']);
		$valueArray['austritt_datum'] = $libTime->assureMysqlDate($valueArray['austritt_datum']);
		$mgarray = $libDb->updateRow($felder, $valueArray, 'base_person', array('id' => $id));
	} else {
		$stmt = $libDb->prepare('SELECT * FROM base_person WHERE id=:id');
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$mgarray = $stmt->fetch(PDO::FETCH_ASSOC);
	}

	//Bildupload durchführen
	//wurde eine Datei hochgeladen?
	if(isset($_POST['formtyp']) && $_POST['formtyp'] == 'fotoupload'){
		//wurde eine Datei hochgeladen?
		if($_FILES['bilddatei']['tmp_name'] != ''){
			if($mgarray['id'] != ''){
				$libImage->savePersonFotoByFilesArray($mgarray['id'], 'bilddatei');
			}
		}
	} elseif(isset($_GET['aktion']) && $_GET['aktion'] == 'fotodelete'){
		if($mgarray['id'] != ''){
			$libImage->deletePersonFoto($mgarray['id']);
		}
	}


	/**
	*
	* Einleitender Text
	*
	*/

	echo '<h1>Person</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo '<p>Hier können sämtliche Daten einer Person bearbeitet werden. Die Gruppe (Fuchs, Bursch etc.) kann nur von einem Internetwart ausgewählt werden, da sie als Zugangskontrolle für Seiten im VCMS dient.</p>';
	echo '<hr />';

	/**
	*
	* Löschoption
	*
	*/
	if(in_array('internetwart', $libAuth->getAemter())){
		if($mgarray['id'] != ''){
			echo '<p><a href="index.php?pid=intranet_admin_db_personenliste&amp;aktion=delete&amp;id='.$mgarray['id'].'" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')"><i class="fa fa-trash" aria-hidden="true"></i> Datensatz löschen</a></p>';
		}
	}

	echo '<div class="row">';
	echo '<div class="col-sm-9">';


	/**
	*
	* Ausgabe des Forms starten
	*
	*/

	if($aktion == 'blank'){
		$extraActionParam = '&amp;aktion=insert';
	} else {
		$extraActionParam = '&amp;aktion=update';
	}

	echo '<form action="index.php?pid=intranet_admin_db_person' .$extraActionParam. '" method="post" class="form-horizontal">';
	echo '<fieldset>';
	echo '<input type="hidden" name="formtyp" value="personendaten" />';
	echo '<input type="hidden" name="id" value="' .$mgarray['id']. '" />';

	$libForm->printTextInput('id', 'Id', $mgarray['id'], 'text', true);
	$libForm->printTextInput('anrede', 'Anrede', $mgarray['anrede']);
	$libForm->printTextInput('titel', 'Titel', $mgarray['titel']);
	$libForm->printTextInput('rang', 'Rang', $mgarray['rang']);
	$libForm->printTextInput('vorname', 'Vorname', $mgarray['vorname']);
	$libForm->printTextInput('praefix', 'Präfix', $mgarray['praefix']);
	$libForm->printTextInput('name', 'Name', $mgarray['name']);
	$libForm->printTextInput('suffix', 'Suffix', $mgarray['suffix']);

	$libForm->printTextInput('zusatz1', 'Zusatz 1', $mgarray['zusatz1']);
	$libForm->printTextInput('strasse1', 'Strasse 1', $mgarray['strasse1']);
	$libForm->printTextInput('ort1', 'Ort 1', $mgarray['ort1']);
	$libForm->printTextInput('plz1', 'Plz 1', $mgarray['plz1']);
	$libForm->printTextInput('land1', 'Land 1', $mgarray['land1']);
	$libForm->printTextInput('telefon1', 'Telefon 1', $mgarray['telefon1'], 'tel');
	$libForm->printTextInput('datum_adresse1_stand', 'Stand 1', $mgarray['datum_adresse1_stand'], 'date', true);

	$libForm->printTextInput('zusatz2', 'Zusatz 2', $mgarray['zusatz2']);
	$libForm->printTextInput('strasse2', 'Strasse 2', $mgarray['strasse2']);
	$libForm->printTextInput('ort2', 'Ort 2', $mgarray['ort2']);
	$libForm->printTextInput('plz2', 'Plz 2', $mgarray['plz2']);
	$libForm->printTextInput('land2', 'Land 2', $mgarray['land2']);
	$libForm->printTextInput('telefon2', 'Telefon 2', $mgarray['telefon2'], 'tel');
	$libForm->printTextInput('datum_adresse2_stand', 'Stand 2', $mgarray['datum_adresse2_stand'], 'date', true);

	$libForm->printRegionDropDownBox('region1', 'Region 1', $mgarray['region1']);
	$libForm->printRegionDropDownBox('region2', 'Region 2', $mgarray['region2']);

	$libForm->printTextInput('mobiltelefon', 'Mobiltelefon', $mgarray['mobiltelefon'], 'tel');
	$libForm->printTextInput('email', 'E-Mail-Adresse', $mgarray['email'], 'email');
	$libForm->printTextInput('skype', 'Skype', $mgarray['skype']);
	$libForm->printTextInput('jabber', 'XMPP', $mgarray['jabber']);
	$libForm->printTextInput('webseite', 'Webseite', $mgarray['webseite']);
	$libForm->printTextInput('datum_geburtstag', 'Geburtsdatum', $mgarray['datum_geburtstag'], 'date');
	$libForm->printTextInput('beruf', 'Beruf', $mgarray['beruf']);
	$libForm->printTextInput('heirat_datum', 'Heiratsdatum', $mgarray['heirat_datum'], 'date');

	$libForm->printMitgliederDropDownBox('heirat_partner', 'Ehepartner', $mgarray['heirat_partner']);

	$libForm->printTextInput('tod_datum', 'Todesdatum', $mgarray['tod_datum'], 'date');
	$libForm->printTextInput('tod_ort', 'Todesort', $mgarray['tod_ort'], 'date');

	$libForm->printStatusDropDownBox('status', 'Status', $mgarray['status']);

	$libForm->printSemesterDropDownBox('semester_reception', 'Semester Reception', $mgarray['semester_reception']);
	$libForm->printSemesterDropDownBox('semester_promotion', 'Semester Promotion', $mgarray['semester_promotion']);
	$libForm->printSemesterDropDownBox('semester_philistrierung', 'Semester Philistrierung', $mgarray['semester_philistrierung']);
	$libForm->printSemesterDropDownBox('semester_aufnahme', 'Semester Aufnahme', $mgarray['semester_aufnahme']);
	$libForm->printSemesterDropDownBox('semester_fusion', 'Semester Fusion', $mgarray['semester_fusion']);

	$libForm->printTextInput('austritt_datum', 'Austrittsdatum', $mgarray['austritt_datum'], 'date');
	$libForm->printTextInput('spitzname', 'Spitzname', $mgarray['spitzname']);

	$libForm->printMitgliederDropDownBox('leibmitglied', 'Leibmitglied', $mgarray['leibmitglied']);

	//Anschreiben zusenden
	$libForm->printBoolSelectBox('anschreiben_zusenden', 'Anschreiben zusenden', $mgarray['anschreiben_zusenden']);

	//Spendenquittung zusenden
	$libForm->printBoolSelectBox('spendenquittung_zusenden', 'Spendenquittung zusenden', $mgarray['spendenquittung_zusenden']);

	$libForm->printTextInput('bemerkung', 'Bemerkung', $mgarray['bemerkung']);
	$libForm->printTextarea('vita', 'Vita', $mgarray['vita']);

	//nur Internetwart darf an sensible Daten
	if(in_array('internetwart', $libAuth->getAemter())){
		$libForm->printGruppeDropDownBox('gruppe', 'Gruppe', $mgarray['gruppe'], false);
		$libForm->printTextInput('datum_gruppe_stand', 'Stand', $mgarray['datum_gruppe_stand'], 'date', true);
		$libForm->printTextInput('password_hash', 'Passwort-Hash', $mgarray['password_hash']);
	}

	echo '<input type="hidden" name="formkomplettdargestellt" value="1" />';

	$libForm->printSubmitButton('Speichern');

	echo '</fieldset>';
	echo '</form>';

	echo '</div>';
	echo '<div class="col-sm-3">';

	if($mgarray['id'] != ''){
		echo '<div class="center-block personSignatureBox personSignatureBoxLarge">';
		echo '<div class="imgBox">';

		echo '<span class="deleteIconBox">';
		echo '<a href="index.php?pid=intranet_admin_db_person&amp;id=' .$mgarray['id']. '&amp;aktion=fotodelete">';
		echo '<i class="fa fa-trash" aria-hidden="true"></i>';
		echo '</a>';
		echo '</span>';

		echo $libMitglied->getMitgliedImage($mgarray['id'], true);
		echo '</div>';
		echo '</div>';

		//image upload form
		echo '<form action="index.php?pid=intranet_admin_db_person&amp;id='. $mgarray['id'] .'" method="post" enctype="multipart/form-data" class="form-horizontal text-center">';
		echo '<input type="hidden" name="formtyp" value="fotoupload" />';
		$libForm->printFileUpload('bilddatei', 'Foto (4x3) hochladen');
		echo '</form>';
	}

	echo '</div>';
	echo '</div>';
}

function updateGruppeStand($id){
	global $libDb;

	$stmt = $libDb->prepare('UPDATE base_person SET datum_gruppe_stand=NOW() WHERE id=:id');
	$stmt->bindValue(':id', $id, PDO::PARAM_INT);
	$stmt->execute();
}

function updateAdresseStand($table, $field, $id){
	global $libDb;

	$stmt = $libDb->prepare('UPDATE '.$table.' SET ' .$field. '=NOW() WHERE id=:id');
	$stmt->bindValue(':id', $id, PDO::PARAM_INT);
	$stmt->execute();
}

function updateCorrespondingEhepartner($ehepartnerId, $mitgliedId){
	global $libDb;

	if(is_numeric($mitgliedId) && is_numeric($ehepartnerId)){
		$stmt = $libDb->prepare('UPDATE base_person SET heirat_partner=:heirat_partner WHERE id=:id');
		$stmt->bindValue(':heirat_partner', $mitgliedId, PDO::PARAM_INT);
		$stmt->bindValue(':id', $ehepartnerId, PDO::PARAM_INT);
		$stmt->execute();
	}
}
?>