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


echo 'Erstelle Tabelle mod_rundbrief_empfaenger<br />';
$sql = "CREATE TABLE mod_rundbrief_empfaenger (
	id int(11) NOT NULL default '0',
	empfaenger tinyint(1) NOT NULL default '1',
	interessiert tinyint(1) NOT NULL default '0',
	PRIMARY KEY id (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$libDb->query($sql);
?>