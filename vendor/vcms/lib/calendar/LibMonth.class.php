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

class LibMonth{
	var $year;
	var $number;
	var $days = array();

	function __construct($year, $number, $startDate, $endDate){
		$this->year = $year;
		$this->number = $number;

		//is start of month restricted?
		if(substr($startDate, 0, 4) == $year->getNumber()
			&& substr($startDate, 5, 2) == $number){
			$startDay = intval(substr($startDate, 8, 2));
		} else {
			$startDay = 1;
		}

		//is end of month restricted?
		if(substr($endDate, 0, 4) == $year->getNumber()
			&& substr($endDate, 5, 2) == $number){
			$endDay = intval(substr($endDate, 8, 2));
		} else {
			$endDay = $this->getNumberOfDays();
		}

		//generate days
		for($i=$startDay; $i <= $endDay && $i <= $this->getNumberOfDays(); $i++){
			$this->days[$i] = new LibDay($this, $i);
		}
	}

	function getYear(){
		return $this->year;
	}

	function getNumber(){
		return $this->number;
	}

	function getNumberOfDays(){
		return 31 - ((($this->number - (($this->number < 8)?1:0)) % 2) + (($this->number == 2)?((!($this->year->getNumber() % ((!($this->year->getNumber() % 100))?400:4)))?1:2):0));
	}

	function hasEvents($eventSet){
		foreach($this->days as $day){
			if($day->hasEvents($eventSet)){
				return true;
			}
		}

		return false;
	}

	function toString($eventSet, $weekShift=1){
		$dayNames = array('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag');

		$retstr = '';

		$weekShift = $weekShift % 7;

		//heading with day names
		$retstr .= '<div class="calendarCellContainer">'.PHP_EOL;

		for($i=0+$weekShift; $i<count($dayNames)+$weekShift; $i++){
			$retstr .= '<div class="calendarCell calendarDayName hidden-xs">';
			$retstr .= $dayNames[$i % 7];
			$retstr .= '</div>'.PHP_EOL;
		}

		$retstr .= '</div>'.PHP_EOL;

		//week
		$retstr .= '<div class="calendarCellContainer">'.PHP_EOL;

		$dayCounter = 1;
		$colCounter = 0 + $weekShift;

		//as long, as there are columns left for output
		while($colCounter != $weekShift || $dayCounter < count($this->days)+1){
			$dayExists = isset($this->days[$dayCounter]);

			//as long, as there are days left for output
			if($dayExists && $this->days[$dayCounter]->getType() == $colCounter){
				$retstr .= $this->days[$dayCounter]->toString($eventSet);
				$dayCounter++;
			} else {
				$retstr .= '<div class="calendarCell hidden-xs"></div>'.PHP_EOL;
			}

			$colCounter = ($colCounter + 1) % 7;
		}

		$retstr .= '</div>'.PHP_EOL;
		return $retstr;
	}
}