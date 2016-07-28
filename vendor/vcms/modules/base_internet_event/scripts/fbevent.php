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


/*
* actions
*/

$id = '';
if(isset($_GET['eventid'])){
	$id = $_GET['eventid'];
}

if($id == ''){
	exit;
}

$stmt = $libDb->prepare('SELECT * FROM base_veranstaltung WHERE id=:id');
$stmt->bindValue(':id', $id);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if($libEvent->isFacebookEvent($row)){
	$fbAppId = $libGenericStorage->loadValue('base_core', 'fbAppId');
	$fbSecretKey = $libGenericStorage->loadValue('base_core', 'fbSecretKey');
	$fbAccessToken = $fbAppId. '|' .$fbSecretKey;
	$fbUrl = 'https://www.facebook.com';
	$fbGraphUrl = 'https://graph.facebook.com';
	$fbAccessTokenQuery = '?access_token=' .$fbAccessToken;

	$fbEventId = $row['fb_eventid'];
	$eventUrl = $fbUrl. '/events/' .$fbEventId;

	$fbEventEndpoint = $fbGraphUrl. '/' .$fbEventId . $fbAccessTokenQuery . '&fields=attending_count,interested_count,cover';
	$eventJson = file_get_contents($fbEventEndpoint);

	if(!empty($eventJson)){
		$eventObject = json_decode($eventJson, true);

		$eventCoverSource = $eventObject['cover']['source'];
		$eventAttendingCount = $eventObject['attending_count'];
		$eventInterestedCount = $eventObject['interested_count'];

		echo '<div class="thumbnail">';

		echo '<div class="thumbnailOverflow">';
		echo '<a href="' .$libString->protectXss($eventUrl). '">';
		echo '<img src="' .$libString->protectXss($eventCoverSource). '" alt="" class="img-responsive center-block" />';
		echo '</a>';
		echo '</div>';

		echo '<div class="caption">';
		echo '<div class="media">';

		echo '<div class="media-left" style="text-align:center">';
		echo '<span style="font-size:32px;line-height:32px">' .substr($row['datum'], 8, 2). '</span><br />';

		$monatName = $libTime->getMonth((int) substr($row['datum'], 5, 2));
		$monatNameSubstr = substr($monatName, 0, 3);
		$monatNameUpper = strtoupper($monatNameSubstr);

		echo '<span style="font-size:12px;line-height:12px;color:#e34e60">' .$monatNameUpper. '</span>';
		echo '</div>';

		echo '<div class="media-body">';

		echo '<h3 style="font-weight:bold;margin-top:0;margin-bottom:0;font-size:14px">';
		echo '<a href="' .$libString->protectXss($eventUrl). '" style="color:black">' .$row['titel']. '</a>';
		echo '</h3>';

		echo '<p style="color:#90949c;margin-top:0;margin-bottom:0;font-size:12px">';
		echo $libString->protectXss($eventInterestedCount). ' Personen sind interessiert';
		echo ' · ';
		echo $libString->protectXss($eventAttendingCount). ' Personen nehmen teil';
		echo '</p>';

		echo '</div>';
		echo '</div>';

		echo '<hr />';

		echo '<p>';
		echo '<a href="' .$libString->protectXss($eventUrl). '">';
		echo '<i class="fa fa-facebook-official fa-lg" aria-hidden="true"></i>';
		echo '</a>';
		echo '</p>';

		echo '</div>';
		echo '</div>';
	}
}
?>