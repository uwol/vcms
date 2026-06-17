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

	if($_GET['datenart'] == 'mitglieder_export'){
		$sql = "SELECT base_person.id, anrede, titel, rang, vorname, praefix, name, suffix, geburtsname, zusatz1, strasse1, ort1, plz1, land1, datum_adresse1_stand, zusatz2, strasse2, ort2, plz2, land2, datum_adresse2_stand, telefon1, telefon2, mobiltelefon, email, skype, webseite, datum_geburtstag, beruf, heirat_datum, heirat_partner, gruppe, status, semester_reception, semester_promotion, semester_philistrierung, semester_aufnahme, semester_fusion, spitzname, anschreiben_zusenden, spendenquittung_zusenden, bemerkung, vita, studium, linkedin, xing, datenschutz_erklaerung_unterschrieben, iban, einzugsermaechtigung_erteilt, base_region1.bezeichnung, base_region2.bezeichnung FROM base_person LEFT JOIN base_region AS base_region1 ON base_region1.id = base_person.region1 LEFT JOIN base_region AS base_region2 ON base_region2.id = base_person.region2 WHERE (gruppe = 'F' OR gruppe = 'B' OR gruppe = 'P') ORDER BY plz1, name";
		$header = array('id', 'anrede', 'titel', 'rang', 'vorname', 'praefix', 'name', 'suffix', 'geburtsname', 'zusatz1', 'strasse1', 'ort1', 'plz1', 'land1', 'stand1', 'zusatz2', 'strasse2', 'ort2', 'plz2', 'land2', 'stand2', 'telefon1', 'telefon2', 'mobiltelefon', 'email', 'skype', 'webseite', 'datum_geburtstag', 'beruf', 'heirat_datum', 'heirat_partner', 'gruppe', 'status', 'semester_reception', 'semester_promotion', 'semester_philistrierung', 'semester_aufnahme', 'semester_fusion', 'spitzname', 'anschreiben_zusenden', 'spendenquittung_zusenden', 'bemerkung', 'vita', 'studium', 'linkedin', 'xing', 'datenschutz_erklaerung_unterschrieben', 'iban', 'einzugsermaechtigung_erteilt', 'region1', 'region2');
	} elseif($_GET['datenart'] == 'adressverzeichnis'){
		$sql = "SELECT base_person.id, titel, rang, vorname, name, geburtsname, zusatz1, strasse1, ort1, plz1, land1, telefon1, telefon2, mobiltelefon, email, webseite, datum_geburtstag, beruf, gruppe, status, semester_reception, semester_philistrierung, studium, linkedin, xing, heirat_partner, semester_aufnahme FROM base_person WHERE (gruppe = 'F' OR gruppe = 'B' OR gruppe = 'P' OR gruppe = 'G') ORDER BY name, vorname";
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
	} elseif(isset($_GET['datenart']) && $_GET['datenart'] == 'adressverzeichnis'){
		global $libFilesystem;
		$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A6', 'default_font_size' => 9, 'default_font' => 'dejavusans']);
		$mpdf->SetTitle($libConfig->verbindungName.' - Mitgliederverzeichnis - '.date("Y-m-d"));
		$mpdf->SetAuthor($libConfig->verbindungName);
		$mpdf->defaultfooterline = 0;
		$mpdf->defaultfooterfontstyle = 'normal';
		$mpdf->mirrorMargins = 1;
		//$mpdf->simpleTables = true;
		//$mpdf->packTableData = true;
		$mpdf->keep_table_proportions = true;
		//$mpdf->shrink_tables_to_fit=1;
		$mpdf->WriteHTML('span { font-size:9pt; line-height: 1.2; }',\Mpdf\HTMLParserMode::HEADER_CSS);
		$mpdf->WriteHTML('<div><img width="100%" src="'.$libFilesystem->getAbsolutePath('custom/styles/adressverzeichnis_cover.jpg').'" /></div>');
		$mpdf->WriteHTML('<p style="font-size:12pt; line-height: 1.1;" align="center"><b>Mitgliederverzeichnis</b></p>');
		$mpdf->AddPage();
		$mpdf->WriteHTML('<p style="font-size:12pt; line-height: 1.4;" align="center">Reihenfolge: </p><ul>');
		$mpdf->WriteHTML('<li>Philister</li>');
		$mpdf->WriteHTML('<li>Aktive</li>');
		if(isset($_GET['gruppen']) && str_contains($_GET['gruppen'], "Y")){
			$mpdf->WriteHTML('<li>Vereinsfreunde</li>');
		}
		if(isset($_GET['gruppen']) && str_contains($_GET['gruppen'], "T")){
			$mpdf->WriteHTML('<li>Verstorbene Bundesbrüder</li>');
		}
		if(isset($_GET['gruppen']) && str_contains($_GET['gruppen'], "C")){
			$mpdf->WriteHTML('<li>Couleurdamen</li>');
		}
		if(isset($_GET['gruppen']) && str_contains($_GET['gruppen'], "G")){
			$mpdf->WriteHTML('<li>Gattinen</li>');
		}
		if(isset($_GET['gruppen']) && str_contains($_GET['gruppen'], "W")){
			$mpdf->WriteHTML('<li>Witwen</li>');
		}
		if(isset($_GET['gruppen']) && str_contains($_GET['gruppen'], "V")){
			$mpdf->WriteHTML('<li>Verstorbene Gattinen</li>');
		}
		$mpdf->WriteHTML('</ul>');
		$mpdf->WriteHTML('<p></p>');
		$mpdf->WriteHTML('<p></p>');
		$mpdf->WriteHTML('<p></p>');
		$mpdf->WriteHTML('<p></p>');
		$mpdf->WriteHTML('<p></p>');
		$mpdf->WriteHTML('<p style="font-size:9pt; line-height: 1.4;" align="center">Datenstand: '.date("Y-m-d").'</p>');
		$mpdf->AddPage();
		$mpdf->setFooter('{PAGENO}');
		/*$mpdf->PageNumSubstitutions[] = [
			'from' => 1,
			'reset' => 0,
			'type' => '1',
			'suppress' => 'on'
		];*/

		$mpdf->WriteHTML('<p style="font-size:12pt; line-height: 1.4;" align="center">Die Philister</p>');
		$ahah_sql = "SELECT base_person.id, titel, rang, vorname, name, geburtsname, zusatz1, strasse1, ort1, plz1, land1, telefon1, telefon2, mobiltelefon, email, webseite, datum_geburtstag, beruf, gruppe, status, semester_reception, semester_philistrierung, studium, linkedin, xing, heirat_partner, semester_aufnahme FROM base_person WHERE gruppe = 'P' ORDER BY name, vorname";
		$ahah_stmt = $libDb->prepare($ahah_sql);
		$ahah_stmt->execute();
		add_group($ahah_stmt, $mpdf);
		$mpdf->AddPage();

		$mpdf->WriteHTML('<p style="font-size:12pt; line-height: 1.4;" align="center">Die Aktiven</p>');
		$aktive_sql = "SELECT base_person.id, titel, rang, vorname, name, geburtsname, zusatz1, strasse1, ort1, plz1, land1, telefon1, telefon2, mobiltelefon, email, webseite, datum_geburtstag, beruf, gruppe, status, semester_reception, semester_philistrierung, studium, linkedin, xing, heirat_partner, semester_aufnahme FROM base_person WHERE (gruppe = 'F' OR gruppe = 'B') ORDER BY name, vorname";
		$aktive_stmt = $libDb->prepare($aktive_sql);
		$aktive_stmt->execute();
		add_group($aktive_stmt, $mpdf);

		if(isset($_GET['gruppen']) && str_contains($_GET['gruppen'], "Y")){
			$mpdf->AddPage();
			$mpdf->WriteHTML('<p style="font-size:12pt; line-height: 1.4;" align="center">Die Vereinsfreunde</p>');
			$vf_sql = "SELECT base_person.id, titel, rang, vorname, name, geburtsname, zusatz1, strasse1, ort1, plz1, land1, telefon1, telefon2, mobiltelefon, email, webseite, datum_geburtstag, beruf, gruppe, status, semester_reception, semester_philistrierung, studium, linkedin, xing, heirat_partner, semester_aufnahme FROM base_person WHERE gruppe = 'Y' ORDER BY name, vorname";
			$vf_stmt = $libDb->prepare($vf_sql);
			$vf_stmt->execute();
			add_group($vf_stmt, $mpdf);
		}

		if(isset($_GET['gruppen']) && str_contains($_GET['gruppen'], "T")){
			$mpdf->AddPage();
			$mpdf->WriteHTML('<p style="font-size:12pt; line-height: 1.4;" align="center">Unsere Verstorbenen Bundesbrüder</p>');
			$ahah_sql = "SELECT base_person.id, titel, rang, vorname, name, geburtsname, zusatz1, strasse1, ort1, plz1, land1, telefon1, telefon2, mobiltelefon, email, webseite, datum_geburtstag, beruf, gruppe, status, semester_reception, semester_philistrierung, studium, linkedin, xing, heirat_partner, semester_aufnahme, tod_datum FROM base_person WHERE gruppe = 'T' ORDER BY name, vorname";
			$ahah_stmt = $libDb->prepare($ahah_sql);
			$ahah_stmt->execute();
			add_group($ahah_stmt, $mpdf);
		}

		if(isset($_GET['gruppen']) && str_contains($_GET['gruppen'], "C")){
			$mpdf->AddPage();
			$mpdf->WriteHTML('<p style="font-size:12pt; line-height: 1.4;" align="center">Die Couleurdamen</p>');
			$cdamen_sql = "SELECT base_person.id, titel, rang, vorname, name, geburtsname, zusatz1, strasse1, ort1, plz1, land1, telefon1, telefon2, mobiltelefon, email, webseite, datum_geburtstag, beruf, gruppe, status, semester_reception, semester_philistrierung, studium, linkedin, xing, heirat_partner, semester_aufnahme FROM base_person WHERE gruppe = 'C' ORDER BY name, vorname";
			$cdamen_stmt = $libDb->prepare($cdamen_sql);
			$cdamen_stmt->execute();
			add_group($cdamen_stmt, $mpdf);
		}

		if(isset($_GET['gruppen']) && str_contains($_GET['gruppen'], "G")){
			$mpdf->AddPage();
			$mpdf->WriteHTML('<p style="font-size:12pt; line-height: 1.4;" align="center">Die Gattinen</p>');
			$damen_sql = "SELECT base_person.id, titel, rang, vorname, name, geburtsname, zusatz1, strasse1, ort1, plz1, land1, telefon1, telefon2, mobiltelefon, email, webseite, datum_geburtstag, beruf, gruppe, status, semester_reception, semester_philistrierung, studium, linkedin, xing, heirat_partner, semester_aufnahme FROM base_person WHERE gruppe = 'G' ORDER BY name, vorname";
			$damen_stmt = $libDb->prepare($damen_sql);
			$damen_stmt->execute();
			add_group($damen_stmt, $mpdf);
		}

		if(isset($_GET['gruppen']) && str_contains($_GET['gruppen'], "W")){
			$mpdf->AddPage();
			$mpdf->WriteHTML('<p style="font-size:12pt; line-height: 1.4;" align="center">Die Witwen</p>');
			$witwen_sql = "SELECT base_person.id, titel, rang, vorname, name, geburtsname, zusatz1, strasse1, ort1, plz1, land1, telefon1, telefon2, mobiltelefon, email, webseite, datum_geburtstag, beruf, gruppe, status, semester_reception, semester_philistrierung, studium, linkedin, xing, heirat_partner, semester_aufnahme FROM base_person WHERE gruppe = 'W' ORDER BY name, vorname";
			$witwen_stmt = $libDb->prepare($witwen_sql);
			$witwen_stmt->execute();
			add_group($witwen_stmt, $mpdf);
		}

		if(isset($_GET['gruppen']) && str_contains($_GET['gruppen'], "V")){
			$mpdf->AddPage();
			$mpdf->WriteHTML('<p style="font-size:12pt; line-height: 1.4;" align="center">Die verstorbenen Gattinen</p>');
			$vdamen_sql = "SELECT base_person.id, titel, rang, vorname, name, geburtsname, zusatz1, strasse1, ort1, plz1, land1, telefon1, telefon2, mobiltelefon, email, webseite, datum_geburtstag, beruf, gruppe, status, semester_reception, semester_philistrierung, studium, linkedin, xing, heirat_partner, semester_aufnahme, tod_datum FROM base_person WHERE gruppe = 'V' ORDER BY name, vorname";
			$vdamen_stmt = $libDb->prepare($vdamen_sql);
			$vdamen_stmt->execute();
			add_group($vdamen_stmt, $mpdf);
		}

		// Output a PDF file directly to the browser
		$mpdf->Output(str_replace(' ', '_', $libConfig->verbindungName).'_Mitgliederverzeichnis_'.date("Y-m-d").'.pdf', 'D');
	}
}


function add_group($stmt, $mpdf) 
{
	global $libFilesystem;
	global $libString;
	global $libTime;
	global $libDb;
	$rowindex = 0;
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
			$valueArray['semester_reception'] = $libString->xmlentities($row[20]);
			$valueArray['semester_philistrierung'] = $libString->xmlentities($row[21]);
			$valueArray['studium'] = $libString->xmlentities($row[22]);
			$valueArray['linkedin'] = $libString->xmlentities($row[23]);
			$valueArray['xing'] = $libString->xmlentities($row[24]);

			$valueArray['heirat_partner'] = $libString->xmlentities($row[25]);
			# Get the name of the partner
			if($valueArray['heirat_partner'] != '') {
				$partner_sql = 'SELECT vorname, name FROM base_person WHERE base_person.id='.$valueArray['heirat_partner'];
				$partner_stmt = $libDb->prepare($partner_sql);
				$partner_stmt->execute();
				$partner_row = $partner_stmt->fetch(PDO::FETCH_NUM);
				$valueArray['heirat_partner'] = $libString->xmlentities($partner_row[0]).' '.$libString->xmlentities($partner_row[1]);
			}
	
			$valueArray['semester_aufnahme'] = $libString->xmlentities($row[26]);
			if ($valueArray['gruppe'] == 'T' || $valueArray['gruppe'] == 'V') {
				$valueArray['tod_datum'] = $libString->xmlentities($row[27]);
			}

			//$mpdf->WriteHTML('<div>');
			//$mpdf->WriteHTML('<columns column-count="2" vAlign="J" column-gap="5" />');
			if ($valueArray['gruppe'] == 'P' ||  $valueArray['gruppe'] == 'B' ||  $valueArray['gruppe'] == 'F' ||  $valueArray['gruppe'] == 'Y') {
				$mpdf->WriteHTML('<table cellpadding="2px" autosize="1" border="0" width="100%" style="padding-bottom: 20px;"><tr><td width="30mm">');
				$mpdf->WriteHTML('<div style="float: left;"><img width="26mm" src="'.$valueArray['bild'].'" alt="'.$valueArray['bild'].'" title="'.$valueArray['bild'].'"></div></td>');
			} else {
				$mpdf->WriteHTML('<table cellpadding="2px" autosize="1" border="0" width="100%" style="padding-bottom: 15px;"><tr><td width="20mm">');
				$mpdf->WriteHTML('<div style="float: left;"><img width="16mm" src="'.$valueArray['bild'].'" alt="'.$valueArray['bild'].'" title="'.$valueArray['bild'].'"></div></td>');
			}
			//$mpdf->WriteHTML('<columnbreak />');
			$mpdf->WriteHTML('<td style="font-size:9pt; line-height: 1.3;" width="100%">');
			$mpdf->WriteHTML('<div style="float: right;">');

			# Titel + Rang
			if($valueArray['titel'] != '' && $valueArray['rang'] != '') {
				$mpdf->WriteHTML('<span style="font-size:9pt; line-height: 1.3;">'.$valueArray['titel'].' '.$valueArray['rang'].'</span><br />');
			} else if($valueArray['titel'] != '' || $valueArray['rang'] != '') {
				$mpdf->WriteHTML('<span style="font-size:9pt; line-height: 1.3;">'.$valueArray['titel'].''.$valueArray['rang'].'</span><br />');
			}

			# Vorname + Nachname + Geburtsname
			$mpdf->WriteHTML('<span style="font-size:12pt; line-height: 1.5;"><b>'.$valueArray['vorname'].' '.$valueArray['name'].$valueArray['geburtsname'].'</b></span><br />');

			# Ehepartner
			if ($valueArray['gruppe'] == 'T' && $valueArray['heirat_partner'] != '') {
				$mpdf->WriteHTML('<span style="font-size:9pt; line-height: 1.3;"><i>Witwe:</i> '.$valueArray['heirat_partner'].'</span><br />');
			} else if($valueArray['heirat_partner'] != '') {
				$mpdf->WriteHTML('<span style="font-size:9pt; line-height: 1.3;"><i>Ehepartner:</i> '.$valueArray['heirat_partner'].'</span><br />');
			}

			# Studium + Beruf
			if($valueArray['studium'] != '' || $valueArray['beruf'] != '') {
				$mpdf->WriteHTML('<span style="font-size:9pt; line-height: 1.3;">(');
				if($valueArray['studium'] != '') {
					$mpdf->WriteHTML($valueArray['studium']);
				}
				if($valueArray['studium'] != '' && $valueArray['beruf'] != '') {
					$mpdf->WriteHTML(' / ');
				}
				if($valueArray['beruf'] != '') {
					$mpdf->WriteHTML($valueArray['beruf']);
				}
				$mpdf->WriteHTML(')</span><br />');
			}

			# Geburtstag
			if($valueArray['datum_geburtstag'] != '') {
				$mpdf->WriteHTML('<span style="font-family: fontawesome;">&#xf1fd;</span>&nbsp;');
				$mpdf->WriteHTML('<span style="font-size:9pt; line-height: 1.3;">'.$valueArray['datum_geburtstag'].'</span><br />');
			}
			if (($valueArray['gruppe'] == 'T' || $valueArray['gruppe'] == 'V') && $valueArray['tod_datum'] != '') {
				$mpdf->WriteHTML('<span style="font-size:9pt; line-height: 1.3;"><svg height="9pt" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free v5.15.4 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M352 128h-96V32c0-17.67-14.33-32-32-32h-64c-17.67 0-32 14.33-32 32v96H32c-17.67 0-32 14.33-32 32v64c0 17.67 14.33 32 32 32h96v224c0 17.67 14.33 32 32 32h64c17.67 0 32-14.33 32-32V256h96c17.67 0 32-14.33 32-32v-64c0-17.67-14.33-32-32-32z"/></svg>&nbsp;&nbsp;'.$valueArray['tod_datum'].'</span><br />');
			}

			# Rezeption + Philistrierung/Aufnahme
			if($valueArray['semester_reception'] != '' || $valueArray['semester_philistrierung'] != '' || $valueArray['semester_aufnahme'] != '' || $valueArray['status'] != '') {
				$mpdf->WriteHTML('<span style="font-size:9pt; line-height: 1.3;">');
				if($valueArray['semester_reception'] != '') {
					$mpdf->WriteHTML('<i>Rez:</i> '.$valueArray['semester_reception']);
				}
				if($valueArray['semester_reception'] != '' && ($valueArray['semester_philistrierung'] != '' || $valueArray['semester_aufnahme'] != '')) {
					$mpdf->WriteHTML(', ');
				}
				if($valueArray['semester_philistrierung'] != '') {
					$mpdf->WriteHTML('<i>Phil:</i> '.$valueArray['semester_philistrierung']);
				} else if($valueArray['semester_aufnahme'] != '') {
					$mpdf->WriteHTML('<i>Auf:</i> '.$valueArray['semester_aufnahme']);
				}
				if($valueArray['status'] != '') {
					if($valueArray['semester_reception'] != '' || $valueArray['semester_philistrierung'] != '' || $valueArray['semester_aufnahme'] != '') {
						$mpdf->WriteHTML(' ('.$valueArray['status'].')');
					} else {
						$mpdf->WriteHTML($valueArray['status']);
					}
				}
			$mpdf->WriteHTML('</span><br />');
			}

			# Elektronische Kommunikation
			if($valueArray['gruppe'] != 'T' && $valueArray['gruppe'] != 'V' && $valueArray['email'] != '') {
				$mpdf->WriteHTML('<span style="font-family: fontawesome;">&#xf003;</span>&nbsp;');
				$mpdf->WriteHTML('<span><a style="color: #000000; text-decoration: none;" href="mailto:'.$valueArray['email'].'">'.$valueArray['email'].'</a></span><br />');
			}
			if($valueArray['gruppe'] != 'T' && $valueArray['gruppe'] != 'V' && $valueArray['webseite'] != '') {
				$valueArray['webseite'] = str_replace('https://', '', $valueArray['webseite']);
				$valueArray['webseite'] = str_replace('http://', '', $valueArray['webseite']);
				$mpdf->WriteHTML('<span style="font-family: fontawesome;">&#xf0ac;</span>&nbsp;');
				$mpdf->WriteHTML('<span><a style="color: #000000; text-decoration: none; font-size:9pt; line-height: 1.3;" href="https://'.$valueArray['webseite'].'">'.$valueArray['webseite'].'</a></span><br />');
			}
			if($valueArray['gruppe'] != 'T' && $valueArray['gruppe'] != 'V' && $valueArray['linkedin'] != '') {
				$valueArray['linkedin'] = str_replace('https://www.linkedin.com/in/', '', $valueArray['linkedin']);
				$valueArray['linkedin'] = str_replace('/', '', $valueArray['linkedin']);
				$mpdf->WriteHTML('<span style="font-family: fontawesome;">&#xf08c;</span>&nbsp;');
				$mpdf->WriteHTML('<span><a style="color: #000000; text-decoration: none; font-size:9pt; line-height: 1.3;" href="https://www.linkedin.com/in/'.$valueArray['linkedin'].'">'.$valueArray['linkedin'].'</a></span><br />');
			}
			if($valueArray['gruppe'] != 'T' && $valueArray['gruppe'] != 'V' && $valueArray['xing'] != '') {
				$valueArray['xing'] = str_replace('https://www.xing.com/profile/', '', $valueArray['xing']);
				$valueArray['xing'] = str_replace('/', '', $valueArray['xing']);
				$mpdf->WriteHTML('<span style="font-family: fontawesome;">&#xf169;</span>&nbsp;');
				$mpdf->WriteHTML('<span><a style="color: #000000; text-decoration: none; font-size:9pt; line-height: 1.3;" href="https://www.xing.com/profile/'.$valueArray['xing'].'">'.$valueArray['xing'].'</a></span><br />');
			}

			# Mobiltelefone
			if($valueArray['gruppe'] != 'T' && $valueArray['gruppe'] != 'V' && ($valueArray['mobiltelefon'] != '' && !str_contains($valueArray['mobiltelefon'], 'stand') && !str_contains($valueArray['mobiltelefon'], 'Stand'))) {
				$mpdf->WriteHTML('<span style="font-family: fontawesome;">&#xf10b;</span>&nbsp;');
				$mpdf->WriteHTML('<span><a style="color: #000000; text-decoration: none;" href="tel:'.$valueArray['mobiltelefon'].'">'.$valueArray['mobiltelefon'].'</a>&nbsp;&nbsp;</span>');
				$mpdf->WriteHTML('<br />');
			}

			# Telefone
			if($valueArray['gruppe'] != 'T' && $valueArray['gruppe'] != 'V' && ($valueArray['telefon1'] != '' || $valueArray['telefon2'] != '')) {
				$onePhoneAdded = false;
				if ($valueArray['telefon1'] != '' && !str_contains($valueArray['telefon1'], 'stand') && !str_contains($valueArray['telefon1'], 'Stand')) {
					$mpdf->WriteHTML('<span style="font-family: fontawesome;">&#xf095;</span>&nbsp;');
					$mpdf->WriteHTML('<span><a style="color: #000000; text-decoration: none;" href="tel:'.$valueArray['telefon1'].'">'.$valueArray['telefon1'].'</a>&nbsp;&nbsp;</span>');
					$onePhoneAdded = true;
				}
				if ($valueArray['telefon2'] != '' && !str_contains($valueArray['telefon2'], 'stand') && !str_contains($valueArray['telefon2'], 'Stand')) {
					$mpdf->WriteHTML('<span style="font-family: fontawesome;">&#xf095;</span>&nbsp;');
					$mpdf->WriteHTML('<span><a style="color: #000000; text-decoration: none;" href="tel:'.$valueArray['telefon2'].'">'.$valueArray['telefon2'].'</a></span>');
					$onePhoneAdded = true;
				}
				if ($onePhoneAdded) {
					$mpdf->WriteHTML('<br />');
				}
			}

			# Adresse
			if($valueArray['gruppe'] != 'T' && $valueArray['gruppe'] != 'V' && $valueArray['zusatz1'] != '') {
				$mpdf->WriteHTML('<span style="font-size:9pt; line-height: 1.3;">'.$valueArray['zusatz1'].'</span><br />');
			}
			if ($valueArray['gruppe'] != 'T' && $valueArray['gruppe'] != 'V') {
				$mpdf->WriteHTML('<span style="font-size:9pt; line-height: 1.3;">'.$valueArray['strasse1'].'</span><br />');
				$mpdf->WriteHTML('<span style="font-size:9pt; line-height: 1.3;">'.$valueArray['plz1'].' '.$valueArray['ort1']);
				if ($valueArray['land1'] != '') {
					$mpdf->WriteHTML(' / '.$valueArray['land1']);
				}
				$mpdf->WriteHTML('</span><br />');
			}

			$mpdf->WriteHTML('</div>');
			$mpdf->WriteHTML('</td></tr></table>');
			//$mpdf->WriteHTML('<hr />');
			//mpdf->WriteHTML('</div>');
		}
		$rowindex++;
	}
}
