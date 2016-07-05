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


echo 'Erstelle Tabelle mod_reservierung_reservierung<br />';
$sql = "CREATE TABLE mod_reservierung_reservierung (
	id int(11) NOT NULL auto_increment,
	datum date NOT NULL default '0000-00-00',
	beschreibung text NOT NULL,
	person int(11) NOT NULL default '0',
	PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);


echo 'Speichere Demo-Datensätze<br />';

$loremIpsum = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.';

$stmt = $libDb->prepare('INSERT IGNORE INTO mod_reservierung_reservierung (id, datum, beschreibung, person) VALUES (1, DATE_ADD(NOW(), INTERVAL 5 DAY), :text, 1)');
$stmt->bindValue(':text', $loremIpsum);
$stmt->execute();

$stmt = $libDb->prepare('INSERT IGNORE INTO mod_reservierung_reservierung (id, datum, beschreibung, person) VALUES (2, DATE_ADD(NOW(), INTERVAL 10 DAY), :text, 1)');
$stmt->bindValue(':text', $loremIpsum);
$stmt->execute();
?>