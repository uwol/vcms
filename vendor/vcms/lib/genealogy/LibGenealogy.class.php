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

namespace vcms\genealogy;

class LibGenealogy{
	var $retstr;

	function __construct($root, $tiefe, $mitgliedid){
		$retstr = '';
		$genealogyRoot = new LibGenealogyElement($root, $mitgliedid);
		$retstr .= $genealogyRoot->getString($tiefe);
		$leibsoehne = $genealogyRoot->searchLeibSoehne();

		for($i=0; $i<count($leibsoehne); $i++){
			$genealogy = new LibGenealogy($leibsoehne[$i], $tiefe + 1, $mitgliedid);
			$retstr .= $genealogy->getString();
		}

		$this->retstr = $retstr;
	}

	function getString(){
		return $this->retstr;
	}
}