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

class LibDay{
	var $month;
	var $number;
	var $type; //0 - sunday ... 6 - saturday

	function __construct($month, $number){
		$this->month = $month;
		$this->number = $number;
		$this->type = $this->getDayOfWeek();
	}

	function getDayOfWeek(){
		$year = $this->month->getYear();
		$a = intval((14 - $this->month->getNumber()) / 12);
		$y = $year->getNumber() - $a;
		$m = $this->month->getNumber() + (12 * $a) - 2;

		return ($this->number + $y + intval($y/4) - intval($y/100) + intval($y/400) + intval((31*$m)/12) ) % 7;
	}

	function getDayOfWeekByTimestamp(){
		$year = $this->month->getYear();
		return @date('w', @mktime(0, 0, 0, $this->month->getNumber(), $this->number, $year->getNumber()));
	}

	function getNumber(){
		return $this->number;
	}

	function getType(){
		return $this->type;
	}

	function getDate(){
		$year = $this->month->getYear();
		$yearNumber = $year->getNumber();
		$monthNumber = str_pad($this->month->getNumber(), 2, 0, STR_PAD_LEFT);
		$dayNumber = str_pad($this->number, 2, 0, STR_PAD_LEFT);
		return $yearNumber.'-'.$monthNumber.'-'.$dayNumber;
	}

	function getEvents($eventSet){
		return $eventSet->getEventsOfDate($this->getDate());
	}

	function hasEvents($eventSet){
		$events = $this->getEvents($eventSet);
		return is_array($events) && sizeof($events > 0);
	}

	function isToday(){
		$year = $this->month->getYear();

		if($this->number == @date('j')){
			if($this->month->getNumber() == @date('n')){
				if($year->getNumber() == @date('Y')){
					return true;
				}
			}
		}

		return false;
	}

	function toString($eventSet){
		//print events of day
		$hasEvents = $this->hasEvents($eventSet);
		$events = $this->getEvents($eventSet);

		$todayClass = $this->isToday() ? ' today' : '';
		$hiddenClass = $hasEvents ? '' : ' hidden-xs';

		//header
		$retstr = '';
		$retstr .= '<div class="calendarCell calendarDay' .$todayClass.$hiddenClass. '">';
		$retstr .= $this->number;

		if($hasEvents){
			foreach($events as $event){
				$retstr .= $event->toString($this->getDate());
			}
		}

		//footer
		$retstr .= '</div>'.PHP_EOL;
		return $retstr;
	}
}