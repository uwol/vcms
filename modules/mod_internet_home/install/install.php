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


echo 'Erstelle Tabelle mod_internethome_nachricht<br />';
$sql = "CREATE TABLE mod_internethome_nachricht (
  id int(11) NOT NULL auto_increment,
  startdatum datetime NOT NULL default '0000-00-00 00:00:00',
  verfallsdatum datetime NOT NULL default '0000-00-00 00:00:00',
  text text,
  PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Speichere Standarddatensätze';

$loremIpsum = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.';

$stmt = $libDb->prepare('INSERT IGNORE INTO mod_internethome_nachricht (id, startdatum, text) VALUES (1, NOW(), :text)');
$stmt->bindValue(':text', $loremIpsum);
$stmt->execute();

$stmt = $libDb->prepare('INSERT IGNORE INTO mod_internethome_nachricht (id, startdatum, text) VALUES (2, NOW(), :text)');
$stmt->bindValue(':text', $loremIpsum);
$stmt->execute();
?>