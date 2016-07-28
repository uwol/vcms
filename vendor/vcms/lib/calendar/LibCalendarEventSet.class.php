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

namespace vcms\calendar;

class LibCalendarEventSet{
	var $events = array();

	function addEvent($event){
		if($event->getStartDate() != '0000-00-00'){
			if($event->getEndDate() == '0000-00-00' || $event->getEndDate() == '' || $event->getEndDate() < $event->getStartDate()){
				$this->events[$event->getStartDate()][] = $event;
			} else {
				$datesIncludingBetweenDates = $this->getDatesIncludingBetweenDates($event->getStartDate(), $event->getEndDate());

				foreach($datesIncludingBetweenDates as $date){
					$this->events[$date][] = $event;
				}
			}
		}
	}

	function getEventsOfDate($date){
		if(isset($this->events[$date])){
			return $this->events[$date];
		}
	}

	function getDatesIncludingBetweenDates($startDate, $endDate){
		$dates = array();
		$dates[] = $startDate;
		$currentDate = $startDate;

		while($currentDate < $endDate){
			$currentDate = date('Y-m-d', strtotime('+1 day', strtotime($currentDate)));
			$dates[] = $currentDate;
		}

		return $dates;
	}
}