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


echo '<div class="row">';

$stmt = $libDb->prepare("SELECT * FROM mod_internethome_nachricht WHERE startdatum < NOW() AND (verfallsdatum > NOW() || verfallsdatum = '0000-00-00 00:00:00') ORDER BY startdatum DESC LIMIT 0,2");
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<div class="col-sm-6">';
	echo '<div class="row">';

	echo '<div class="hidden-xs col-sm-6">';
	$posssibleImage = $libModuleHandler->getModuleDirectory(). '/custom/bilder/' .$row['id']. '.jpg';

	if(is_file($posssibleImage)){
		echo '<img src="' .$posssibleImage. '" class="img-responsive center-block" alt="" />';
	}

	echo '</div>';

	echo '<div class="col-xs-12 col-sm-6">';
	echo '<p>' .$libString->parseBBCode(nl2br(trim($row['text']))). '</p>';
	echo '</div>';

	echo '</div>';
	echo '</div>';
}

echo '</div>';
?>