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

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	if($libAuth->isLoggedin()){
		$level = 1;
	} else {
		$level = 0;
	}

	// is there a gallery?
	if($libGallery->hasPictures($row['id'], $level)){
		$pictures = $libGallery->getPictures($row['id'], $level);

		//determine random image
		srand(microtime() * 1000000);
		$zufallszahl = rand(0, count($pictures)-1);
		$keys = array_keys($pictures);
		$pictureid = $keys[$zufallszahl];

		echo '<h2>Impression</h2>';
		echo '<hr />';

		echo '<h3 class="title">Veranstaltung:</h3> ' .wordwrap($row['titel'], 50, '<br />', 1);

		echo '<a href="index.php?pid=semesterprogramm_event&amp;eventid=' .$row['id']. '">';

		$visibilityClass = '';

		if($libGallery->getPublicityLevel($pictures[$pictureid]) == 1){
			$visibilityClass = "internal";
		}

		echo '<img src="inc.php?iid=semesterprogramm_picture&amp;eventid=' .$row['id']. '&amp;pictureid=' .$pictureid . '&thumb=1" alt="" class="img-responsive thumbnail center-block thumb ' .$visibilityClass. '" />';
		echo '</a>';

		echo '<hr />';

		break;
	}
}
?>