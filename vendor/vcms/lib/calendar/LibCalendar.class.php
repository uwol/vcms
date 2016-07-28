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

class LibCalendar{
	var $years = array();
	var $eventSet;

	function __construct($startDate, $endDate){
		$this->eventSet = new LibCalendarEventSet();
		$startYear = intval(substr($startDate, 0, 4));
		$endYear = intval(substr($endDate, 0, 4));

		for($i = $startYear; $i <= $endYear; $i++){
			$this->years[] = new LibYear($i, $startDate, $endDate);
		}
	}

	function addEvent($event){
		$this->eventSet->addEvent($event);
	}

	function toString(){
		$retstr = '';

		foreach($this->years as $year){
			$retstr .= $year->toString($this->eventSet);
		}

		return $retstr;
	}
}