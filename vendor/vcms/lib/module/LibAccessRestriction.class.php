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

namespace vcms\module;

class LibAccessRestriction{
	var $gruppen;
	var $aemter;

	function __construct($gruppen, $aemter){
		$this->gruppen = $gruppen;
		$this->aemter = $aemter;
	}

	function getGruppen(){
		return $this->gruppen;
	}

	function getAemter(){
		return $this->aemter;
	}

	function hasGruppenRestriction(){
		return is_array($this->gruppen) && count($this->gruppen) > 0;
	}

	function hasAemterRestriction(){
		return is_array($this->aemter) && count($this->aemter) > 0;
	}

	function isFulfilledBy($gruppe, $aemter){
		$gruppenOk = false;

		//should this restriction be restricted by group membership?
		if(is_array($this->gruppen) && count($this->gruppen) > 0){
			if(in_array($gruppe, $this->gruppen)){
				$gruppenOk = true;
			}
		} else {
			$gruppenOk = true;
		}

		$aemterOk = false;

		//should this restriction be restricted by function?
		if(is_array($this->aemter) && count($this->aemter) > 0){
			if(is_array($aemter)){
				foreach($aemter as $amt){
					if(in_array($amt, $this->aemter)){
						$aemterOk = true;
					}
				}
			}
		} else {
			$aemterOk = true;
		}

		return $gruppenOk && $aemterOk;
	}
}