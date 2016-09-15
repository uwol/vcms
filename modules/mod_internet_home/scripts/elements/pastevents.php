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


$stmt = $libDb->prepare('SELECT id FROM base_veranstaltung WHERE intern = 0 AND DATEDIFF(NOW(), datum) < 365 ORDER BY datum DESC');
$stmt->execute();

$maxNumberOfThumbnails = 6;
$i = 0;

$eventIds = array();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$pictureId = $libGallery->getMainPictureId($row['id']);

	if($pictureId > -1){
		$eventIds[] = $row['id'];
		$i++;

		if($i > $maxNumberOfThumbnails - 1){
			break;
		}
	}
}

if(count($eventIds) > 0){
	echo '<section id="pastevents" class="pastevents-box">';
	echo '<div class="container-fluid">';
	echo '<div class="row no-gutter">';

	foreach($eventIds as $eventId){
		$stmt = $libDb->prepare('SELECT id, titel, datum, ort, intern FROM base_veranstaltung WHERE id = :id');
		$stmt->bindValue(':id', $eventId);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		$pictureId = $libGallery->getMainPictureId($eventId);

		echo '<div class="col-lg-4 col-sm-6">';
		echo '<div itemscope itemtype="http://schema.org/SocialEvent">';

		echo '<div class="thumbnail">';
		echo '<div class="thumbnailOverflow">';
		echo '<a href="index.php?pid=event&amp;id=' .$eventId. '" class="event-box" itemprop="sameAs">';
		echo '<img src="inc.php?iid=event_picture&amp;eventid=' .$eventId. '&amp;id=' .$pictureId . '" alt="" class="img-responsive" />';

		echo '<div class="event-box-caption">';
		echo '<div class="event-box-caption-content">';
		echo '<div class="event-name text-faded" itemprop="name">';
		printVeranstaltungTitle($row);
		echo '</div>';
    echo '<div class="event-time">';
    printVeranstaltungDateTime($row);
    echo '</div>';
    echo '</div>';
    echo '</div>';

    echo '</a>';
    echo '</div>';
		echo '</div>';

		echo '<address itemprop="location" itemscope itemtype="http://schema.org/Place">';

		if($row['ort'] != ''){
			echo '<meta itemprop="name" content="' .$row['ort']. '" />';
			echo '<meta itemprop="address" content="' .$row['ort']. '" />';
		} else {
			echo '<meta itemprop="name" content="adH" />';
			echo '<meta itemprop="address" content="adH" />';
		}

		echo '</address>';

    echo '</div>';
    echo '</div>';
	}

	echo '</div>';
	echo '</div>';
	echo '</section>';
}
