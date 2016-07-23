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

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();


require_once('lib/LibTimeline.class.php');


if(!$libGenericStorage->attributeExistsInCurrentModule('userNameICalendar') || !$libGenericStorage->attributeExistsInCurrentModule('passwordICalendar')){
	$libGenericStorage->saveValueInCurrentModule('userNameICalendar', $libString->randomAlphaNumericString(40));
	$libGenericStorage->saveValueInCurrentModule('passwordICalendar', $libString->randomAlphaNumericString(40));
}


echo '<h1>Intranet-Portal</h1>';

require_once('check/log.php');
require_once('check/system.php');

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();


// -----------------------------------------------------------------------

/*
* semester menu
*/

$stmt = $libDb->prepare('SELECT * FROM base_semester ORDER BY SUBSTRING(semester, 3) DESC');
$stmt->execute();

$semesters = array();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$semesters[] = $row['semester'];
}

echo $libTime->getSemesterMenu($semesters, $libGlobal->semester);

$zeitraum = $libTime->getZeitraum($libGlobal->semester);
$zeitraumLimit = 14;


// -----------------------------------------------------------------------

/*
* timeline
*/

$timelineEventSet = new LibTimelineEventSet();

require_once('timeline/now.php');
require_once('timeline/semester.php');
require_once('timeline/birthdays.php');
require_once('timeline/deceased.php');
require_once('timeline/wedding.php');
require_once('timeline/events.php');

if($libModuleHandler->moduleIsAvailable('mod_intranet_news')){
	require_once('timeline/news.php');
}

if($libModuleHandler->moduleIsAvailable('mod_intranet_chargierkalender')){
	require_once('timeline/chargierkalender.php');
}

if($libModuleHandler->moduleIsAvailable('mod_intranet_reservierungen')){
	require_once('timeline/reservations.php');
}


// -----------------------------------------------------------------------

echo '<div class="timeline">';
echo '<div class="timeline-divider"></div>';
echo '<div class="timeline-body">';

$timelineEventSet->sortEvents();
$timelineEvents = $timelineEventSet->getEvents();

foreach($timelineEvents as $timelineEvent){
	echo $timelineEvent->toString();
}

echo '</div>';
echo '<div class="timeline-footer"></div>';
echo '</div>';
?>