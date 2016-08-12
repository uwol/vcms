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

class LibGallery{

	/*
	* external, internal, pooled, main
	*/
	var $validAccessStrings = array('E', 'I', 'P', 'M');

	function hasPictures($eventid, $level){
		$numberOfPictures = $this->getNumberOfPictures($eventid, $level);

		if($numberOfPictures > 0){
			return true;
		}

		return false;
	}

	function getMainPictureId($eventid){
		return $this->getFirstVisiblePictureId($eventid, 0);
	}

	function getFirstVisiblePictureId($eventid, $level){
		$pictures = $this->getPictures($eventid, $level);

		$firstPictureId = -1;
		$firstMainPictureId = -1;

		foreach($pictures as $key => $picture){
			if($firstPictureId == -1){
				$firstPictureId = $key;
			}

			$accessString = $this->parseAccessString($picture);

			if($firstMainPictureId == -1 && $accessString == 'M'){
				$firstMainPictureId = $key;
				break;
			}
		}

		if($firstMainPictureId != -1){
			return $firstMainPictureId;
		}

		return $firstPictureId;
	}

	function getPictures($eventid, $level){
		if($eventid != '' && !is_numeric($eventid)){
			exit();
		}

		$path = 'custom/veranstaltungsfotos/' .$eventid;

		//escape prevention
		if(preg_match("/\.\./", $path)){
			exit();
		}

	    $pictures = array();

		if(is_dir($path)){
			$files = array_diff(scandir($path), array('..', '.', 'thumbs'));

			foreach ($files as $file){
				$extension = pathinfo($file, PATHINFO_EXTENSION);

				if($extension == 'jpg' || $extension == 'jpeg'){
					$pictures[] = $file;
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

	function getNumberOfPictures($eventid, $level){
		return count($this->getPictures($eventid, $level));
	}

	function parseAccessString($file){
		$filename = pathinfo($file, PATHINFO_FILENAME);
		$accessSuffix = substr($filename, -2);

		foreach($this->validAccessStrings as $validAccessString){
			$validAccessSuffix = '-' .$validAccessString;

			if($accessSuffix == $validAccessSuffix){
				return $validAccessString;
			}
		}
	}

	function getPublicityLevel($file){
		$accessString = $this->parseAccessString($file);

		if($accessString == 'E'){
			return 0;
		} elseif($accessString == 'I'){
			return 1;
		} elseif($accessString == 'P'){
			return 2;
		} elseif($accessString == 'M'){
			return 0;
		} else {
			return 2;
		}
	}

	function hasPublicityLevel($file){
		$accessString = $this->parseAccessString($file);
		$result = in_array($accessString, $this->validAccessStrings);
		return $result;
	}

	function getPublicityFilename($file, $accessString){
		$filename = pathinfo($file, PATHINFO_FILENAME);
		$extension = pathinfo($file, PATHINFO_EXTENSION);
		$hasPublicityLevel = $this->hasPublicityLevel($file);

		if($hasPublicityLevel){
			$filename = substr($filename, 0, -2);
		}

		$result = $filename. '-' .$accessString;

		if($extension != ''){
			$result .= '.' .$extension;
		}

		return $result;
	}

	function hasFotowartPrivilege($aemterArrayOfUser){
		$priviliegedAemter = array('fotowart', 'internetwart', 'senior', 'consenior',
			'fuchsmajor', 'fuchsmajor2', 'scriptor', 'quaestor', 'jubelsenior');
		$privilegedAemterOfUser = array_intersect($priviliegedAemter, $aemterArrayOfUser);
		$numberOfPrivilegedAemterOfUser = count($privilegedAemterOfUser);

		return $numberOfPrivilegedAemterOfUser > 0;
	}

	function setPublicityLevel($eventId, $pictureId, $accessString){
		global $libGlobal;

		if($accessString == 'M'){
			$this->resetPublicityLevelMain($eventId);
		}

		$pictures = $this->getPictures($eventId, 2);
		$filename = $pictures[$pictureId];
		$publicityFilename = $this->getPublicityFilename($filename, $accessString);

		$notificationText = '';

		switch($accessString){
			case 'E':
				$notificationText = 'Gebe Bild ' .($pictureId + 1). ' für das Internet frei.';
				break;
			case 'I':
				$notificationText = 'Gebe Bild ' .($pictureId + 1). ' für das Intranet frei.';
				break;
			case 'P':
				$notificationText = 'Lege Bild in ' .($pictureId + 1). ' den Pool zurück.';
				break;
			case 'M':
				$notificationText = 'Gebe Bild ' .($pictureId + 1). ' als Hauptbild für das Internet frei.';
				break;
		}

		$libGlobal->notificationTexts[] = $notificationText;

		rename('custom/veranstaltungsfotos/' .$eventId. '/' .$filename, 'custom/veranstaltungsfotos/' .$eventId. '/' .$publicityFilename);
	}

	function setPublicityLevels($eventId, $accessString){
		$pictures = $this->getPictures($eventId, 2);

		foreach($pictures as $key => $value){
			$this->setPublicityLevel($eventId, $key, $accessString);
		}
	}

	function resetPublicityLevelMain($eventId){
		$pictures = $this->getPictures($eventId, 0);

		foreach($pictures as $key => $file){
			$accessString = $this->parseAccessString($file);

			if($accessString == 'M'){
				$this->setPublicityLevel($eventId, $key, 'E');
			}
		}
	}
}