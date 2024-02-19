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
	$sql = '';
	$header = '';

	if($_GET['datenart'] == 'mitglieder_export' && (in_array('quaestor', $libAuth->getAemter()) || in_array('ahv_quaestor', $libAuth->getAemter()) || in_array('scriptor', $libAuth->getAemter()) || in_array('ahv_scriptor', $libAuth->getAemter()) || in_array('internetwart', $libAuth->getAemter()) || in_array('datenpflegewart', $libAuth->getAemter()))){
		$sql = "SELECT base_person.id, anrede, titel, rang, vorname, praefix, name, suffix, geburtsname, zusatz1, strasse1, ort1, plz1, land1, datum_adresse1_stand, zusatz2, strasse2, ort2, plz2, land2, datum_adresse2_stand, telefon1, telefon2, mobiltelefon, email, skype, webseite, datum_geburtstag, beruf, heirat_datum, heirat_partner, gruppe, status, semester_reception, semester_promotion, semester_philistrierung, semester_aufnahme, semester_fusion, spitzname, anschreiben_zusenden, spendenquittung_zusenden, bemerkung, vita, studium, linkedin, xing, datenschutz_erklaerung_unterschrieben, iban, einzugsermaechtigung_erteilt, base_region1.bezeichnung, base_region2.bezeichnung FROM base_person LEFT JOIN base_region AS base_region1 ON base_region1.id = base_person.region1 LEFT JOIN base_region AS base_region2 ON base_region2.id = base_person.region2 WHERE (gruppe = 'F' OR gruppe = 'B' OR gruppe = 'P') ORDER BY plz1, name";
		$header = array('id', 'anrede', 'titel', 'rang', 'vorname', 'praefix', 'name', 'suffix', 'geburtsname', 'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1', 'stand1', 'zusatz2', 'strasse2', 'ort2', 'plz2', 'land2', 'stand2', 'telefon1', 'telefon2', 'mobiltelefon', 'email', 'skype', 'webseite', 'datum_geburtstag', 'beruf', 'heirat_datum', 'heirat_partner', 'gruppe', 'status', 'semester_reception', 'semester_promotion', 'semester_philistrierung', 'semester_aufnahme', 'semester_fusion', 'spitzname', 'anschreiben_zusenden', 'spendenquittung_zusenden', 'bemerkung', 'vita', 'studium', 'linkedin', 'xing', 'datenschutz_erklaerung_unterschrieben', 'iban', 'einzugsermaechtigung_erteilt', 'region1', 'region2');
	} elseif($_GET['datenart'] == 'adressverzeichnis'){
		$sql = "SELECT base_person.id, titel, rang, vorname, name, geburtsname, zusatz1, strasse1, ort1, plz1, land1, telefon1, telefon2, mobiltelefon, email, webseite, datum_geburtstag, beruf, gruppe, status, semester_reception, semester_philistrierung, studium, linkedin, xing FROM base_person WHERE (gruppe = 'F' OR gruppe = 'B' OR gruppe = 'P') ORDER BY name, vorname";
	} elseif($_GET['datenart'] == 'mitglieder_anschreiben'){
		$sql = "SELECT anrede, titel, rang, vorname, praefix, name, suffix, zusatz1, strasse1, ort1, plz1, land1, datum_adresse1_stand, zusatz2, strasse2, ort2, plz2, land2, datum_adresse2_stand, email, gruppe, status, base_region1.bezeichnung, base_region2.bezeichnung FROM base_person LEFT JOIN base_region AS base_region1 ON base_region1.id = base_person.region1 LEFT JOIN base_region AS base_region2 ON base_region2.id = base_person.region2 WHERE (gruppe = 'F' OR gruppe = 'B' OR gruppe = 'P') AND anschreiben_zusenden=1 ORDER BY plz1, name";
		$header = array('anrede', 'titel', 'rang', 'vorname', 'praefix', 'name', 'suffix', 'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1', 'stand1', 'zusatz2', 'strasse2', 'ort2', 'plz2', 'land2', 'stand2', 'email', 'gruppe', 'status', 'region1', 'region2');
	} elseif($_GET['datenart'] == 'mitglieder_spendenquittung'){
		$sql = "SELECT anrede, titel, rang, vorname, praefix, name, suffix, zusatz1, strasse1, ort1, plz1, land1, datum_adresse1_stand, zusatz2, strasse2, ort2, plz2, land2, datum_adresse2_stand, email, gruppe, status, base_region1.bezeichnung, base_region2.bezeichnung FROM base_person LEFT JOIN base_region AS base_region1 ON base_region1.id = base_person.region1 LEFT JOIN base_region AS base_region2 ON base_region2.id = base_person.region2 WHERE (gruppe = 'F' OR gruppe = 'B' OR gruppe = 'P') AND spendenquittung_zusenden=1 ORDER BY plz1, name";
		$header = array('anrede', 'titel', 'rang', 'vorname', 'praefix', 'name', 'suffix', 'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1', 'stand1', 'zusatz2', 'strasse2', 'ort2', 'plz2', 'land2', 'stand2', 'email', 'gruppe', 'status', 'region1', 'region2');
	} elseif($_GET['datenart'] == 'damenflor_anschreiben'){
		$sql = "SELECT anrede, titel, rang, vorname, praefix, name, suffix, zusatz1, strasse1, ort1, plz1, land1, datum_adresse1_stand, zusatz2, strasse2, ort2, plz2, land2, datum_adresse2_stand, email, gruppe, status, base_region1.bezeichnung, base_region2.bezeichnung FROM base_person LEFT JOIN base_region AS base_region1 ON base_region1.id = base_person.region1 LEFT JOIN base_region AS base_region2 ON base_region2.id = base_person.region2 WHERE (gruppe = 'C' OR gruppe = 'G' OR gruppe = 'W') AND anschreiben_zusenden=1 ORDER BY plz1, name";
		$header = array('anrede', 'titel', 'rang', 'vorname', 'praefix', 'name', 'suffix', 'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1', 'stand1', 'zusatz2', 'strasse2', 'ort2', 'plz2', 'land2', 'stand2', 'email', 'gruppe', 'status', 'region1', 'region2');
	} elseif($_GET['datenart'] == 'damenflor_spendenquittung'){
		$sql = "SELECT anrede, titel, rang, vorname, praefix, name, suffix, zusatz1, strasse1, ort1, plz1, land1, datum_adresse1_stand, zusatz2, strasse2, ort2, plz2, land2, datum_adresse2_stand, email, gruppe, status, base_region1.bezeichnung, base_region2.bezeichnung FROM base_person LEFT JOIN base_region AS base_region1 ON base_region1.id = base_person.region1 LEFT JOIN base_region AS base_region2 ON base_region2.id = base_person.region2 WHERE (gruppe = 'C' OR gruppe = 'G' OR gruppe = 'W') AND spendenquittung_zusenden=1 ORDER BY plz1, name";
		$header = array('anrede', 'titel', 'rang', 'vorname', 'praefix', 'name', 'suffix', 'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1', 'stand1', 'zusatz2', 'strasse2', 'ort2', 'plz2', 'land2', 'stand2', 'email', 'gruppe', 'status', 'region1', 'region2');
	} elseif($_GET['datenart'] == 'vereine'){
		$sql = "SELECT name, titel, rang, dachverband, zusatz1, strasse1, ort1, plz1, land1, aktivitas, ahahschaft FROM base_verein WHERE anschreiben_zusenden=1 ORDER BY plz1";
		$header = array('name', 'titel', 'rang', 'dachverband', 'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1', 'aktivitas', 'ahahschaft');
	} elseif($_GET['datenart'] == 'vips'){
		$sql = "SELECT anrede, titel, rang, vorname, praefix, name, suffix, zusatz1, strasse1, ort1, plz1, land1, datum_adresse1_stand FROM base_vip ORDER BY plz1, name";
		$header = array('anrede', 'titel', 'rang', 'vorname', 'praefix', 'name', 'suffix', 'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1', 'stand1');
	}

	if($sql != '' && $header != '' && is_array($header)){
		$stmt = $libDb->prepare($sql);

		$table = new vcms\LibTable($libDb);
		$table->addHeader($header);
		$table->addTableByStatement($stmt);

		if(isset($_GET['type']) && $_GET['type'] == 'csv'){
			$table->writeContentAsCSV($_GET['datenart']. '.csv');
		} else {
			$table->writeContentAsHtmlTable($_GET['datenart']. '.html');
		}
	} elseif($sql != '' && isset($_GET['datenart']) && $_GET['datenart'] == 'adressverzeichnis'){
		global $libFilesystem;
		$stmt = $libDb->prepare($sql);

		$rowindex = 0;
		$stmt->execute();

		$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A6', 'default_font_size' => 9, 'default_font' => 'dejavusans']);
		$mpdf->SetTitle('Mitglieder-Verzeichnis '.date("Y-m-d")); // :TODO: Verbindungsnamen hinzufuegen
		$mpdf->SetAuthor('Verbindung'); // :TODO: Verbindungsnamen hinzufuegen
		$mpdf->defaultfooterline = 0;
		$mpdf->defaultfooterfontstyle = 'normal';
		$mpdf->mirrorMargins = 1;
		//$mpdf->simpleTables = true;
		//$mpdf->packTableData = true;
		$mpdf->keep_table_proportions = true;
		//$mpdf->shrink_tables_to_fit=1;
		$mpdf->WriteHTML('span { font-size:9pt; line-height: 1.2; }',\Mpdf\HTMLParserMode::HEADER_CSS);
		$mpdf->WriteHTML('<div><img width="100%" src="'.$libFilesystem->getAbsolutePath('custom/styles/adressverzeichnis_cover.jpg').'"></div>');
		$mpdf->WriteHTML('<p style="font-size:12pt; line-height: 1.4;" align="center"><b>Mitglieder-Verzeichnis</b></p>');
		$mpdf->AddPage();
		$mpdf->AddPage();
		$mpdf->WriteHTML('<p style="font-size:12pt; line-height: 1.4;" align="center">Datenstand: '.date("Y-m-d").'</p>');
		$mpdf->AddPage();
		$mpdf->setFooter('{PAGENO}');
		/*$mpdf->PageNumSubstitutions[] = [
			'from' => 1,
			'reset' => 0,
			'type' => '1',
			'suppress' => 'on'
		];*/
		while($row = $stmt->fetch(PDO::FETCH_NUM)){
			if(is_array($row) && count($row) > 0){
				$valueArray = array();
				$valueArray['id'] = $libString->xmlentities($row[0]);
				// 'https://'.$libGlobal->getSiteUrlAuthority().'/'
				//$valueArray['bild'] = 'https://'.$libGlobal->getSiteUrlAuthority().'/api.php?iid=base_intranet_personenbild&amp;id='.$valueArray['id'];
				//$valueArray['bild'] = 'api.php?iid=base_intranet_personenbild&amp;id='.$valueArray['id'];
				$valueArray['bild'] = $libFilesystem->getAbsolutePath('custom/intranet/mitgliederfotos/'.$valueArray['id'].'.jpg');
				if (!is_file($valueArray['bild'])) {
					$valueArray['bild'] = $libFilesystem->getAbsolutePath('custom/intranet/mitgliederfotos/blank.jpg');
				}

				$valueArray['titel'] = $libString->xmlentities($row[1]);
				$valueArray['rang'] = $libString->xmlentities($row[2]);
				if($valueArray['rang'] != '') {
					$valueArray['rang'] = ' '.$valueArray['rang'];
				}
				$valueArray['vorname'] = $libString->xmlentities($row[3]);
				$valueArray['name'] = $libString->xmlentities($row[4]);
				$valueArray['geburtsname'] = $libString->xmlentities($row[5]);
				if($valueArray['geburtsname'] != '') {
					$valueArray['geburtsname'] = ' ('.$valueArray['geburtsname'].')';
				}
				$valueArray['zusatz1'] = $libString->xmlentities($row[6]);
				$valueArray['strasse1'] = $libString->xmlentities($row[7]);
				$valueArray['ort1'] = $libString->xmlentities($row[8]);
				$valueArray['plz1'] = $libString->xmlentities($row[9]);
				$valueArray['land1'] = $libString->xmlentities($row[10]);
				$valueArray['telefon1'] = $libString->xmlentities($row[11]);
				$valueArray['telefon2'] = $libString->xmlentities($row[12]);
				$valueArray['mobiltelefon'] = $libString->xmlentities($row[13]);
				$valueArray['email'] = strtolower($libString->xmlentities($row[14]));
				$valueArray['webseite'] = $libString->xmlentities($row[15]);
				$valueArray['datum_geburtstag'] = $libTime->assureMysqlDate($libString->xmlentities($row[16]));
				$valueArray['beruf'] = $libString->xmlentities($row[17]);
				$valueArray['gruppe'] = $libString->xmlentities($row[18]);
				$valueArray['status'] = $libString->xmlentities($row[19]);
				if($valueArray['status'] != '') {
					$valueArray['status'] = ', '.$valueArray['status'];
				}
				$valueArray['semester_reception'] = $libString->xmlentities($row[20]);
				$valueArray['semester_philistrierung'] = $libString->xmlentities($row[21]);
				if($valueArray['semester_philistrierung'] != '') {
					$valueArray['semester_philistrierung'] = ', '.$valueArray['semester_philistrierung'];
				}
				$valueArray['studium'] = $libString->xmlentities($row[22]);
				if($valueArray['studium'] != '') {
					$valueArray['studium'] = $valueArray['studium'].' / ';
				}
				$valueArray['linkedin'] = $libString->xmlentities($row[23]);
				$valueArray['xing'] = $libString->xmlentities($row[24]);

				//$mpdf->WriteHTML('<div>');
				//$mpdf->WriteHTML('<columns column-count="2" vAlign="J" column-gap="5" />');
				$mpdf->WriteHTML('<table cellpadding="2px" autosize="1" border="0" width="100%" style="padding-bottom: 20px;"><tr><td width="30mm">');
				$mpdf->WriteHTML('<div style="float: left;"><img width="26mm" src="'.$valueArray['bild'].'" alt="'.$valueArray['bild'].'" title="'.$valueArray['bild'].'"></div></td>');
				//$mpdf->WriteHTML('<columnbreak />');
				$mpdf->WriteHTML('<td style="font-size:9pt; line-height: 1.4;" width="100%">');
				$mpdf->WriteHTML('<div style="float: right;">');
				if($valueArray['titel'] != '' || $valueArray['rang'] != '') {
					$mpdf->WriteHTML('<span style="font-size:8pt; line-height: 1.2;">'.$valueArray['titel'].''.$valueArray['rang'].'</span><br />');
				}
				$mpdf->WriteHTML('<span style="font-size:12pt; line-height: 1.5;"><b>'.$valueArray['vorname'].' '.$valueArray['name'].$valueArray['geburtsname'].'</b></span><br />');
				$mpdf->WriteHTML('<span style="font-size:8pt; line-height: 1.2;">('.$valueArray['studium'].''.$valueArray['beruf'].')</span><br />');
				$mpdf->WriteHTML('<span style="font-size:8pt; line-height: 1.2;">* '.$valueArray['datum_geburtstag'].'</span><br />');
				$mpdf->WriteHTML('<span style="font-size:8pt; line-height: 1.2;">'.$valueArray['semester_reception'].''.$valueArray['semester_philistrierung'].''.$valueArray['status'].'</span><br />');
				if($valueArray['email'] != '') {
					$mpdf->WriteHTML('<span>'.$valueArray['email'].'</span><br />');
				}
				if($valueArray['webseite'] != '') {
					$mpdf->WriteHTML('<span style="font-size:8pt; line-height: 1.2;">'.$valueArray['webseite'].'</span><br />');
				}
				if($valueArray['linkedin'] != '') {
					$mpdf->WriteHTML('<span style="font-size:8pt; line-height: 1.2;">'.$valueArray['linkedin'].'</span><br />');
				}
				if($valueArray['xing'] != '') {
					$mpdf->WriteHTML('<span style="font-size:8pt; line-height: 1.2;">'.$valueArray['xing'].'</span><br />');
				}
				$mpdf->WriteHTML('<span>'.$valueArray['mobiltelefon'].' '.$valueArray['telefon1'].' '.$valueArray['telefon2'].'</span><br />');
				if($valueArray['zusatz1'] != '') {
					$mpdf->WriteHTML('<span>'.$valueArray['zusatz1'].'</span><br />');
				}
				$mpdf->WriteHTML('<span>'.$valueArray['strasse1'].'</span><br />');
				$mpdf->WriteHTML('<span>'.$valueArray['plz1'].' '.$valueArray['ort1'].'/'.$valueArray['land1'].'</span><br />');
				$mpdf->WriteHTML('</div>');
				$mpdf->WriteHTML('</td></tr></table>');
				//$mpdf->WriteHTML('<hr />');
				//mpdf->WriteHTML('</div>');
			}
			$rowindex++;
		}

		// Output a PDF file directly to the browser
		$mpdf->Output('Mitglieder-Verzeichnis_'.date("Y-m-d").'.pdf', 'D');
	}
}
