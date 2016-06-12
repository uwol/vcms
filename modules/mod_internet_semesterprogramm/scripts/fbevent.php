<?php
if(!is_object($libGlobal))
	exit();


/*
* actions
*/

$id = '';
if(isset($_REQUEST['eventid'])){
	$id = $_REQUEST['eventid'];
}

if($id == ''){
	exit;
}

$stmt = $libDb->prepare('SELECT * FROM base_veranstaltung WHERE id=:id');
$stmt->bindValue(':id', $id);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if($libEvent->isFacebookEvent($row)){
	$fbAccessToken = $libGenericStorage->loadValue('mod_internet_home', 'fbAccessToken');
	$fbEventId = $row['fb_eventid'];

	$fbUrl = 'https://www.facebook.com';
	$fbGraphUrl = 'https://graph.facebook.com';
	$fbAccessTokenQuery = '?access_token=' .$fbAccessToken;
	
	$fbEventPhotosEndpoint = $fbGraphUrl. '/' .$fbEventId. '/photos' .$fbAccessTokenQuery;
	$fbEventInterestedEndpoint = $fbGraphUrl. '/' .$fbEventId. '/interested' .$fbAccessTokenQuery. '&summary=count';
	$fbEventAttendingEndpoint = $fbGraphUrl. '/' .$fbEventId. '/attending' .$fbAccessTokenQuery. '&summary=count';

	$eventLink = $fbUrl. '/events/' .$fbEventId;

	$eventPhotosJson = file_get_contents($fbEventPhotosEndpoint);
	$eventPhotosObject = json_decode($eventPhotosJson, true);
	$eventPhotoSource = $eventPhotosObject['data'][0]['source'];

	$eventInterestedJson = file_get_contents($fbEventInterestedEndpoint);
	$eventInterestedObject = json_decode($eventInterestedJson, true);
	$eventInterestedCount = $eventInterestedObject['summary']['count'];

	$eventAttendingJson = file_get_contents($fbEventAttendingEndpoint);
	$eventAttendingObject = json_decode($eventAttendingJson, true);
	$eventAttendingCount = $eventAttendingObject['summary']['count'];

	// ------------------

	echo '<div class="thumbnail">';

	echo '<div class="thumbnailOverflow">';
	echo '<a href="' .$libString->protectXss($eventLink). '">';
	echo '<img src="' .$libString->protectXss($eventPhotoSource). '" alt="" class="img-responsive center-block" />';
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

	echo '<h4 style="font-weight:bold;margin-top:0;margin-bottom:0;font-size:14px">';
	echo '<a href="' .$libString->protectXss($eventLink). '" style="color:black">' .$row['titel']. '</a>';
	echo '</h4>';

	echo '<p style="color:#90949c;margin-top:0;margin-bottom:0;font-size:12px">';
	echo $libString->protectXss($eventInterestedCount). ' Personen sind interessiert';
	echo ' · ';
	echo $libString->protectXss($eventAttendingCount). ' Personen nehmen teil';
	echo '</p>';

	echo '</div>';
	echo '</div>';

	echo '<hr />';
	echo '<a href="' .$libString->protectXss($eventLink). '">';
	echo '<img src="styles/icons/social/facebook.svg" alt="FB" class="icon" />';
	echo '</a>';

	echo '</div>';
	echo '</div>';
}
?>