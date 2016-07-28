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

class LibYear{
	var $number;
	var $months = array();

	function __construct($number, $startDate, $endDate){
		if($number != '0' && $number == ''){
			$number = @date('Y');
		}

		$this->number = $number;

		//is start of year restricted?
		if(substr($startDate, 0, 4) == $number){
			$startMonth = max(intval(substr($startDate, 5, 2)), 1);
		} else {
			$startMonth = 1;
		}

		//is end of year restricted?
		if(substr($endDate, 0, 4) == $number){
			$endMonth = intval(substr($endDate, 5, 2));
		} else {
			$endMonth = 12;
		}

		//generate months
		for($i=$startMonth; $i<=$endMonth; $i++){
			$this->months[$i] = new LibMonth($this, $i, $startDate, $endDate);
		}
	}

	function getNumber(){
		return $this->number;
	}

	function toString($eventSet){
		$monthNames = array('Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');

		$retstr = '';
		$retstr .= '<div class="calendar">'.PHP_EOL;

		foreach($this->months as $month){
			$hasEvents = $month->hasEvents($eventSet);
			$hiddenClass = $hasEvents ? '' : ' hidden-xs';

			$retstr .= '<div class="calendarMonthName' .$hiddenClass. '">';
			$retstr .= '<h2>' . $monthNames[$month->getNumber()-1]. ' ' .$this->number. '</h2>';
			$retstr .= '</div>';
			$retstr .= $month->toString($eventSet);
		}

		$retstr .= '</div>';
		return $retstr;
	}
}