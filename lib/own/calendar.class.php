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

class LibCalendar{
	var $years = array();
	var $eventSet;

	function __construct($startDate, $endDate){
		$this->eventSet = new LibEventSet();
		$startYear = intval(substr($startDate,0,4));
		$endYear = intval(substr($endDate,0,4));

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
		$retstr .= '<table class="calendarYear">'."\n\r";

		foreach($this->months as $month){
			$retstr .= '<tr><td colspan="7" ';
			$retstr .= ' class="calendarMonth"><h2>' . $monthNames[$month->getNumber()-1]. ' ' .$this->number. '</h2></td></tr>';
			$retstr .= $month->toString($eventSet);
		}

		$retstr .= '</table>';
		return $retstr;
	}
}

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
		return 31-((($this->number-(($this->number<8)?1:0))%2)+(($this->number==2)?((!($this->year->getNumber()%((!($this->year->getNumber()%100))?400:4)))?1:2):0));
	}

	function toString($eventSet, $weekShift=1){
		$dayNames = array('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag');
		$retstr = '';
		$br = "\n\r";
		$weekShift = $weekShift % 7;

		//heading with day names
		$retstr .= '<tr>'.$br;

		for($i=0+$weekShift;$i<count($dayNames)+$weekShift;$i++){
			$retstr .= '<td class="calendarDayName">';
			$retstr .= $dayNames[$i % 7];
			$retstr .= '</td>'.$br;
		}

		$retstr .= '</tr>'.$br;
		$retstr .= '<tr>'.$br;

		//weeks
		$dayCounter = 1;
		$colCounter = 0 + $weekShift;

		//as long, as there are days left for output
		while($dayCounter < count($this->days)+1){
			$day = $this->days[$dayCounter];

			if($day->getType() == $colCounter){
				$retstr .= $day->toString($eventSet);

				if($day->getType() == (6 + $weekShift) % 7){
					$retstr .= '</tr><tr>'.$br;
				}

				$dayCounter++;
			} else {
				$retstr .= '<td></td>'.$br;
			}

			$colCounter = ($colCounter + 1) % 7;
		}

		$retstr .= '</tr>'.$br;
		return $retstr;
	}
}

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
		$retstr = '';

		if($this->isToday()){
			$class = 'today';
			$aktuell = '<a id="aktuell"></a>';
		} else {
			$aktuell = '';
		}

		//header
		$retstr .= '<td class="calendarDay ' .$class .'">';
		$retstr .= $aktuell;
		$retstr .= '<b>' .$this->number. '</b><br /><br />';

		//print events of day
		$year = $this->month->getYear();
		$yearNumber = $year->getNumber();
		$monthNumber = str_pad($this->month->getNumber(), 2, 0, STR_PAD_LEFT);
		$dayNumber = str_pad($this->number, 2, 0, STR_PAD_LEFT);
		$events = $eventSet->getEventsOfDate($yearNumber.'-'.$monthNumber.'-'.$dayNumber);

		if(is_array($events)){
			foreach($eventSet->getEventsOfDate($yearNumber.'-'.$monthNumber.'-'.$dayNumber) as $event){
				$retstr .= $event->toString($yearNumber.'-'.$monthNumber.'-'.$dayNumber);
			}
		}

		//footer
		$retstr .= '</td>'."\n\r";
		return $retstr;
	}
}

//-------------------------------------------------------------------------------------

class LibEventSet{
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

class LibEvent{
	//time infos
	var $startDateTime; //2008-12-24 20:15:00
	var $endDateTime;
	var $allDay;

	//event infos
	var $id;
	var $summary;
	var $description;
	var $category;
	var $status;
	var $location;
	var $linkUrl;
	var $imageUrl;

	//meta infos
	var $timeStyle; // 0-Normal, 1-s.t./c.t.
	var $attended;
	var $attendedImageUrl;

	//-------------------------------------------------

	function __construct($startDateTime){
		$this->startDateTime = $startDateTime;
	}

	function setEndDateTime($endDateTime){
		$this->endDateTime = $endDateTime;
	}

	function isAllDay($allDay){
		$this->allDay = $allDay;
	}

	function setId($id){
		$this->id = $id;
	}

	function setSummary($summary){
		$this->summary = $summary;
	}

	function setDescription($description){
		$this->description = $description;
	}

	function setCategory($category){
		$this->category = $category;
	}

	function setStatus($status){
		$this->status = $status;
	}

	function setLocation($location){
		$this->location = $location;
	}

	function setLinkUrl($linkUrl){
		$this->linkUrl = $linkUrl;
	}

	function setImageUrl($imageUrl){
		$this->imageUrl = $imageUrl;
	}

	function setTimeStyle($timeStyle){
		$this->timeStyle = $timeStyle;
	}

	function isAttended($attended){
		$this->attended = $attended;
	}

	function setAttendedImageUrl($attendedImageUrl){
		$this->attendedImageUrl = $attendedImageUrl;
	}

	function getStartDateTime(){
		return $this->startDateTime;
	}

	function getStartDate(){
		return $this->getDateOfDateTime($this->startDateTime);
	}

	function getEndDateTime(){
		return $this->endDateTime;
	}

	function getEndDate(){
		return $this->getDateOfDateTime($this->endDateTime);
	}

	function getDateOfDateTime($dateTime){
		return substr($dateTime,0,10);
	}

	function getTimeOfDateTime($dateTime){
		return substr($dateTime,11,5);
	}

	function toString($forDate = ''){
		//optionally, $forDate contains the date this event should be printed for
		//this is relevant for multi-day events, that should not contain time information for
		//days between startDate and endDate
		global $libString;

		$retstr = '';
		$timeString = '';

		//format timeString
		if(!$this->allDay){ //event with timeinfo?
			if($forDate == $this->getStartDate()){
				$timeString = $this->getTimeOfDateTime($this->startDateTime);

				//s.t./c.t. ?
				if($this->timeStyle == 1){
					if(substr($timeString,3,2) == 00){
						$timeString = substr($timeString,0,2).'h s.t.';
					} elseif(substr($timeString,3,2) == 15){
						$timeString = substr($timeString,0,2).'h c.t.';
					}
				}
			}
		}

		//format dateTime for microformats
		$dtstart = substr($this->startDateTime,0,4) . substr($this->startDateTime,5,2) . substr($this->startDateTime,8,2);

		if(!$this->allDay){
			$dtstart .= 'T'. substr($this->startDateTime,11,2) . substr($this->startDateTime,14,2) . substr($this->startDateTime,17,2);
		}

		/*
		* print event
		*/
		$idSuffix = '';

		if($forDate != ''){
			$idSuffix = '_'.$forDate;
		}

		$retstr .= '<div class="calendarEvent vevent"><a id="t' .$this->id . $idSuffix .'"></a>';
		$retstr .= '<abbr class="dtstart" title="' .$dtstart. '"><b>'.$timeString.'</b></abbr><br />';

		//link
		if($this->linkUrl != ''){
			$retstr .= '<a href="' .$this->linkUrl. '">';
		}

		//summary
		$retstr .= '<span class="summary">';
		$retstr .= $libString->silbentrennung($this->summary, 14);
		$retstr .= '</span><br />';

		//image
		if($this->imageUrl != ''){
			$retstr .= '<img class="calendarEventImg" src="'.$this->imageUrl.'" alt="Foto" /><br />';
		}

		if($this->linkUrl != ''){
			$retstr .= '</a>';
		}

		//description
		if($this->description != ''){
			$retstr .= $this->description.'<br />';
		}

		//location
		if($this->location != ''){
			$retstr .= '<span class="location">' . $libString->silbentrennung($this->location, 14) . '</span><br />';
		}

		//status
		if($this->status != ''){
			$retstr .= '<b>'.$this->status.'</b> ';
		}

		//attended
		if($this->attended && $this->attendedImageUrl != ''){
			$retstr .= '<img src="'.$this->attendedImageUrl.'" alt="angemeldet" /> ';
		}

		//footer
		$retstr .= '</div><br />';

		return $retstr;
	}
}
?>