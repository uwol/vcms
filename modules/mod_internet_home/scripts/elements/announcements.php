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


echo '<tr><th>Ankündigung</th></tr>';
echo '<tr><td class="ankuendigungsBox">';
echo '<hr />';

$stmt = $libDb->prepare("SELECT * FROM mod_internethome_nachricht WHERE startdatum < NOW() AND (verfallsdatum > NOW() || verfallsdatum = '0000-00-00 00:00:00') ORDER BY startdatum DESC LIMIT 0,3");
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<p class="textankuendigung" style="clear:both">';
	$posssibleImage = $libModuleHandler->getModuleDirectory(). 'custom/bilder/' .$row['id']. '.jpg';

	if(is_file($posssibleImage)){
		echo '<img src="'.$posssibleImage.'" style="float:left;margin-right:10px;margin-bottom:6px;';
		list($width, $height, $type, $attr) = getimagesize($posssibleImage);

		if(($width / 4 * 3) >= $height){
			echo 'width:200px;';
		} else {
			echo 'height:150px;';
		}

		echo '" alt="" />';
	}

	echo $libString->parseBBCode(nl2br(trim($row['text'])));
	echo '</p>';
	echo '<div class="textankuendigung" style="clear:both"><hr /></div>';
}

// link
$stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM mod_internethome_nachricht WHERE startdatum < NOW() ORDER BY startdatum DESC');
$stmt->execute();
$stmt->bindColumn('number', $number);
$stmt->fetch();

if($number > 3){
	echo '<p class="textankuendigung" style="clear:both">';
	echo '<a href="index.php?pid=home_ankuendigungen">Weitere Ankündigungen ...</a>';
	echo '</p>';
	echo '<div class="textankuendigung" style="clear:both"><hr /></div>';
}

echo '</td></tr>';
?>