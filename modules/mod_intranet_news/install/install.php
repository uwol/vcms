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


echo 'Erstelle Tabelle mod_news_kategorie<br />';
$sql = "CREATE TABLE mod_news_kategorie (
	id int(11) NOT NULL auto_increment,
	bezeichnung varchar(255) NOT NULL default '',
	PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Erstelle Tabelle mod_news_news<br />';
$sql = "CREATE TABLE mod_news_news (
	id int(11) NOT NULL auto_increment,
	kategorieid int(11),
	eingabedatum datetime NOT NULL default '0000-00-00 00:00:00',
	text text NOT NULL,
	betroffenesmitglied int(11),
	autor int(11),
	PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Speichere Standarddatensätze<br />';
$sql = "INSERT IGNORE INTO mod_news_kategorie (id, bezeichnung) VALUES (1, 'Todesfall'), (2, 'Geburt'), (3, 'Hochzeit'), (4, 'Klatsch und Tratsch'), (5, 'Austritt'), (6, 'Eintritt'), (8, 'Veranstaltung'), (9, 'Adressänderung'), (10, 'Allgemeiner Hinweis'), (11, 'Philistrierung'), (12, 'Neuerung'), (13, 'Kritik'), (15, 'Frage'), (16, 'Chargieren'), (17, 'Gratulation');";
$libDb->query($sql);


echo 'Speichere Demo-Datensätze<br />';

$loremIpsum = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.';

$stmt = $libDb->prepare('INSERT IGNORE INTO mod_news_news (id, kategorieid, eingabedatum, text, autor) VALUES (1, 12, NOW(), :text, 1)');
$stmt->bindValue(':text', $loremIpsum);
$stmt->execute();

$stmt = $libDb->prepare('INSERT IGNORE INTO mod_news_news (id, kategorieid, eingabedatum, text, autor) VALUES (2, 4, DATE_SUB(NOW(), INTERVAL 5 DAY), :text, 1)');
$stmt->bindValue(':text', $loremIpsum);
$stmt->execute();

$stmt = $libDb->prepare('INSERT IGNORE INTO mod_news_news (id, kategorieid, eingabedatum, text, autor) VALUES (2, 3, DATE_SUB(NOW(), INTERVAL 8 DAY), :text, 1)');
$stmt->bindValue(':text', $loremIpsum);
$stmt->execute();
