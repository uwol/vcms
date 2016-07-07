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


class LibNowTimelineEvent extends LibTimelineEvent{
	function getBadgeClass(){
		return '';
	}

	function isFullWidth(){
		return true;
	}

	function toString(){
		return '<hr />';
	}
}

$now = date('Y-m-d H:i:s');

if($zeitraum[0] <= $now && $now <= $zeitraum[1]){
	$timelineEvent = new LibNowTimelineEvent();
	$timelineEvent->setDatetime(date('Y-m-d H:i:s'));
	$timelineEventSet->addEvent($timelineEvent);
}
?>