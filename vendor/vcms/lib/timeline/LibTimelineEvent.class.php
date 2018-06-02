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

namespace vcms\timeline;

class LibTimelineEvent {
	var $title;
	var $datetime;
	var $description;
	var $referencedPersonId;
	var $authorId;
	var $url;
	var $form;

	var $hideAuthorSignature;
	var $hideReferencedPersonSignature;

	function getBadgeClass(){
		return '';
	}

	function getBadgeIcon(){
		return '';
	}

	function hideAuthorSignature(){
		$this->hideAuthorSignature = true;
	}

	function hideReferencedPersonSignature(){
		$this->hideReferencedPersonSignature = true;
	}

	function isFullWidth(){
		return false;
	}

	function setAuthorId($authorId){
		$this->authorId = $authorId;
	}

	function setDatetime($datetime){
		$this->datetime = $datetime;
	}

	function setDescription($description){
		$this->description = $description;
	}

	function setForm($form){
		$this->form = $form;
	}

	function setReferencedPersonId($referencedPersonId){
		$this->referencedPersonId = $referencedPersonId;
	}

	function setTitle($title){
		$this->title = $title;
	}

	function setUrl($url){
		$this->url = $url;
	}

	function toString(){
		global $libPerson, $libTime;

		$retstr = '<article class="timeline-event">';

		if(!$this->isFullWidth()){
			$retstr .= '<div class="timeline-badge ' .$this->getBadgeClass(). '">';
			$retstr .= '<span class="reveal">' .$this->getBadgeIcon(). '</span>';
			$retstr .= '</div>';
		}

		$panelTypeClass = $this->isFullWidth() ? 'full-width' : 'with-badge';
		$retstr .= '<div class="timeline-panel ' .$panelTypeClass. ' panel panel-default">';

		/*
		* heading
		*/
		$retstr .= '<div class="panel-heading">';
		$retstr .= '<h3 class="panel-title">';

		if($this->datetime != ''){
			$retstr .= '<time class="text-muted" datetime="' .$libTime->formatUtcString($this->datetime). '">';
			$retstr .= $libTime->formatDateString($this->datetime);
			$retstr .= '</time> ';
		}

		if($this->url != ''){
			$retstr .= '<a href="' .$this->url. '">';
		}

		$retstr .= $this->title;

		if($this->url != ''){
			$retstr .=  '</a>';
		}

		$retstr .= '</h3>';
		$retstr .= '</div>';

		/*
		* body
		*/
		$retstr .= '<div class="panel-body">';

		// description
		$retstr .= '<div class="media">';

		if($this->description != ''){
			$retstr .= '<div class="media-body">';
			$retstr .= trim($this->description);
			$retstr .= '</div>';
		}

		if(($this->authorId != '' && !$this->hideAuthorSignature)
				|| ($this->referencedPersonId != '' && !$this->hideReferencedPersonSignature)){
			$retstr .= '<div class="media-right hidden-xs">';

			if($this->referencedPersonId != '' && !$this->hideReferencedPersonSignature){
				$retstr .= $libPerson->getSignature($this->authorId);
			}

			if($this->referencedPersonId != '' && !$this->hideReferencedPersonSignature){
				$retstr .= $libPerson->getSignature($this->referencedPersonId);
			}

			$retstr .= '</div>';
		}

		$retstr .= '</div>';

		// form
		if($this->form != ''){
			$retstr .= $this->form;
		}

		$retstr .= '</div>';
		$retstr .= '</div>';
		$retstr .= '</article>';

		return $retstr;
	}
}
