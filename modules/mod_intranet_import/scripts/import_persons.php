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


$libDb->connect();

if($libAuth->isLoggedin()){

    $fileMimes = array(
        'text/x-comma-separated-values',
        'text/comma-separated-values',
        'application/octet-stream',
        'application/vnd.ms-excel',
        'application/x-csv',
        'text/x-csv',
        'text/csv',
        'application/csv',
        'application/excel',
        'application/vnd.msexcel',
        'text/plain'
    );
 
    // Validate selected file is a CSV file or not
    if (!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $fileMimes))
    {
        // Open uploaded CSV file with read-only mode
        $csvFile = fopen($_FILES['file']['tmp_name'], 'r');

        // Skip the first line
        $csvHeader = fgetcsv($csvFile, 10000, ",");
		//Felder in der Personentabelle angeben -> Metadaten
		$felder = array(
			'anrede', 'titel', 'rang', 'vorname', 'praefix', 'name', 'suffix', 'geburtsname',
			'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1',
			'zusatz2', 'strasse2', 'ort2', 'plz2', 'land2',
			'telefon1', 'telefon2', 'mobiltelefon', 'email', 'skype', 'webseite',
			'datum_geburtstag', 'beruf', 'heirat_datum', 'tod_datum', 'tod_ort', 'status',
			'semester_reception', 'semester_promotion', 'semester_philistrierung', 'semester_aufnahme', 'semester_fusion',
			'austritt_datum', 'spitzname', 'leibmitglied',
			'anschreiben_zusenden', 'spendenquittung_zusenden',
			'bemerkung', 'vita',
			'studium', 'linkedin', 'xing',
			'datenschutz_erklaerung_unterschrieben', 'iban', 'einzugsermaechtigung_erteilt', 'gruppe');

		if ($felder !== $csvHeader)
		{
			echo "CSV headers do not conform to the specification!";
			echo "<pre>CSV Header:";
			print_r($csvHeader);
			echo "</pre>";
			echo "<pre>Expected CSV Header:";
			print_r($felder);
			echo "</pre>";
			// Close opened CSV file
			fclose($csvFile);
		}
		else
		{
			// Parse data from CSV file line by line
			while (($getData = fgetcsv($csvFile, 10000, ",")) !== FALSE)
			{
				$valueArray = array();
				$valueArray['id'] = '';

				// Get row data
				$anrede = $getData[0];
				$titel = $getData[1];
				$rang = $getData[2];
				$vorname = $getData[3];
				$praefix = $getData[4];
				$name = $getData[5];
				$suffix = $getData[6];
				$geburtsname = $getData[7];
				$zusatz1 = $getData[8];
				$strasse1 = $getData[9];
				$ort1 = $getData[10];
				$plz1 = $getData[11];
				$land1 = $getData[12];
				$zusatz2 = $getData[13];
				$strasse2 = $getData[14];
				$ort2 = $getData[15];
				$plz2 = $getData[16];
				$land2 = $getData[17];
				$telefon1 = $getData[18];
				$telefon2 = $getData[19];
				$mobiltelefon = $getData[20];
				$email = $getData[21];
				$skype = $getData[22];
				$webseite = $getData[23];
				$datum_geburtstag = $getData[24];
				$beruf = $getData[25];
				$heirat_datum = $getData[26];
				$tod_datum = $getData[27];
				$tod_ort = $getData[28];
				$status = $getData[29];
				$semester_reception = $getData[30];
				$semester_promotion = $getData[31];
				$semester_philistrierung = $getData[32];
				$semester_aufnahme = $getData[33];
				$semester_fusion = $getData[34];
				$austritt_datum = $getData[35];
				$spitzname = $getData[36];
				$leibmitglied = $getData[37];
				$anschreiben_zusenden = $getData[38] ?? '1';
				if ($anschreiben_zusenden != '0' && $anschreiben_zusenden != '1')
				{
					$anschreiben_zusenden = '1';
				}
				$spendenquittung_zusenden = $getData[39] ?? '1';
				if ($spendenquittung_zusenden != '0' && $spendenquittung_zusenden != '1')
				{
					$spendenquittung_zusenden = '1';
				}
				$bemerkung = $getData[40];
				$vita = $getData[41];
				$studium = $getData[42];
				$linkedin = $getData[43];
				$xing = $getData[44];
				$datenschutz_erklaerung_unterschrieben = $getData[45] ?? '0';
				if ($datenschutz_erklaerung_unterschrieben != '0' && $datenschutz_erklaerung_unterschrieben != '1')
				{
					$datenschutz_erklaerung_unterschrieben = '0';
				}
				$iban = $getData[46];
				$einzugsermaechtigung_erteilt = $getData[47] ?? '0';
				if ($einzugsermaechtigung_erteilt != '0' && $einzugsermaechtigung_erteilt != '1')
				{
					$einzugsermaechtigung_erteilt = '0';
				}
				$gruppe = $getData[48];

				// If user already exists in the database with the same name
				$query = "SELECT id FROM base_person WHERE vorname = '" . $vorname . "' AND name = '" . $name . "'";
				$stmt = $libDb->prepare($query);
				$stmt->execute();

				$query2 = "SELECT id FROM base_person WHERE email = '" . $email . "'";
				$stmt2 = $libDb->prepare($query2);
				$stmt2->execute();

				$valueArray['anrede'] = $anrede;
				$valueArray['titel'] = $titel;
				$valueArray['rang'] = $rang;
				$valueArray['vorname'] = $vorname;
				$valueArray['praefix'] = $praefix;
				$valueArray['name'] = $name;
				$valueArray['suffix'] = $suffix;
				$valueArray['geburtsname'] = $geburtsname;
				$valueArray['zusatz1'] = $zusatz1;
				$valueArray['strasse1'] = $strasse1;
				$valueArray['ort1'] = $ort1;
				$valueArray['plz1'] = $plz1;
				$valueArray['land1'] = $land1;
				$valueArray['zusatz2'] = $zusatz2;
				$valueArray['strasse2'] = $strasse2;
				$valueArray['ort2'] = $ort2;
				$valueArray['plz2'] = $plz2;
				$valueArray['land2'] = $land2;
				$valueArray['telefon1'] = $telefon1;
				$valueArray['telefon2'] = $telefon2;
				$valueArray['mobiltelefon'] = $mobiltelefon;
				$valueArray['skype'] = $skype;
				$valueArray['beruf'] = $beruf;
				$valueArray['tod_ort'] = $tod_ort;
				$valueArray['status'] = $status;
				$valueArray['semester_reception'] = $semester_reception;
				$valueArray['semester_promotion'] = $semester_promotion;
				$valueArray['semester_philistrierung'] = $semester_philistrierung;
				$valueArray['semester_aufnahme'] = $semester_aufnahme;
				$valueArray['semester_fusion'] = $semester_fusion;
				$valueArray['spitzname'] = $spitzname;
				$valueArray['leibmitglied'] = $leibmitglied;
				$valueArray['anschreiben_zusenden'] = $anschreiben_zusenden;
				$valueArray['spendenquittung_zusenden'] = $spendenquittung_zusenden;
				$valueArray['bemerkung'] = $bemerkung;
				$valueArray['vita'] = $vita;
				$valueArray['studium'] = $studium;
				$valueArray['linkedin'] = $linkedin;
				$valueArray['xing'] = $xing;
				$valueArray['datenschutz_erklaerung_unterschrieben'] = $datenschutz_erklaerung_unterschrieben;
				$valueArray['iban'] = $iban;
				$valueArray['gruppe'] = $gruppe;
				$valueArray['einzugsermaechtigung_erteilt'] = $einzugsermaechtigung_erteilt;
				$valueArray['email'] = strtolower($email);
				$valueArray['webseite'] = $webseite;
				$valueArray['atum_geburtstag'] = $libTime->assureMysqlDate($datum_geburtstag);
				$valueArray['heirat_datum'] = $libTime->assureMysqlDate($heirat_datum);
				$valueArray['tod_datum'] = $libTime->assureMysqlDate($tod_datum);
				$valueArray['austritt_datum'] = $libTime->assureMysqlDate($austritt_datum);

				$row = $stmt->fetch(PDO::FETCH_NUM);
				$row2 = $stmt2->fetch(PDO::FETCH_NUM);
				if ($row && count($row) > 0)
				{
					$valueArray['id'] = $row[0];
					echo "" . $vorname . " " . $name . ": updating...";
					echo "<script>console.log('{$vorname} {$name}: updating...');</script>";

					$mgarray = $libDb->updateRow($felder, $valueArray, 'base_person', array('id' => $row[0]));

					updateAdresseStand('base_person', 'datum_adresse1_stand', $mgarray['id']);
					updateAdresseStand('base_person', 'datum_adresse2_stand', $mgarray['id']);
					updateGruppeStand($mgarray['id']);

					echo "" . $vorname . " " . $name . ": updated";
					echo "<script>console.log('{$vorname} {$name}: updated');</script>";
				}
				else if ($row2 && count($row2) > 0)
				{
					$valueArray['id'] = $row2[0];
					echo "" . $vorname . " " . $name . ": updating...";
					echo "<script>console.log('{$vorname} {$name}: updating...');</script>";

					$mgarray = $libDb->updateRow($felder, $valueArray, 'base_person', array('id' => $row2[0]));

					updateAdresseStand('base_person', 'datum_adresse1_stand', $mgarray['id']);
					updateAdresseStand('base_person', 'datum_adresse2_stand', $mgarray['id']);
					updateGruppeStand($mgarray['id']);

					echo "" . $vorname . " " . $name . ": updated";
					echo "<script>console.log('{$vorname} {$name}: updated');</script>";
				}
				else
				{
					echo "" . $vorname . " " . $name . ": adding...";
					echo "<script>console.log('{$vorname} {$name}: adding...');</script>";

					$mgarray = $libDb->insertRow($felder, $valueArray, 'base_person', array('id' => ''));

					updateAdresseStand('base_person', 'datum_adresse1_stand', $mgarray['id']);
					updateAdresseStand('base_person', 'datum_adresse2_stand', $mgarray['id']);
					updateGruppeStand($mgarray['id']);

					echo "" . $vorname . " " . $name . ": added";
					echo "<script>console.log('{$vorname} {$name}: added');</script>";
				}
			}

			// Close opened CSV file
			fclose($csvFile);

			header("Location: index.php?pid=intranet_admin_persons");
		}
    }
    else
    {
        echo "Please select a valid CSV file";
    }
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
