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


include($libModuleHandler->getModuleDirectoryByModuleid("mod_internet_semesterprogramm") . 'scripts/lib/gallery.class.php');

$libGallery = new LibGallery($libDb);

$stmt = $libDb->prepare("SELECT id, titel FROM base_veranstaltung WHERE DATEDIFF(NOW(), datum) < 90 ORDER BY RAND()");
$stmt->execute();


if($libAuth->isLoggedin()){
	$level = 1;
} else {
	$level = 0;
}


echo '<div class="col-md-12">';
echo '<h2>Impressionen</h2>';
echo '<hr />';
echo '<div class="row">';

$i = 0;

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	// is there a gallery?
	if($libGallery->hasPictures($row['id'], $level)){
		$pictures = $libGallery->getPictures($row['id'], $level);

		//determine random image
		srand(microtime() * 1000000);
		$zufallszahl = rand(0, count($pictures)-1);
		$keys = array_keys($pictures);
		$pictureid = $keys[$zufallszahl];

		echo '<section class="col-sm-6 col-md-4 col-lg-3">';
		echo '<h3>' .wordwrap($row['titel'], 50, '-', 1). '</h3> ';

		echo '<a href="index.php?pid=semesterprogramm_event&amp;eventid=' .$row['id']. '">';

		$visibilityClass = '';

		if($libGallery->getPublicityLevel($pictures[$pictureid]) == 1){
			$visibilityClass = "internal";
		}

		echo '<img src="inc.php?iid=semesterprogramm_picture&amp;eventid=' .$row['id']. '&amp;pictureid=' .$pictureid . '&thumb=1" alt="" class="img-responsive center-block thumbnail ' .$visibilityClass. '" />';
		echo '</a>';

		echo '</section>';

		$i++;

		if($i >= 4){
			break;
		}
	}
}

echo '</div>';
echo '<hr />';
echo '</div>';
?>