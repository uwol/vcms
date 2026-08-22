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

namespace vcms\module;

class LibAccessRestriction
{
    public $groups;
    public $offices;

    public function __construct($groups, $offices)
    {
        $this->groups = $groups;
        $this->offices = $offices;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function getOffices()
    {
        return $this->offices;
    }

    public function hasGroupsRestriction()
    {
        return is_array($this->groups) && count($this->groups) > 0;
    }

    public function hasOfficesRestriction()
    {
        return is_array($this->offices) && count($this->offices) > 0;
    }

    public function isFulfilledBy($group, $offices)
    {
        $groupsOk = false;

        //should this restriction be restricted by group membership?
        if (is_array($this->groups) && count($this->groups) > 0) {
            if (in_array($group, $this->groups)) {
                $groupsOk = true;
            }
        } else {
            $groupsOk = true;
        }

        $officesOk = false;

        //should this restriction be restricted by function?
        if (is_array($this->offices) && count($this->offices) > 0) {
            if (is_array($offices)) {
                foreach ($offices as $office) {
                    if (in_array($office, $this->offices)) {
                        $officesOk = true;
                    }
                }
            }
        } else {
            $officesOk = true;
        }

        return $groupsOk && $officesOk;
    }
}
