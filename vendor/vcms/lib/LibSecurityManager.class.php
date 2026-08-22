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

class LibSecurityManager
{
    public $possibleOffices = [
            'senior', 'consenior', 'fuchsmajor', 'fuchsmajor2', 'scriptor', 'quaestor', 'jubelsenior',
            'ahv_senior', 'ahv_consenior', 'ahv_keilbeauftragter', 'ahv_scriptor', 'ahv_quaestor', 'ahv_beisitzer1', 'ahv_beisitzer2',
            'hv_vorsitzender', 'hv_kassierer', 'hv_beisitzer1', 'hv_beisitzer2',
            'archivar', 'ausflugswart',
            'bierwart', 'bootshauswart',
            'couleurartikelwart',
            'dachverbandsberichterstatter', 'datenpflegewart',
            'fechtwart', 'ferienordner', 'fotowart',
            'hauswart', 'huettenwart',
            'internetwart',
            'kuehlschrankwart',
            'musikwart',
            'redaktionswart',
            'sportwart', 'stammtischwart',
            'technikwart', 'thekenwart',
            'wirtschaftskassenwart', 'wichswart',
            'vop', 'vvop', 'vopxx', 'vopxxx', 'vopxxxx'];

    public function getPossibleOffices()
    {
        return $this->possibleOffices;
    }

    public function hasAccess($libElement, $libAuth)
    {
        //public page?
        if (!$libElement->hasAccessRestriction()) {
            return true;
        } else { //internal page?
            //not logged in?
            if (!$libAuth->isLoggedIn()) {
                return false;
            }

            $accessRestriction = $libElement->getAccessRestriction();

            //enough rights?
            if ($accessRestriction->isFulfilledBy($libAuth->getGroup(), $libAuth->getOffices())) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function generateAggregatedAccessRestriction($accessRestrictions)
    {
        $includedGroups = [];
        $modifyGroups = true;
        $freshGroups = true;

        $includedOffices = [];
        $modifyOffices = true;
        $freshOffices = true;

        //no foreach, as otherwise php4 does copy-by-value!
        for ($i = 0; $i < count($accessRestrictions); $i++) {
            $accessRestriction = $accessRestrictions[$i];

            /*
            * aggregate groups
            */
            if ($modifyGroups) {
                //element without group restriction?
                if (!$accessRestriction->hasGroupsRestriction()) {
                    //remove group filder
                    $includedGroups = [];
                    //protect group filter from modification
                    $modifyGroups = false;
                    $freshGroups = false;
                }
                //element with group restriction
                else {
                    //first iteration
                    if ($freshGroups) {
                        //add all groups
                        $includedGroups = $accessRestriction->getGroups();
                        $freshGroups = false;
                    } else {
                        //remove all groups not contained in the restriction
                        $includedGroups = array_unique(array_merge(
                            $includedGroups,
                            $accessRestriction->getGroups()
                        ));
                    }
                }
            }

            /*
            * aggregate functions
            */
            if ($modifyOffices) {
                //element without function restriction?
                if (!$accessRestriction->hasOfficesRestriction()) {
                    //remove function filter
                    $includedOffices = [];
                    //protect function filter from modification
                    $modifyOffices = false;
                    $freshOffices = false;
                }
                //element with function restriction
                else {
                    //if first iteration
                    if ($freshOffices) {
                        //add all functions
                        $includedOffices = $accessRestriction->getOffices();
                        $freshOffices = false;
                    } else {
                        //remove all functions not contained in the restriction
                        $includedOffices = array_unique(array_merge(
                            $includedOffices,
                            $accessRestriction->getOffices()
                        ));
                    }
                }
            }
        }

        if (count($includedGroups) == 0) {
            $includedGroups = '';
        } else {
            $includedGroups = array_values(array_unique($includedGroups));
        }

        if (count($includedOffices) == 0) {
            $includedOffices = '';
        } else {
            $includedOffices = array_values(array_unique($includedOffices));
        }

        return new \vcms\module\LibAccessRestriction($includedGroups, $includedOffices);
    }
}
