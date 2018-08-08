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

if(!is_object($libGlobal))
  exit();


echo 'Erstelle Tabelle base_gruppe<br />';
$sql = "CREATE TABLE base_gruppe (
  bezeichnung char(1),
  beschreibung varchar(255),
  PRIMARY KEY (bezeichnung)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_status<br />';
$sql = "CREATE TABLE base_status (
  bezeichnung varchar(255),
  beschreibung varchar(255),
  PRIMARY KEY (bezeichnung)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_region<br />';
$sql = "CREATE TABLE base_region (
  id int(11) NOT NULL auto_increment,
  bezeichnung varchar(255),
  PRIMARY KEY (id),
  UNIQUE KEY bezeichnung (bezeichnung)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_person<br />';
$sql = "CREATE TABLE base_person (
  id int(11) NOT NULL auto_increment,
  anrede varchar(255),
  titel varchar(255),
  rang varchar(255),
  vorname varchar(255),
  praefix varchar(255),
  name varchar(255),
  suffix varchar(255),
  geburtsname varchar(255),
  zusatz1 varchar(255),
  strasse1 varchar(255),
  ort1 varchar(255),
  plz1 varchar(255),
  land1 varchar(255),
  telefon1 varchar(255),
  datum_adresse1_stand date,
  zusatz2 varchar(255),
  strasse2 varchar(255),
  ort2 varchar(255),
  plz2 varchar(255),
  land2 varchar(255),
  telefon2 varchar(255),
  datum_adresse2_stand date,
  region1 int(11),
  region2 int(11),
  mobiltelefon varchar(255),
  email varchar(255),
  skype varchar(255),
  webseite varchar(255),
  datum_geburtstag date,
  beruf varchar(255),
  heirat_partner int(11),
  heirat_datum date,
  tod_datum date,
  tod_ort varchar(255),
  gruppe char(1) NOT NULL default 'F',
  datum_gruppe_stand date,
  status varchar(255),
  semester_reception varchar(10),
  semester_promotion varchar(10),
  semester_philistrierung varchar(10),
  semester_aufnahme varchar(10),
  semester_fusion varchar(10),
  austritt_datum date,
  spitzname varchar(255),
  leibmitglied int(11),
  anschreiben_zusenden tinyint(1) NOT NULL default '1',
  spendenquittung_zusenden tinyint(1) NOT NULL default '1',
  vita text,
  bemerkung varchar(255),
  password_hash varchar(255),
  validationkey varchar(255),
  PRIMARY KEY (id),
  UNIQUE KEY email (email),
  KEY gruppe (gruppe),
  KEY status (status)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_semester<br />';
$sql = "CREATE TABLE base_semester (
  semester varchar(10),
  senior int(11),
  sen_dech tinyint(1),
  consenior int(11),
  con_dech tinyint(1),
  fuchsmajor int(11),
  fm_dech tinyint(1),
  fuchsmajor2 int(11),
  fm2_dech tinyint(1),
  scriptor int(11),
  scr_dech tinyint(1),
  quaestor int(11),
  quaes_dech tinyint(1),
  jubelsenior int(11),
  jubelsen_dech tinyint(1),
  ahv_senior int(11),
  ahv_consenior int(11),
  ahv_keilbeauftragter int(11),
  ahv_scriptor int(11),
  ahv_quaestor int(11),
  ahv_beisitzer1 int(11),
  ahv_beisitzer2 int(11),
  hv_vorsitzender int(11),
  hv_kassierer int(11),
  hv_beisitzer1 int(11),
  hv_beisitzer2 int(11),
  archivar int(11),
  ausflugswart int(11),
  bierwart int(11),
  bootshauswart int(11),
  couleurartikelwart int(11),
  datenpflegewart int(11),
  fechtwart int(11),
  fotowart int(11),
  hauswart int(11),
  huettenwart int(11),
  internetwart int(11),
  kuehlschrankwart int(11),
  musikwart int(11),
  redaktionswart int(11),
  technikwart int(11),
  thekenwart int(11),
  sportwart int(11),
  stammtischwart int(11),
  wichswart int(11),
  wirtschaftskassenwart int(11),
  ferienordner int(11),
  dachverbandsberichterstatter int(11),
  vop int(11),
  vvop int(11),
  vopxx int(11),
  vopxxx int(11),
  vopxxxx int(11),
  PRIMARY KEY (semester)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_veranstaltung<br />';
$sql = "CREATE TABLE base_veranstaltung (
  id int(11) NOT NULL auto_increment,
  datum datetime NOT NULL default '0000-00-00 00:00:00',
  datum_ende datetime,
  titel varchar(255),
  spruch varchar(255),
  beschreibung text,
  status varchar(2),
  ort varchar(255),
  fb_eventid VARCHAR(255) NULL,
  intern tinyint(1) NOT NULL default 0,
  PRIMARY KEY (id),
  KEY datum (datum)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_verein<br />';
$sql = "CREATE TABLE base_verein (
  id int(11) NOT NULL auto_increment,
  name varchar(255),
  kuerzel varchar(255),
  aktivitas tinyint(1) NOT NULL default '1',
  ahahschaft tinyint(1) NOT NULL default '1',
  titel varchar(255),
  rang varchar(255),
  dachverband varchar(255),
  dachverbandnr int(11),
  zusatz1 varchar(255),
  strasse1 varchar(255),
  ort1 varchar(255),
  plz1 varchar(255),
  land1 varchar(255),
  datum_adresse1_stand date,
  telefon1 varchar(255),
  anschreiben_zusenden tinyint(1) NOT NULL default '0',
  mutterverein int(11),
  fusioniertin int(11),
  datum_gruendung date,
  webseite varchar(255),
  wahlspruch text,
  farbenstrophe text,
  farbenstrophe_inoffiziell text,
  fuchsenstrophe text,
  bundeslied text,
  farbe1 varchar(255),
  farbe2 varchar(255),
  farbe3 varchar(255),
  farbe4 varchar(255),
  beschreibung text,
  PRIMARY KEY (id)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_vip<br />';
$sql = "CREATE TABLE base_vip (
  id int(11) NOT NULL auto_increment,
  praefix varchar(255),
  name varchar(255),
  suffix varchar(255),
  vorname varchar(255),
  anrede varchar(255),
  titel varchar(255),
  rang varchar(255),
  zusatz1 varchar(255),
  strasse1 varchar(255),
  plz1 varchar(255),
  ort1 varchar(255),
  land1 varchar(255),
  datum_adresse1_stand date,
  telefon1 varchar(255),
  status varchar(255),
  grund varchar(255),
  bemerkung varchar(255),
  PRIMARY KEY (id)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_verein_mitgliedschaft<br />';
$sql = "CREATE TABLE base_verein_mitgliedschaft (
  mitglied int(11) NOT NULL default '0',
  verein int(11) NOT NULL default '0',
  ehrenmitglied tinyint(1),
  semester_reception varchar(10),
  semester_philistrierung varchar(10),
  PRIMARY KEY (mitglied,verein),
  KEY verein (verein)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_veranstaltung_teilnahme<br />';
$sql = "CREATE TABLE base_veranstaltung_teilnahme (
  veranstaltung int(11) NOT NULL default '0',
  person int(11) NOT NULL default '0',
  PRIMARY KEY (veranstaltung,person),
  KEY person (person)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle sys_genericstorage<br />';
$sql = "CREATE TABLE sys_genericstorage (
  moduleid varchar(100),
  array_name varchar(30),
  position int(11) NOT NULL default '0',
  value text,
  PRIMARY KEY (moduleid, array_name, position)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle sys_log_intranet<br />';
$sql = "CREATE TABLE sys_log_intranet (
  id int(11) NOT NULL auto_increment,
  mitglied int(11) NOT NULL default '0',
  aktion smallint(4),
  datum datetime NOT NULL default '0000-00-00 00:00:00',
  punkte smallint(4) NOT NULL default '0',
  ipadresse varchar(255),
  PRIMARY KEY (id),
  KEY mitglied (mitglied),
  KEY datum (datum),
  KEY aktion (aktion)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Speichere Standarddatensätze<br />';

$sql = "INSERT IGNORE INTO base_gruppe (bezeichnung, beschreibung) VALUES ('F', 'Fuchs'),
('B', 'Bursche'),
('P', 'Philister'),
('T', 'Verstorbenes Mitglied'),
('C', 'Couleurdame'),
('G', 'Gattin'),
('W', 'Witwe'),
('V', 'Verstorbene Gattin'),
('Y', 'Vereinsfreund'),
('X', 'Ausgetreten');";
$libDb->query($sql);


$sql = "INSERT IGNORE INTO base_status (bezeichnung, beschreibung) VALUES ('A-Phil', 'A-Philister'),
('B-Phil', 'B-Philister'),
('Ehrenmitglied', 'Ehrenmitglied'),
('ex loco', 'Mitglied an anderem Ort'),
('HV-M', 'Hausvereinsmitglied, kein Philister'),
('Inaktiv', 'inaktives Mitglied'),
('Inaktiv ex loco', 'Inaktives Mitglied an einem anderen Ort'),
('VG', 'Verkehrsgast');";
$libDb->query($sql);


echo 'Speichere Demo-Datensätze<br />';

$loremIpsum = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.';

$stmt = $libDb->prepare('INSERT IGNORE INTO base_person (id, vorname, name, gruppe) VALUES (1, :vorname, :name, :gruppe)');
$stmt->bindValue(':vorname', 'Felix');
$stmt->bindValue(':name', 'Fuchs');
$stmt->bindValue(':gruppe', 'F');
$stmt->execute();

$stmt = $libDb->prepare('INSERT IGNORE INTO base_person (id, vorname, name, gruppe) VALUES (2, :vorname, :name, :gruppe)');
$stmt->bindValue(':vorname', 'Bernd');
$stmt->bindValue(':name', 'Bursche');
$stmt->bindValue(':gruppe', 'B');
$stmt->execute();

$stmt = $libDb->prepare('INSERT IGNORE INTO base_person (id, titel, vorname, name, gruppe, datum_geburtstag, heirat_partner, heirat_datum) VALUES (3, :titel, :vorname, :name, :gruppe, DATE_SUB(CURDATE(), INTERVAL 50 YEAR), 4, DATE_SUB(CURDATE(), INTERVAL 2 DAY))');
$stmt->bindValue(':titel', 'Dr.');
$stmt->bindValue(':vorname', 'Peter');
$stmt->bindValue(':name', 'Philister');
$stmt->bindValue(':gruppe', 'P');
$stmt->execute();

$stmt = $libDb->prepare('INSERT IGNORE INTO base_person (id, vorname, name, gruppe, heirat_partner) VALUES (4, :vorname, :name, :gruppe, 3)');
$stmt->bindValue(':vorname', 'Gabriele');
$stmt->bindValue(':name', 'Gattin');
$stmt->bindValue(':gruppe', 'G');
$stmt->execute();

$stmt = $libDb->prepare('INSERT IGNORE INTO base_person (id, vorname, name, gruppe) VALUES (5, :vorname, :name, :gruppe)');
$stmt->bindValue(':vorname', 'Claudia');
$stmt->bindValue(':name', 'Couleurdame');
$stmt->bindValue(':gruppe', 'C');
$stmt->execute();

$stmt = $libDb->prepare('INSERT IGNORE INTO base_person (id, vorname, name, gruppe, tod_datum, datum_geburtstag) VALUES (6, :vorname, :name, :gruppe, DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_SUB(CURDATE(), INTERVAL 80 YEAR))');
$stmt->bindValue(':vorname', 'Valdemar');
$stmt->bindValue(':name', 'Verstorbener');
$stmt->bindValue(':gruppe', 'T');
$stmt->execute();

$stmt = $libDb->prepare('INSERT IGNORE INTO base_veranstaltung (id, datum, titel, beschreibung) VALUES (1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), :titel, :beschreibung)');
$stmt->bindValue(':titel', 'Semestergottesdienst');
$stmt->bindValue(':beschreibung', $loremIpsum);
$stmt->execute();

$stmt = $libDb->prepare('INSERT IGNORE INTO base_veranstaltung (id, datum, titel, beschreibung) VALUES (2, DATE_ADD(CURDATE(), INTERVAL 2 DAY), :titel, :beschreibung)');
$stmt->bindValue(':titel', 'Gästeabend');
$stmt->bindValue(':beschreibung', $loremIpsum);
$stmt->execute();

$stmt = $libDb->prepare('INSERT IGNORE INTO base_veranstaltung (id, datum, titel, beschreibung) VALUES (3, DATE_ADD(CURDATE(), INTERVAL 10 DAY), :titel, :beschreibung)');
$stmt->bindValue(':titel', 'Festkommers');
$stmt->bindValue(':beschreibung', $loremIpsum);
$stmt->execute();

$stmt = $libDb->prepare('INSERT IGNORE INTO base_veranstaltung (id, datum, titel, beschreibung) VALUES (4, DATE_ADD(CURDATE(), INTERVAL 11 DAY), :titel, :beschreibung)');
$stmt->bindValue(':titel', 'Festball');
$stmt->bindValue(':beschreibung', $loremIpsum);
$stmt->execute();
