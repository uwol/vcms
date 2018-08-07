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

use PDO;

class LibSecurityManager{
	var $possibleAemter = array(
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
			'vop', 'vvop', 'vopxx', 'vopxxx', 'vopxxxx');

	function getPossibleAemter(){
		return $this->possibleAemter;
	}

	function hasAccess($libElement, $libAuth){
		//public page?
		if(!$libElement->hasAccessRestriction()){
			return true;
		} else { //internal page?
			//not logged in?
			if(!$libAuth->isLoggedIn()){
				return false;
			}

			$accessRestriction = $libElement->getAccessRestriction();

			//enough rights?
			if($accessRestriction->isFulfilledBy($libAuth->getGruppe(), $libAuth->getAemter())){
				return true;
			} else {
				return false;
			}
		}
	}

	function generateAggregatedAccessRestriction($accessRestrictions){
		$includedGruppen = array();
		$modifyGruppen = true;
		$freshGruppen = true;

		$includedAemter = array();
		$modifyAemter = true;
		$freshAemter = true;

		//no foreach, as otherwise php4 does copy-by-value!
		for($i=0; $i<count($accessRestrictions); $i++){
			$accessRestriction = $accessRestrictions[$i];

			/*
			* aggregate groups
			*/
			if($modifyGruppen){
				//element without group restriction?
				if(!$accessRestriction->hasGruppenRestriction()){
					//remove group filder
					$includedGruppen = array();
					//protect group filter from modification
					$modifyGruppen = false;
					$freshGruppen = false;
				}
				//element with group restriction
				else{
					//first iteration
					if($freshGruppen){
						//add all groups
						$includedGruppen = $accessRestriction->getGruppen();
						$freshGruppen = false;
					} else {
						//remove all groups not contained in the restriction
						$includedGruppen = array_unique(array_merge(
							$includedGruppen, $accessRestriction->getGruppen()));
					}
				}
			}

			/*
			* aggregate functions
			*/
			if($modifyAemter){
				//element without function restriction?
				if(!$accessRestriction->hasAemterRestriction()){
					//remove function filter
					$includedAemter = array();
					//protect function filter from modification
					$modifyAemter = false;
					$freshAemter = false;
				}
				//element with function restriction
				else{
					//if first iteration
					if($freshAemter){
						//add all functions
						$includedAemter = $accessRestriction->getAemter();
						$freshAemter = false;
					} else {
						//remove all functions not contained in the restriction
						$includedAemter = array_unique(array_merge(
							$includedAemter, $accessRestriction->getAemter()));
					}
				}
			}
		}

		if(count($includedGruppen) == 0){
			$includedGruppen = '';
		} else {
			$includedGruppen = array_values(array_unique($includedGruppen));
		}

		if(count($includedAemter) == 0){
			$includedAemter = '';
		} else {
			$includedAemter = array_values(array_unique($includedAemter));
		}

		return new \vcms\module\LibAccessRestriction($includedGruppen, $includedAemter);
	}
}
