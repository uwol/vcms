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

namespace vcms;

use PDO;

class LibGallery{

	function hasPictures($eventid, $level){
		if(count($this->getPictures($eventid, $level)) > 0){
			return true;
		}

		return false;
	}

	function getFirstVisiblePictureId($eventid, $level){
		if(count($this->getPictures($eventid, $level)) > 0){
			$pictures = $this->getPictures($eventid, $level);
			$keys = array_keys($pictures);
			return $keys[0];
		} else {
			return -1;
		}
	}

	function getPictures($eventid, $level){
		if($eventid != "" && !is_numeric($eventid)){
			exit();
		}

		$path = 'custom/veranstaltungsfotos/' .$eventid;

		//escape prevention
		if(preg_match("/\.\./", $path)){
			exit();
		}

	    $pictures = array();

		if(is_dir($path)){
			$files = array_diff(scandir($path), array('..', '.'));

			foreach ($files as $file){
				if($file != "thumbs"){
					//image?
					$fileParts = explode(".", $file);

					if(strtolower($fileParts[count($fileParts)-1]) == "jpg"){
						$pictures[] = $file;
					}
				}
	    	}
	    }

		sort($pictures);
	    reset($pictures);

	    $visiblePictures = array();
	    $i = 0;

	   	foreach($pictures as $picture){
	    	$levelOfPicture = $this->getPublicityLevel($picture);

	    	if($levelOfPicture <= $level){
	    		$visiblePictures[$i] = $picture;
	    	}

	    	$i++;
	   	}

		return $visiblePictures;
	}

	/**
	* 0 - public
	* 1 - intranet
	* 2 - pool
	*/
	function getPublicityLevel($filename){
		$array = explode(".", $filename);
		$stringbeforeextension = $array[count($array)-2];
		$accessString = substr($stringbeforeextension, -2);

		if($accessString == "-E"){
			return 0;
		} elseif($accessString == "-I"){
			return 1;
		} elseif($accessString == "-P"){
			return 2;
		} else {
			return 2;
		}
	}

	function hasPublicityLevel($filename){
		$array = explode(".", $filename);
		$stringbeforeextension = $array[count($array)-2];
		$accessString = substr($stringbeforeextension, -2);

		if($accessString == "-E" || $accessString == "-I" || $accessString == "-P"){
			return true;
		}

		return false;
	}

	function changeVisibility($filename, $string){
		$array = explode(".", $filename);
		$criticalPart = $array[count($array)-2];

		//is there already a visibility suffix?
		if(substr($criticalPart, -2, 1) == "-"){
			//remove suffix
			$criticalPart = substr($criticalPart, 0, -2);
		}

		$criticalPart = $criticalPart ."-".$string;

		$array[count($array)-2] = $criticalPart;
		return implode(".", $array);
	}

	function hasFotowartPrivilege($aemterArrayOfUser){
		$priviliegedAemter = array('fotowart', 'internetwart', 'senior', 'consenior',
			'fuchsmajor', 'fuchsmajor2', 'scriptor', 'quaestor', 'jubelsenior');
		$privilegedAemterOfUser = array_intersect($priviliegedAemter, $aemterArrayOfUser);
		$numberOfPrivilegedAemterOfUser = count($privilegedAemterOfUser);

		return $numberOfPrivilegedAemterOfUser > 0;
	}
}