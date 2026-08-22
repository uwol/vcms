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

namespace vcms\menu;

class LibMenuFolder extends LibMenuElement
{
    public $elements = [];

    public function __construct($pid, $name, $position)
    {
        parent::__construct($pid, $name, $position, 2);
    }

    public function addElement($element)
    {
        $this->elements[] = $element;
        $this->id = substr(sha1($element->getId().$this->id), 0, 8);
    }

    public function addElements($elements)
    {
        $this->elements = array_merge($this->elements, $elements);
    }

    public function setElements($menuElements)
    {
        $this->elements = $menuElements;
    }

    public function getElements()
    {
        return $this->elements;
    }

    public function hasElements()
    {
        return !empty($this->elements);
    }

    public function canonizeElements()
    {
        $result = [];

        foreach ($this->elements as $element) {
            $name = $element->getName();
            $type = $element->getType();
            $position = $element->getPosition();

            if (!isset($result[$name])) {
                $result[$name] = $element;
            } elseif ($type == 2) {
                $collidingElement = $result[$name];
                $collidingElement->addElements($element->getElements());
                $newPosition = min($collidingElement->getPosition(), $position);
                $collidingElement->setPosition($newPosition);
            }
        }

        $result = array_values($result);
        $this->setElements($result);
    }

    public function sortElementsByPosition()
    {
        $elementsNew = [];

        $minPositionValue = -1;
        $minPosition = -1;

        $temp = array_values($this->elements);

        while (count($temp) > 0) {
            for ($i = 0; $i < count($temp); $i++) {
                $position = $temp[$i]->getPosition();

                if ($minPositionValue == -1 || $position < $minPositionValue) {
                    $minPositionValue = $position;
                    $minPosition = $i;
                }
            }

            $elementsNew[] = $temp[$minPosition];

            //clean up
            unset($temp[$minPosition]);
            $minPosition = -1;
            $minPositionValue = -1;

            //build temp array for correct indices
            $temp = array_values($temp);
        }

        $this->elements = $elementsNew;

        //no foreach, as otherwise php4 does copy-by-value
        for ($i = 0; $i < count($this->elements); $i++) {
            $element = $this->elements[$i];

            if ($element->getType() == 2) {
                $element->sortElementsByPosition();
            }
        }
    }

    public function reduceByAccessRestriction($group, $offices)
    {
        //no foreach, as otherwise php4 does copy-by-value
        for ($i = 0; $i < count($this->elements); $i++) {
            $element = $this->elements[$i];

            //menu folder?
            if ($element->getType() == 2) {
                $element->reduceByAccessRestriction($group, $offices);
            }
        }

        $elementsNew = [];

        //no foreach, as otherwise php4 does copy-by-value
        for ($i = 0; $i < count($this->elements); $i++) {
            $element = $this->elements[$i];

            //menu entry?
            if (!$element->hasAccessRestriction()) {
                $elementsNew[] = $element;
            } else {
                $accessRestriction = $element->getAccessRestriction();

                //restrict
                if ($accessRestriction->isFulfilledBy($group, $offices)) {
                    $elementsNew[] = $element;
                }
            }
        }

        $this->elements = $elementsNew;
    }

    public function applyMinAccessRestriction()
    {
        global $libSecurityManager;

        //no foreach, as otherwise php4 does copy-by-value
        //apply min access restriction recursively
        for ($i = 0; $i < count($this->elements); $i++) {
            $element = $this->elements[$i];

            //a folder?
            if ($element->getType() == 2) {
                //without access restriction?
                if (!$element->hasAccessRestriction()) {
                    //generate access restriction
                    $element->applyMinAccessRestriction();
                }
            }
        }

        $accessRestrictions = [];
        //no foreach, as otherwise php4 does copy-by-value
        //collect access restrictions of a folder
        for ($i = 0; $i < count($this->elements); $i++) {
            $element = $this->elements[$i];

            //element with access restriction?
            if ($element->hasAccessRestriction()) {
                $accessRestrictions[] = $element->getAccessRestriction();
            }
        }

        //aggregate access restrictions
        $accessRestriction = $libSecurityManager->
            generateAggregatedAccessRestriction($accessRestrictions);

        if ($accessRestriction->hasGroupsRestriction() ||
            $accessRestriction->hasOfficesRestriction()) {
            $this->accessRestriction = $accessRestriction;
        }
    }

    public function setAccessRestrictionOfMenuElement($mid, $accessRestriction)
    {
        //no foreach, as otherwise php4 does copy-by-value
        for ($i = 0; $i < count($this->elements); $i++) {
            $element = $this->elements[$i];

            if ($element->getId() == $mid) {
                $element->setAccessRestriction($accessRestriction);
            }
        }
    }

    public function copy()
    {
        $menuFolder	= new LibMenuFolder($this->pid, $this->name, $this->position);
        $menuFolder->id = $this->id;
        $menuFolder->type = $this->type;
        $menuFolder->accessRestriction = $this->accessRestriction;

        //no foreach, as otherwise php4 does copy-by-value
        for ($i = 0; $i < count($this->elements); $i++) {
            $element = $this->elements[$i];
            $menuFolder->elements[] = $element->copy();
        }

        return $menuFolder;
    }
}
