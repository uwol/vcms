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


echo 'Erstelle Tabelle base_gruppe<br />';
$sql = "CREATE TABLE base_gruppe (
  bezeichnung char(1) NOT NULL default '',
  beschreibung varchar(30) NOT NULL default '',
  PRIMARY KEY  (bezeichnung)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_status<br />';
$sql = "CREATE TABLE base_status (
  bezeichnung varchar(20) NOT NULL default '',
  beschreibung varchar(255) NOT NULL default '',
  PRIMARY KEY  (bezeichnung)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_region<br />';
$sql = "CREATE TABLE base_region (
  id int(11) NOT NULL  auto_increment,
  bezeichnung varchar(30) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY bezeichnung (bezeichnung)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_person<br />';
$sql = "CREATE TABLE base_person (
  id int(11) NOT NULL auto_increment,
  anrede varchar(30) NOT NULL default 'Herr',
  titel varchar(255) default NULL,
  rang varchar(255) default NULL,
  vorname varchar(255) NOT NULL default '',
  praefix varchar(30) default NULL,
  name varchar(255) NOT NULL default '',
  suffix varchar(30) default NULL,
  zusatz1 varchar(255) default NULL,
  strasse1 varchar(255) default NULL,
  ort1 varchar(255) default NULL,
  plz1 varchar(5) default NULL,
  land1 varchar(255) default NULL,
  telefon1 varchar(255) default NULL,
  datum_adresse1_stand date default NULL,
  zusatz2 varchar(255) default NULL,
  strasse2 varchar(255) default NULL,
  ort2 varchar(255) default NULL,
  plz2 varchar(5) default NULL,
  land2 varchar(255) default NULL,
  telefon2 varchar(255) default NULL,
  datum_adresse2_stand date default NULL,
  region1 int(11) default NULL,
  region2 int(11) default NULL,
  mobiltelefon varchar(255) default NULL,
  email varchar(255) default NULL,
  skype varchar(255) default NULL,
  jabber varchar(255) default NULL,
  webseite varchar(255) default NULL,
  datum_geburtstag date default NULL,
  beruf varchar(255) default NULL,
  heirat_partner int(11) default NULL,
  heirat_datum date default NULL,
  tod_datum date default NULL,
  tod_ort varchar(255) default NULL,
  gruppe char(1) NOT NULL default 'F',
  datum_gruppe_stand date default NULL,
  status varchar(20) default NULL,
  semester_reception varchar(10) default NULL,
  semester_promotion varchar(10) default NULL,
  semester_philistrierung varchar(10) default NULL,
  semester_aufnahme varchar(10) default NULL,
  semester_fusion varchar(10) default NULL,
  austritt_datum date default NULL,
  spitzname varchar(255) default NULL,
  leibmitglied int(11) default NULL,
  anschreiben_zusenden tinyint(1) NOT NULL default '1',
  spendenquittung_zusenden tinyint(1) NOT NULL default '1',
  vita text,
  bemerkung varchar(255) default NULL,
  username varchar(255) default NULL,
  password_hash varchar(255) default NULL,
  validationkey varchar(255) default NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY username (username),
  KEY gruppe (gruppe),
  KEY status (status)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_semester<br />';
$sql = "CREATE TABLE base_semester (
  semester varchar(10) NOT NULL default '',
  senior int(11) default NULL,
  sen_dech tinyint(1) default NULL,
  consenior int(11) default NULL,
  con_dech tinyint(1) default NULL,
  fuchsmajor int(11) default NULL,
  fm_dech tinyint(1) default NULL,
  fuchsmajor2 int(11) default NULL,
  fm2_dech tinyint(1) default NULL,
  scriptor int(11) default NULL,
  scr_dech tinyint(1) default NULL,
  quaestor int(11) default NULL,
  quaes_dech tinyint(1) default NULL,
  jubelsenior int(11) default NULL,
  jubelsen_dech tinyint(1) default NULL,
  ahv_senior int(11) default NULL,
  ahv_consenior int(11) default NULL,
  ahv_keilbeauftragter int(11) default NULL,
  ahv_scriptor int(11) default NULL,
  ahv_quaestor int(11) default NULL,
  ahv_beisitzer1 int(11) default NULL,
  ahv_beisitzer2 int(11) default NULL,
  hv_vorsitzender int(11) default NULL,
  hv_kassierer int(11) default NULL,
  hv_beisitzer1 int(11) default NULL,
  hv_beisitzer2 int(11) default NULL,
  archivar int(11) default NULL,
  redaktionswart int(11) default NULL,
  hauswart int(11) default NULL,
  bierwart int(11) default NULL,
  kuehlschrankwart int(11) default NULL,
  thekenwart int(11) default NULL,
  internetwart int(11) default NULL,
  technikwart int(11) default NULL,
  fotowart int(11) default NULL,
  wirtschaftskassenwart int(11) default NULL,
  wichswart int(11) default NULL,
  bootshauswart int(11) default NULL,
  huettenwart int(11) default NULL,
  fechtwart int(11) default NULL,
  stammtischwart int(11) default NULL,
  musikwart int(11) default NULL,
  ausflugswart int(11) default NULL,
  sportwart int(11) default NULL,
  couleurartikelwart int(11) default NULL,
  ferienordner int(11) default NULL,
  dachverbandsberichterstatter int(11) default NULL,
  vop int(11) default NULL,
  vvop int(11) default NULL,
  vopxx int(11) default NULL,
  vopxxx int(11) default NULL,
  vopxxxx int(11) default NULL,
  PRIMARY KEY  (semester)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_veranstaltung<br />';
$sql = "CREATE TABLE base_veranstaltung (
  id int(11) NOT NULL auto_increment,
  datum datetime NOT NULL default '0000-00-00 00:00:00',
  datum_ende datetime default NULL,
  titel varchar(255) NOT NULL default '',
  spruch varchar(255) default NULL,
  beschreibung text,
  status varchar(2) default NULL,
  ort varchar(255) default NULL,
  fb_eventid VARCHAR(255) NULL,
  PRIMARY KEY  (id),
  KEY datum (datum)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_verein<br />';
$sql = "CREATE TABLE base_verein (
  id int(11) NOT NULL auto_increment,
  name varchar(255) default NULL,
  kuerzel varchar(30) default NULL,
  aktivitas tinyint(1) NOT NULL default '1',
  ahahschaft tinyint(1) NOT NULL default '1',
  titel varchar(255) default NULL,
  rang varchar(255) default NULL,
  dachverband varchar(30) default NULL,
  dachverbandnr int(11) default NULL,
  zusatz1 varchar(255) default NULL,
  strasse1 varchar(255) default NULL,
  ort1 varchar(255) default NULL,
  plz1 varchar(5) default NULL,
  land1 varchar(255) default NULL,
  datum_adresse1_stand date default NULL,
  telefon1 varchar(255) default NULL,
  anschreiben_zusenden tinyint(1) NOT NULL default '0',
  mutterverein int(11) default NULL,
  fusioniertin int(11) default NULL,
  datum_gruendung date default NULL,
  webseite varchar(255) default NULL,
  wahlspruch text,
  farbenstrophe text,
  farbenstrophe_inoffiziell text,
  fuchsenstrophe text,
  bundeslied text,
  farbe1 varchar(255) default NULL,
  farbe2 varchar(255) default NULL,
  farbe3 varchar(255) default NULL,
  farbe4 varchar(255) default NULL,
  beschreibung text,
  PRIMARY KEY  (id)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_vip<br />';
$sql = "CREATE TABLE base_vip (
  id int(11) NOT NULL auto_increment,
  praefix varchar(30) default NULL,
  name varchar(255) default NULL,
  suffix varchar(30) default NULL,
  vorname varchar(255) default NULL,
  anrede varchar(30) default NULL,
  titel varchar(255) default NULL,
  rang varchar(255) default NULL,
  zusatz1 varchar(255) default NULL,
  strasse1 varchar(255) default NULL,
  plz1 varchar(5) default NULL,
  ort1 varchar(255) default NULL,
  land1 varchar(255) default NULL,
  datum_adresse1_stand date default NULL,
  telefon1 varchar(255) default NULL,
  status varchar(255) default NULL,
  grund varchar(255) default NULL,
  bemerkung varchar(255) default NULL,
  PRIMARY KEY  (id)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_verein_mitgliedschaft<br />';
$sql = "CREATE TABLE base_verein_mitgliedschaft (
  mitglied int(11) NOT NULL default '0',
  verein int(11) NOT NULL default '0',
  ehrenmitglied tinyint(1) default NULL,
  semester_reception varchar(10) default NULL,
  semester_philistrierung varchar(10) default NULL,
  PRIMARY KEY  (mitglied,verein),
  KEY verein (verein)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle base_veranstaltung_teilnahme<br />';
$sql = "CREATE TABLE base_veranstaltung_teilnahme (
  veranstaltung int(11) NOT NULL default '0',
  person int(11) NOT NULL default '0',
  PRIMARY KEY  (veranstaltung,person),
  KEY person (person)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle sys_genericstorage<br />';
$sql = "CREATE TABLE sys_genericstorage (
  moduleid varchar(100) NOT NULL default '',
  array_name varchar(30) NOT NULL default '',
  position int(11) NOT NULL default '0',
  value text NOT NULL,
  PRIMARY KEY  (moduleid,array_name,position)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle sys_log_intranet<br />';
$sql = "CREATE TABLE sys_log_intranet (
  id int(11) NOT NULL auto_increment,
  mitglied int(11) NOT NULL default '0',
  aktion smallint(4) default NULL,
  datum datetime NOT NULL default '0000-00-00 00:00:00',
  punkte smallint(4) NOT NULL default '0',
  ipadresse varchar(39) default NULL,
  PRIMARY KEY  (id),
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

$stmt = $libDb->prepare('INSERT IGNORE INTO base_person (id, titel, vorname, name, gruppe, datum_geburtstag) VALUES (3, :titel, :vorname, :name, :gruppe, DATE_SUB(CURDATE(), INTERVAL 50 YEAR))');
$stmt->bindValue(':titel', 'Dr.');
$stmt->bindValue(':vorname', 'Peter');
$stmt->bindValue(':name', 'Philister');
$stmt->bindValue(':gruppe', 'P');
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
?>