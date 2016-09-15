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

class LibCalendarEvent{
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
	var $attended;
	var $attendedIcon;

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

	function isAttended($attended){
		$this->attended = $attended;
	}

	function setAttendedIcon($attendedIcon){
		$this->attendedIcon = $attendedIcon;
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
		return substr($dateTime, 0, 10);
	}

	function toString($forDate = ''){
		//optionally, $forDate contains the date this event should be printed for
		//this is relevant for multi-day events, that should not contain time information for
		//days between startDate and endDate
		global $libString, $libTime, $libEvent;

		$retstr = '';
		$timeString = '';

		//format timeString
		if(!$this->allDay){ //event with timeinfo?
			if($forDate == $this->getStartDate()){
				$timeString = $libTime->formatTimeString($this->startDateTime);
			}
		}

		/*
		* print event
		*/
		$retstr .= '<div id="t' .$this->id. '_' .$forDate. '" class="calendarEvent">';
		$retstr .= '<div><time datetime="' .$libTime->formatUtcString($this->startDateTime). '">' .$timeString. '</time></div>';

		//link
		if($this->linkUrl != ''){
			$retstr .= '<a href="' .$this->linkUrl. '">';
		}

		//summary
		$retstr .= '<div>';
		$retstr .= $this->summary;
		$retstr .= '</div>';

		//image
		if($this->imageUrl != ''){
			$retstr .= '<div class="thumbnail">';
			$retstr .= '<div class="thumbnailOverflow">';
			$retstr .= '<img class="img-responsive center-block" src="'.$this->imageUrl.'" alt="Foto" />';
			$retstr .= '</div>';
			$retstr .= '</div>';
		}

		if($this->linkUrl != ''){
			$retstr .= '</a>';
		}

		//description
		if($this->description != ''){
			$retstr .= '<div>';
			$retstr .= $this->description;
			$retstr .= '</div>';
		}

		//location
		$retstr .= '<address>';

		if($this->location != ''){
			$retstr .= '<span>' .$this->location. '</span>';
		}

		$retstr .= '</address>';

		//attended
		if($this->attended && $this->attendedIcon != ''){
			$retstr .= $this->attendedIcon. ' ';
		}

		//status
		if($this->status != ''){
			$retstr .= '<span class="status">';
			$retstr .= $this->status;
			$retstr .= '</span>';
		}

		//footer
		$retstr .= '</div>';

		return $retstr;
	}
}
