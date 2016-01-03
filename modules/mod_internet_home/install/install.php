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
echo 'Erstelle Tabelle: mod_internethome_nachricht<br />';
$sql = "CREATE TABLE mod_internethome_nachricht (
  id int(11) NOT NULL auto_increment,
  startdatum datetime NOT NULL default '0000-00-00 00:00:00',
  verfallsdatum datetime NOT NULL default '0000-00-00 00:00:00',
  text text,
  PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);

echo 'Füge Standarddatensatz ein.';
$sql = "INSERT IGNORE INTO mod_internethome_nachricht (id,startdatum,verfallsdatum,text) VALUES (1,'2000-01-01 20:00:00','0000-00-00 00:00:00', 'Dies ist ein Beispielbeitrag für die Startseite')";
$libDb->query($sql);
?>