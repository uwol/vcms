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


if($libModuleHandler->moduleIsAvailable('mod_internet_semesterprogramm')){
	include($libModuleHandler->getModuleDirectoryByModuleid('mod_internet_semesterprogramm'). '/scripts/lib/gallery.class.php');

	$libGallery = new LibGallery($libDb);

	$stmt = $libDb->prepare('SELECT id FROM base_veranstaltung WHERE DATEDIFF(NOW(), datum) < 120 ORDER BY datum DESC');
	$stmt->execute();

	$maxNumberOfThumbnails = 4;
	$i = 0;

	$eventIds = array();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		if($libGallery->hasPictures($row['id'], 0)){
			$eventIds[] = $row['id'];
			$i++;

			if($i > $maxNumberOfThumbnails - 1){
				break;
			}
		}
	}

	if(count($eventIds) > 0){
		echo '<hr />';
		echo '<div class="row">';

		foreach($eventIds as $eventId){
			$stmt = $libDb->prepare('SELECT id, titel, datum FROM base_veranstaltung WHERE id = :id');
			$stmt->bindValue(':id', $eventId);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			$pictures = $libGallery->getPictures($eventId, 0);

			//determine random image
			srand(microtime() * 1000000);
			$randomNumber = rand(0, count($pictures)-1);
			$keys = array_keys($pictures);
			$pictureid = $keys[$randomNumber];

			echo '<div class="col-sm-6 col-md-3">';

			echo '<div class="thumbnail">';
			echo '<div class="thumbnailOverflow">';
			echo '<a href="index.php?pid=semesterprogramm_event&amp;eventid=' .$eventId. '">';
			echo '<img src="inc.php?iid=semesterprogramm_picture&amp;eventid=' .$eventId. '&amp;pictureid=' .$pictureid . '" alt="" class="img-responsive center-block" />';
			echo '</a>';
			echo '</div>';
			echo '<div class="caption">';
			printVeranstaltungTitle($row);
			printVeranstaltungTime($row);
			echo '</div>';
			echo '</div>';

			echo '</div>';
		}

		echo '</div>';
	}
}
?>