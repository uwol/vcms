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

class LibTimelineEvent
{
    public $title;
    public $datetime;
    public $description;
    public $referencedPersonId;
    public $authorId;
    public $url;
    public $form;

    public $hideAuthorSignature;
    public $hideReferencedPersonSignature;

    public function getBadgeClass()
    {
        return '';
    }

    public function getBadgeIcon()
    {
        return '';
    }

    public function hideAuthorSignature()
    {
        $this->hideAuthorSignature = true;
    }

    public function hideReferencedPersonSignature()
    {
        $this->hideReferencedPersonSignature = true;
    }

    public function isFullWidth()
    {
        return false;
    }

    public function setAuthorId($authorId)
    {
        $this->authorId = $authorId;
    }

    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setForm($form)
    {
        $this->form = $form;
    }

    public function setReferencedPersonId($referencedPersonId)
    {
        $this->referencedPersonId = $referencedPersonId;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function toString()
    {
        global $libPerson, $libTime, $libString;

        $retstr = '<article class="timeline-event">';

        if (!$this->isFullWidth()) {
            $retstr .= '<div class="timeline-badge ' .$this->getBadgeClass(). '">';
            $retstr .= '<span class="reveal">' .$this->getBadgeIcon(). '</span>';
            $retstr .= '</div>';
        }

        $panelTypeClass = $this->isFullWidth() ? 'full-width' : 'with-badge';
        $retstr .= '<div class="timeline-panel ' .$panelTypeClass. ' panel panel-default mb-2">';

        /*
        * heading
        */
        $retstr .= '<div class="panel-heading">';
        $retstr .= '<h3 class="panel-title">';

        if ($this->datetime != '') {
            $retstr .= '<time datetime="' .$libTime->formatUtcString($this->datetime). '">';
            $retstr .= $libTime->formatDateString($this->datetime);
            $retstr .= '</time> ';
        }

        if ($this->url != '') {
            $retstr .= '<a href="' .$this->url. '">';
        }

        $retstr .= $libString->protectXSS((string) $this->title);

        if ($this->url != '') {
            $retstr .=  '</a>';
        }

        $retstr .= '</h3>';
        $retstr .= '</div>';

        /*
        * body
        */
        $retstr .= '<div class="panel-body">';

        // description
        $retstr .= '<div class="row">';

        $hasPersonColumn = ($this->authorId != '' && !$this->hideAuthorSignature)
                || ($this->referencedPersonId != '' && !$this->hideReferencedPersonSignature);

        if ($this->description != '') {
            $retstr .= $hasPersonColumn ? '<div class="col-xs-12 col-sm-9">' : '<div class="col-xs-12">';
            $retstr .= trim((string) $this->description);
            $retstr .= '</div>';
        }

        if ($hasPersonColumn) {
            $retstr .= '<div class="hidden-xs col-sm-3">';

            if ($this->authorId != '' && !$this->hideAuthorSignature) {
                $retstr .= $libPerson->getSignature($this->authorId);
            }

            if ($this->referencedPersonId != '' && !$this->hideReferencedPersonSignature) {
                $retstr .= $libPerson->getSignature($this->referencedPersonId);
            }

            $retstr .= '</div>';
        }

        $retstr .= '</div>';

        // form
        if ($this->form != '') {
            $retstr .= $this->form;
        }

        $retstr .= '</div>';
        $retstr .= '</div>';
        $retstr .= '</article>';

        return $retstr;
    }
}
