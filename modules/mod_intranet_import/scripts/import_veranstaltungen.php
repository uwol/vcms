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
		$felder = array('datum', 'datum_ende', 'titel', 'spruch', 'beschreibung', 'status', 'ort', 'fb_eventid', 'intern');

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
				$datum = $getData[0];
				$datum_ende = $getData[1];
				$titel = $getData[2];
				$spruch = $getData[3];
				$beschreibung = $getData[4];
				$status = $getData[5];
				$ort = $getData[6];
				$fb_eventid = $getData[7];
				$intern = $getData[8] ?? $libGenericStorage->loadValue('base_core', 'event_preselect_intern');
				if ($anschreiben_zusenden != '0' && $anschreiben_zusenden != '1')
				{
					$anschreiben_zusenden = $libGenericStorage->loadValue('base_core', 'event_preselect_intern');
				}

				$valueArray['datum'] = $libTime->assureMysqlDateTime($datum);
				$valueArray['datum_ende'] = $libTime->assureMysqlDateTime($datum_ende);
				$valueArray['titel'] = $titel;
				$valueArray['spruch'] = $spruch;
				$valueArray['beschreibung'] = $beschreibung;
				$valueArray['status'] = $status;
				$valueArray['ort'] = $ort;
				$valueArray['fb_eventid'] = $fb_eventid;
				$valueArray['intern'] = $intern;

				if($valueArray['datum_ende'] != '0000-00-00 00:00:00' &&
						$valueArray['datum_ende'] != '' &&
						$valueArray['datum_ende'] < $valueArray['datum']){
					$valueArray['datum_ende'] = '';
					$libGlobal->errorTexts[] = 'Das Enddatum liegt vor dem Startdatum für die Veranstaltung: ' .  $titel;
				}

				// If user already exists in the database with the same name
				$query = "SELECT id FROM base_veranstaltung WHERE datum = '" . $valueArray['datum'] .  "'";
				$stmt = $libDb->prepare($query);
				$stmt->execute();

				$row = $stmt->fetch(PDO::FETCH_NUM);
				if ($row && count($row) > 0)
				{
					$valueArray['id'] = $row[0];
					echo "" . $datum . ": " . $titel . " - updating...";
					echo "<script>console.log('{$datum}: {$titel} - updating...');</script>";

					$mgarray = $libDb->updateRow($felder, $valueArray, 'base_veranstaltung', array('id' => $row[0]));

					echo "" . $datum . ": " . $titel . " - updated";
					echo "<script>console.log('{$datum}: {$titel} - updated');</script>";
				}
				else
				{
					echo "" . $vorname . " " . $name . ": adding...";
					echo "<script>console.log('{$vorname} {$name}: adding...');</script>";

					$mgarray = $libDb->insertRow($felder, $valueArray, 'base_veranstaltung', array('id' => ''));

					echo "" . $vorname . " " . $name . ": added";
					echo "<script>console.log('{$vorname} {$name}: added');</script>";
				}
			}

			// Close opened CSV file
			fclose($csvFile);

			header("Location: index.php?pid=intranet_admin_events");
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
