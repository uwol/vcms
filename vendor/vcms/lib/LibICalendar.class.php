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

namespace vcms;

class LibICalendar{

	var $events = array();

	function addEvent($event){
		$this->events[] = $event;
	}

	function printCalendar(){
		$br = chr(13).chr(10); //define line break, RFC 5545 chapter 3.1

		$retstr = '';

		$retstr .= 'BEGIN:VCALENDAR'.$br;
		$retstr .= 'VERSION:2.0'.$br;
		$retstr .= 'PRODID:-//vcms//icalendar.class.php'.$br;
		$retstr .= 'X-WR-TIMEZONE:Europe/Berlin'.$br;

		$retstr .= 'METHOD:PUBLISH'.$br;

		foreach($this->events as $event){
			$retstr .= $event->getEvent();
		}

		$retstr .= 'END:VCALENDAR'.$br;

		/*
		* encoding must be UTF-8, RFC 5545 chapter 6
		*/

		if(!isset($_SERVER['HTTP_USER_AGENT'])){
			header('Content-Type: text/calendar');
			echo $retstr;
		}
		//Google Calendar: Mozilla/5.0 (compatible; Googlebot/2.1;+http://www.google.com/bot.html)
		elseif(stristr($_SERVER['HTTP_USER_AGENT'], 'Google')){
			header('Content-Type: text/calendar; charset=UTF-8');
			echo $retstr;
		}
		//iCal: DAVKit/3.0.6 (653); CalendarStore/3.0.6 (847); iCal/3.0.6 (1273); Mac OS X/10.5.6 (9G55)
		elseif(stristr($_SERVER['HTTP_USER_AGENT'], 'iCal')){
			header('Content-Type: text/calendar; charset=UTF-8');
			echo $retstr;
		}
		//Outlook: Microsoft Office/12.0 (Windows NT 6.1; Microsoft Office Outlook 12.0.6425; Pro)
		elseif(stristr($_SERVER['HTTP_USER_AGENT'], 'Outlook')){
			header('Content-Type: text/calendar');
			echo $retstr;
		}
		//Thunderbird: Mozilla/5.0 (Windows; U; Windows NT 6.1; de; rv:1.8.1.23) Gecko/20090812 Lightning/0.9 Thunderbird/2.0.0.23
		elseif(stristr($_SERVER['HTTP_USER_AGENT'], 'Lightning') || strstr($_SERVER['HTTP_USER_AGENT'], 'Thunderbird')){
			header('Content-Type: text/calendar');
			echo utf8_encode(utf8_decode($retstr));
		}
		//remaining client types
		else {
			header('Content-Type: text/calendar');
			echo $retstr;
		}
	}
}