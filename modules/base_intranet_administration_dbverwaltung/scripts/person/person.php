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

if($libAuth->isLoggedin()){
	$libForm = new LibForm();

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
	$felder = array("anrede", "titel", "rang", "vorname", "praefix", "name", "suffix", "zusatz1", "strasse1", "ort1", "plz1", "land1", "zusatz2", "strasse2", "ort2", "plz2", "land2", "region1", "region2", "telefon1", "telefon2", "mobiltelefon", "email", "skype", "jabber", "webseite", "datum_geburtstag", "beruf", "heirat_datum", "heirat_partner", "tod_datum", "tod_ort", "status", "semester_reception", "semester_promotion", "semester_philistrierung", "semester_aufnahme", "semester_fusion", "austritt_datum", "spitzname", "leibmitglied", "anschreiben_zusenden", "spendenquittung_zusenden", "bemerkung", "vita", "vita_letzterautor");

	//Ist der Bearbeiter ein Internetwart?
	if(in_array("internetwart", $libAuth->getAemter())){
		//dann auch die sensiblen Felder bearbeiten
		$felder = array_merge($felder, array("gruppe", "username", "password_hash"));
	}

	/**
	*
	* Verschiedene Aktionen auf der Datenbank durchführen, je nach Kontext
	* der durch aktion definiert wird
	*
	*/

	//neue Person, leerer Datensatz
	if($aktion == "blank"){
		foreach($felder as $feld){
			$mgarray[$feld] = '';
		}

		$mgarray['anrede'] = "Anrede angeben!";
		$mgarray['vorname'] = "Vornamen angeben!";
		$mgarray['name'] = "Namen angeben!";
		$mgarray['anschreiben_zusenden'] = "1";
		$mgarray['spendenquittung_zusenden'] = "1";
		$mgarray['datum_adresse1_stand'] = '';
		$mgarray['datum_adresse2_stand'] = '';
		$mgarray['gruppe'] = '';
		$mgarray['datum_gruppe_stand'] = '';
		$mgarray['username'] = '';
		$mgarray['password_hash'] = '';
	}
	//Daten wurden mit blank eingegeben, werden nun gespeichert: INSERT
	elseif($aktion == "insert"){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
			die("Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.");
		}

		//Ist der Bearbeiter kein Internetwart?
		if(!in_array("internetwart",$libAuth->getAemter())){
			die("Fehler: Diese Aktion darf nur von einem Internetwart ausgeführt werden.");
		}

		$valueArray = $_REQUEST;
		$valueArray['datum_geburtstag'] = $libTime->assureMysqlDate($valueArray['datum_geburtstag']);
		$valueArray['heirat_datum'] = $libTime->assureMysqlDate($valueArray['heirat_datum']);
		$valueArray['tod_datum'] = $libTime->assureMysqlDate($valueArray['tod_datum']);
		$valueArray['austritt_datum'] = $libTime->assureMysqlDate($valueArray['austritt_datum']);
		$mgarray = $libDb->insertRow($felder, $valueArray, "base_person", array('id' => ''));

		updateAdresseStand("base_person", "datum_adresse1_stand", $mgarray['id']);
		updateAdresseStand("base_person", "datum_adresse2_stand", $mgarray['id']);
		updateGruppeStand($mgarray['id']);

		//wenn ein Ehepartner angegeben wird, muss bei diesem dieses Mitglied auch als Ehepartner eingetragen werden
		updateCorrespondingEhepartner($_REQUEST['heirat_partner'], $mgarray['id']);
	}
	//bestehende Mitgliedsdaten werden modifiziert: UPDATE
	elseif($aktion == "update"){
		if(!isset($_POST['formkomplettdargestellt']) || !$_POST['formkomplettdargestellt']){
			die("Die Eingabemaske war noch nicht komplett dargestellt. Bitte Seite neu laden.");
		}

		//aktuelle Daten holen
		$stmt = $libDb->prepare("SELECT * FROM base_person WHERE id=:id");
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$mgarray = $stmt->fetch(PDO::FETCH_ASSOC);

		//ist das zu bearbeitende Mitglied jemals Internetwart gewesen?
		if($mgarray['id'] != "" && $libMitglied->hasBeenInternetWartAnyTime($mgarray['id'])){
			//soll die Gruppe modifiziert werden? => Drohende Eintragung als tot oder entlassen
			$gruppeWirdKritischModifiziert = false;

			if(in_array("gruppe", $felder) && $_REQUEST['gruppe'] != $mgarray['gruppe']){
				//ist eine kritische Gruppe angeben?
				if($_REQUEST['gruppe'] == "X" || $_REQUEST['gruppe'] == "T"){
					$gruppeWirdKritischModifiziert = true;
				}
			}

			//soll der Username modifiziert werden? => Drohende Eintragung eines leeren Usernamen
			$usernameIstLeer = false;
			if(in_array("username", $felder) && $_REQUEST['username'] == ""){
				$usernameIstLeer = true;
			}

			//soll der password_hash modifiziert werden? => Drohende Eintragung eines leeren password_hash
			$password_hashIstLeer = false;
			if(in_array("password_hash", $felder) && $_REQUEST['password_hash'] == ""){
				$password_hashIstLeer = true;
			}

			//sollen kritische Daten modifiziert werden?
			if($gruppeWirdKritischModifiziert || $usernameIstLeer || $password_hashIstLeer){
				//ist das Mitglied ein valider Intranetwart?
				if($libMitglied->couldBeValidInternetWart($mgarray['id'])){
					//dann ist das Ändern evtl ein Problem, wenn nämlich damit der letzte valide Internetwart gekillt wird
					$valideInternetWarte = $libVerein->getValideInternetWarte();

					//ist dies der letzte valide Internetwart?
					if(count($valideInternetWarte) < 2){
						//STOPP, DRAMA ahead, dann gibt es keinen validen Intranetwart mehr
						die('Fataler Fehler: Der bisherige Intranetwart ist der einzige valide, mit der Änderung gibt es keinen validen Intranetwart mehr!');
					}
				}
			}
		}


		//Adressänderungen prüfen und vermerken im Stand
		if($_REQUEST['strasse1'] != $mgarray['strasse1'] || $_REQUEST['ort1'] != $mgarray['ort1'] || $_REQUEST['plz1'] != $mgarray['plz1'] || $_REQUEST['land1'] != $mgarray['land1'] || $_REQUEST['telefon1'] != $mgarray['telefon1']){
			updateAdresseStand("base_person", "datum_adresse1_stand", $mgarray['id']);
		}

		if($_REQUEST['strasse2'] != $mgarray['strasse2'] || $_REQUEST['ort2'] != $mgarray['ort2'] || $_REQUEST['plz2'] != $mgarray['plz2'] || $_REQUEST['land2'] != $mgarray['land2'] || $_REQUEST['telefon2'] != $mgarray['telefon2']){
			updateAdresseStand("base_person", "datum_adresse2_stand", $mgarray['id']);
		}

		if(isset($_REQUEST['gruppe']) && $_REQUEST['gruppe'] != $mgarray['gruppe']){
			updateGruppeStand($mgarray['id']);
		}

		//wenn ein Ehepartner angegeben wird, muss bei diesem dieses Mitglied auch als Ehepartner eingetragen werden
		if($_REQUEST['heirat_partner'] != $mgarray['heirat_partner']){
			updateCorrespondingEhepartner($_REQUEST['heirat_partner'], $mgarray['id']);
		}

		$valueArray = $_REQUEST;
		$valueArray['datum_geburtstag'] = $libTime->assureMysqlDate($valueArray['datum_geburtstag']);
		$valueArray['heirat_datum'] = $libTime->assureMysqlDate($valueArray['heirat_datum']);
		$valueArray['tod_datum'] = $libTime->assureMysqlDate($valueArray['tod_datum']);
		$valueArray['austritt_datum'] = $libTime->assureMysqlDate($valueArray['austritt_datum']);
		$mgarray = $libDb->updateRow($felder, $valueArray, "base_person", array('id' => $id));
	} else {
		$stmt = $libDb->prepare("SELECT * FROM base_person WHERE id=:id");
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$mgarray = $stmt->fetch(PDO::FETCH_ASSOC);
	}

	//Bildupload durchführen
	//wurde eine Datei hochgeladen?
	if(isset($_POST['formtyp']) && $_POST['formtyp'] == "fotodatenupload"){
		//wurde eine Datei hochgeladen?
		if($_FILES['bilddatei']['tmp_name'] != ""){
			if($mgarray['id'] != ""){
				$libImage = new LibImage($libTime, $libGenericStorage);
				$libImage->savePersonFotoByFilesArray($mgarray['id'], "bilddatei");
			}
		}
	} elseif(isset($_POST['formtyp']) && $_POST['formtyp'] == "fotodatendelete"){
		if($mgarray['id'] != ""){
			$libImage = new LibImage($libTime, $libGenericStorage);
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

	if($mgarray['id'] != ""){
		echo '<div style="float:right;text-align:right">';
		echo $libMitglied->getMitgliedSignature($mgarray['id'], "right");
		echo '</div>';

		echo '<div style="float:right;text-align:right">';
		//Fotouploadform
		echo '<form method="post" enctype="multipart/form-data" action="index.php?pid=intranet_admin_db_person&amp;id='. $mgarray['id'] .'">';
		echo '<input type="hidden" name="formtyp" value="fotodatenupload" />';
		echo '<input name="bilddatei" type="file" size="10" /><br />';
		echo '<input type="submit" value="Foto hochladen" style="width: 10em" />';
		echo '</form>';

		//Fotolöschform
		echo '<form method="post" action="index.php?pid=intranet_admin_db_person&amp;id='. $mgarray['id'] .'">';
		echo '<input type="hidden" name="formtyp" value="fotodatendelete" />';
		echo '<input type="submit" value="Foto löschen" style="width: 10em" />';
		echo '</form>';
		echo '</div>';
	}


	/**
	*
	* Löschoption
	*
	*/

	//Ist der Bearbeiter ein Internetwart?
	if(in_array("internetwart", $libAuth->getAemter())){
		if($mgarray['id'] != ''){
			echo '<p><a href="index.php?pid=intranet_admin_db_personenliste&amp;aktion=delete&amp;id='.$mgarray['id'].'" onclick="return confirm(\'Willst Du den Datensatz wirklich löschen?\')">Datensatz löschen</a></p>';
		}
	}


	/**
	*
	* Ausgabe des Forms starten
	*
	*/

	if($aktion == "blank"){
		$extraActionParam = "&amp;aktion=insert";
	} else {
		$extraActionParam = "&amp;aktion=update";
	}

	echo '<form action="index.php?pid=intranet_admin_db_person' .$extraActionParam. '" method="post">';
	echo '<input type="submit" value="Speichern" name="Save"><br />';
	echo '<input type="hidden" name="formtyp" value="personendaten" />';
	echo '<input type="hidden" name="id" value="' .$mgarray['id']. '" />';
	echo '<input size="30" type="text" name="id" value="' .$mgarray['id']. '" disabled /> Id<br />';
	echo '<input size="30" type="text" name="anrede" value="' .$mgarray['anrede']. '" /> Anrede<br />';
	echo '<input size="30" type="text" name="titel" value="' .$mgarray['titel']. '" /> Titel<br />';
	echo '<input size="30" type="text" name="rang" value="' .$mgarray['rang']. '" /> Rang<br />';
	echo '<input size="30" type="text" name="vorname" value="' .$mgarray['vorname']. '" /> Vorname<br />';
	echo '<input size="30" type="text" name="praefix" value="' .$mgarray['praefix']. '" /> Präfix<br />';
	echo '<input size="30" type="text" name="name" value="' .$mgarray['name']. '" /> Name<br />';
	echo '<input size="30" type="text" name="suffix" value="' .$mgarray['suffix']. '" /> Suffix<br />';

	echo '<input size="30" type="text" name="zusatz1" value="' .$mgarray['zusatz1']. '" /> Zusatz1<br />';
	echo '<input size="30" type="text" name="strasse1" value="' .$mgarray['strasse1']. '" /> Strasse1<br />';
	echo '<input size="30" type="text" name="ort1" value="' .$mgarray['ort1']. '" /> Ort1<br />';
	echo '<input size="30" type="text" name="plz1" value="' .$mgarray['plz1']. '" /> Plz1<br />';
	echo '<input size="30" type="text" name="land1" value="' .$mgarray['land1']. '" /> Land1<br />';
	echo '<input size="30" type="text" name="telefon1" value="' .$mgarray['telefon1']. '" /> Telefon1<br />';
	echo '<input size="30" type="text" name="datum_adresse1_stand" value="' .$mgarray['datum_adresse1_stand']. '" disabled /> Stand1<br />';

	echo '<input size="30" type="text" name="zusatz2" value="' .$mgarray['zusatz2']. '" /> Zusatz2<br />';
	echo '<input size="30" type="text" name="strasse2" value="' .$mgarray['strasse2']. '" /> Strasse2<br />';
	echo '<input size="30" type="text" name="ort2" value="' .$mgarray['ort2']. '" /> Ort2<br />';
	echo '<input size="30" type="text" name="plz2" value="' .$mgarray['plz2']. '" /> Plz2<br />';
	echo '<input size="30" type="text" name="land2" value="' .$mgarray['land2']. '" /> Land2<br />';
	echo '<input size="30" type="text" name="telefon2" value="' .$mgarray['telefon2']. '" /> Telefon2<br />';
	echo '<input size="30" type="text" name="datum_adresse2_stand" value="' .$mgarray['datum_adresse2_stand']. '" disabled /> Stand2<br />';

	echo $libForm->getRegionDropDownBox("region1","Region1",$mgarray['region1']);
	echo $libForm->getRegionDropDownBox("region2","Region2",$mgarray['region2']);

	echo '<input size="30" type="text" name="mobiltelefon" value="' .$mgarray['mobiltelefon']. '" /> Mobiltelefon<br />';
	echo '<input size="30" type="text" name="email" value="' .$mgarray['email']. '" /> Email<br />';
	echo '<input size="30" type="text" name="skype" value="' .$mgarray['skype']. '" /> Skype<br />';
	echo '<input size="30" type="text" name="jabber" value="' .$mgarray['jabber']. '" /> XMPP<br />';
	echo '<input size="30" type="text" name="webseite" value="' .$mgarray['webseite']. '" /> Webseite<br />';
	echo '<input size="30" type="text" name="datum_geburtstag" value="' .$mgarray['datum_geburtstag']. '" /> Geburtsdatum<br />';
	echo '<input size="30" type="text" name="beruf" value="' .$mgarray['beruf']. '" /> Beruf<br />';
	echo '<input size="30" type="text" name="heirat_datum" value="' .$mgarray['heirat_datum']. '" /> Heiratsdatum<br />';

	echo $libForm->getMitgliederDropDownBox("heirat_partner","Ehepartner",$mgarray['heirat_partner']);

	echo '<input size="30" type="text" name="tod_datum" value="' .$mgarray['tod_datum']. '" /> Todesdatum<br />';
	echo '<input size="30" type="text" name="tod_ort" value="' .$mgarray['tod_ort']. '" /> Todesort<br />';

	echo $libForm->getStatusDropDownBox("status","Status",$mgarray['status']);

	echo '<br />Die folgenden Semester stammen aus der Semestertabelle und müssen dort angelegt worden sein, um hier ausgewählt werden zu können:<br />';
	echo $libForm->getSemesterDropDownBox("semester_reception", "Semester Reception", $mgarray['semester_reception']);
	echo $libForm->getSemesterDropDownBox("semester_promotion", "Semester Promotion", $mgarray['semester_promotion']);
	echo $libForm->getSemesterDropDownBox("semester_philistrierung", "Semester Philistrierung", $mgarray['semester_philistrierung']);
	echo $libForm->getSemesterDropDownBox("semester_aufnahme", "Semester Aufnahme", $mgarray['semester_aufnahme']);
	echo $libForm->getSemesterDropDownBox("semester_fusion", "Semester Fusion", $mgarray['semester_fusion']);

	echo '<input size="30" type="text" name="austritt_datum" value="' .$mgarray['austritt_datum']. '" /> Austrittsdatum<br />';
	echo '<input size="30" type="text" name="spitzname" value="' .$mgarray['spitzname']. '" /> Spitzname<br />';

	echo $libForm->getMitgliederDropDownBox("leibmitglied","Leibmitglied",$mgarray['leibmitglied']);

	//Anschreiben zusenden
	echo $libForm->getBoolSelectBox("anschreiben_zusenden","Anschreiben zusenden",$mgarray['anschreiben_zusenden']);

	//Spendenquittung zusenden
	echo $libForm->getBoolSelectBox("spendenquittung_zusenden","Spendenquittung zusenden",$mgarray['spendenquittung_zusenden']);

	echo '<input size="30"  type="text" name="bemerkung" value="' .$mgarray['bemerkung']. '" /> Bemerkung<br />';
	echo 'Vita<br /><textarea name="vita" cols="70" rows="10">' . $mgarray['vita'] .'</textarea><br />';

	echo $libForm->getMitgliederDropDownBox("vita_letzterautor","Vita letzter Autor",$mgarray['vita_letzterautor']);

	//nur Internetwart darf an sensible Daten
	if(in_array("internetwart",$libAuth->getAemter())){
		//ist das zu bearbeitende Mitglied jemals Internetwart gewesen
		if($mgarray['id'] != "" && $libMitglied->hasBeenInternetWartAnyTime($mgarray['id'])){
			echo '<p><b>!!! VORSICHT BEI DER MODIFIKATION DER FOLGENDEN DATEN !!!</b></p>';
			echo '<p>Diese Person ist ein Internetwart.</p>';

			$valideInternetWarte = $libVerein->getValideInternetWarte();
			echo 'Die folgenden Internetwarte haben Intranetzugang und sind nicht verstorben oder ausgetreten: ';

			foreach($valideInternetWarte as $key => $value){
				echo $libMitglied->getMitgliedNameString($key,5). ", ";
			}

			echo '<p>Falls dies der Datensatz des einzigen Internetwartes ist, so sollte eine zweite Person mit Intranetzugang zur Sicherheit auch zu einem Internetwart gemacht werden. Andernfalls kann es passieren, dass der Internetwart durch die Modifikation der folgenden Daten aus dem System ausgesperrt wird. In diesem Fall muss mit dem Installationsscript ein neuer Intranetwart angelegt werden. Dies wird in der Installationsanleitung erklärt.</p>';
		}

		echo $libForm->getGruppeDropDownBox("gruppe","Gruppe",$mgarray['gruppe'],false);
		echo '<input size="30" type="text" name="datum_gruppe_stand" value="' .$mgarray['datum_gruppe_stand']. '" disabled /> Stand<br />';

		//Credentials
		echo '<p><b>Achtung</b></p>';
		echo '<p>Durch Eingabe von Benutzername und Passwort wird Zugang zum Intranet gewährt. Dies geschieht meistens aufgrund einer Registrierungsanfrage. Es ist vor einer Freischaltung <b>unbedingt</b> die Person zu kontaktieren, die sich registriert hat. Falls dies telefonisch erfolgt, sollte die TelefonNr. dem Verein bekannt sein, und nicht auf die TelefonNr. aus der Registrierungsmail vertraut werden. Ein einziger falsch vergebener Intranetaccount genügt, um das Intranet zu kompromittieren!</p>';

		echo '<input size="30" type="text" name="username" value="' .$mgarray['username']. '" /> Username<br />';
		echo '<input size="50" type="text" name="password_hash" value="' .$mgarray['password_hash']. '" /> Password-Hash<br />';
	}

	echo '<input type="hidden" name="formkomplettdargestellt" value="1" />';
	echo '<input type="submit" value="Speichern" name="Save"><br />';
	echo "</form>";
}

function updateGruppeStand($id){
	global $libDb;

	$stmt = $libDb->prepare("UPDATE base_person SET datum_gruppe_stand=NOW() WHERE id=:id");
	$stmt->bindValue(':id', $id, PDO::PARAM_INT);
	$stmt->execute();
}

function updateAdresseStand($table, $field, $id){
	global $libDb;

	$stmt = $libDb->prepare("UPDATE ".$table." SET " .$field. "=NOW() WHERE id=:id");
	$stmt->bindValue(':id', $id, PDO::PARAM_INT);
	$stmt->execute();
}

function updateCorrespondingEhepartner($ehepartnerId, $mitgliedId){
	global $libDb;

	if(is_numeric($mitgliedId) && is_numeric($ehepartnerId)){
		$stmt = $libDb->prepare("UPDATE base_person SET heirat_partner=:heirat_partner WHERE id=:id");
		$stmt->bindValue(':heirat_partner', $mitgliedId, PDO::PARAM_INT);
		$stmt->bindValue(':id', $ehepartnerId, PDO::PARAM_INT);
		$stmt->execute();
	}
}
?>