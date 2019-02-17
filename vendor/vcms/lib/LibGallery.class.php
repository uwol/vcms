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

	function getMainPictureId($eventId){
		return $this->getFirstVisiblePictureId($eventId, 0);
	}

	function getFirstVisiblePictureId($eventId, $level){
		$pictures = $this->getPictures($eventId, $level);

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

	function getPictures($eventId, $level){
		if($eventId != '' && !is_numeric($eventId)){
			exit();
		}

		$path = 'custom/veranstaltungsfotos/' .$eventId;

		//escape prevention
		if(preg_match("/\.\./", $path)){
			exit();
		}

		$pictures = array();

		if(is_dir($path)){
			$files = array_diff(scandir($path), array('.', '..', 'thumbs'));

			foreach($files as $file){
				$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				$basename = pathinfo($file, PATHINFO_BASENAME);

				if(substr($basename, 0, 1) != '.'){
					if($extension == 'jpg' || $extension == 'jpeg'){
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

	function getNumberOfPictures($eventId, $level){
		return count($this->getPictures($eventId, $level));
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
		$priviliegedAemter = array('fotowart', 'internetwart', 'datenpflegewart', 'senior', 'consenior',
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
		$currentAccessString = $this->parseAccessString($filename);

		if($currentAccessString != $accessString){
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

			$publicityFilename = $this->getPublicityFilename($filename, $accessString);
			rename('custom/veranstaltungsfotos/' .$eventId. '/' .$filename, 'custom/veranstaltungsfotos/' .$eventId. '/' .$publicityFilename);
		}
	}

	function setPublicityLevels($eventId, $accessString){
		$pictures = $this->getPictures($eventId, 2);

		foreach($pictures as $key => $value){
			$this->setPublicityLevel($eventId, $key, $accessString);
		}
	}

	function setPublicityLevelsUntilSemester($semesterString, $accessString){
		global $libTime, $libDb, $libGlobal;

		$zeitraum = $libTime->getZeitraum($semesterString);

		$stmt = $libDb->prepare('SELECT id FROM base_veranstaltung WHERE DATEDIFF(datum, :semester_ende) <= 0 ORDER BY datum DESC');
		$stmt->bindValue(':semester_ende', $zeitraum[1]);
		$stmt->execute();

		$libGlobal->notificationTexts[] = 'Gebe Bilder aller Veranstaltungen bis ' .$semesterString. ' für das Intranet frei.';

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$this->setPublicityLevels($row['id'], $accessString);
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
