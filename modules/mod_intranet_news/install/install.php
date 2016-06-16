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


/**
* Datenbankstrukturen installieren
*/

echo 'Erstelle Tabelle: mod_news_kategorie<br />';
$sql = "CREATE TABLE mod_news_kategorie (
	id int(11) NOT NULL auto_increment,
	bezeichnung varchar(255) NOT NULL default '',
	PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);

echo 'Erstelle Tabelle: mod_news_news<br />';
$sql = "CREATE TABLE mod_news_news (
	id int(11) NOT NULL auto_increment,
	kategorieid int(11) default NULL,
	eingabedatum datetime NOT NULL default '0000-00-00 00:00:00',
	text text NOT NULL,
	betroffenesmitglied int(11) default NULL,
	autor int(11) default NULL,
	PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);

/**
* Datenbankwerte installieren
*/

echo 'Füge Standarddatensätze ein in Tabelle: mod_news_kategorie<br />';
$sql = "INSERT IGNORE INTO mod_news_kategorie (id, bezeichnung) VALUES (1, 'Todesfall'),
(2, 'Geburt'),
(3, 'Hochzeit'),
(4, 'Klatsch und Tratsch'),
(5, 'Austritt'),
(6, 'Eintritt'),
(8, 'Veranstaltung'),
(9, 'Adressänderung'),
(10, 'Allgemeiner Hinweis'),
(11, 'Philistrierung'),
(12, 'Neuerung'),
(13, 'Kritik'),
(15, 'Frage'),
(16, 'Chargieren');";
$libDb->query($sql);

echo 'Füge Standarddatensatz ein in Tabelle mod_news_news';
$sql = "INSERT IGNORE INTO mod_news_news (id, kategorieid, eingabedatum, text) VALUES (1, 12, NOW(), 'Dies ist ein Beispielbeitrag im Intranet.');";
$libDb->query($sql);
?>