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

class LibImage{
	var $galleryThumbWidth = 200;
	var $galleryThumbHeight = 150;

	var $galleryImageWidth = 800;
	var $galleryImageHeight = 600;

	var $personFotoWidth = 100;
	var $personFotoHeight = 133;

	var $startseiteFotoWidth = 400;
	var $startseiteFotoHeight = 300;

	var $semesterCoverWidth = 500;
	var $semesterCoverHeight = 500;
	
	var $GDlib_colorBits = 5;
	var $libTime;
	var $libGenericStorage;

	function __construct(LibTime $libTime, LibGenericStorage $libGenericStorage){
		global $libGenericStorage;

		$this->libTime = $libTime;
		$this->libGenericStorage = $libGenericStorage;

		if(!$libGenericStorage->attributeExists('base_core', 'imagemanipulator')){
			$libGenericStorage->saveValue('base_core', 'imagemanipulator', '1');
		}
	}

	//Checks--------------------------------------------------------------------

	function GDlib_isAvailable(){
		return function_exists('gd_info');
	}

	function GDlib_imageIsTooBig($width, $height){
		$memLimitMByte = (int) substr(ini_get('memory_limit'), 0, -1);
		$memRequiredByte = $width * $height * $this->GDlib_colorBits;

		if($memRequiredByte > ($memLimitMByte * 1000000)){
			return true;
		}

		return false;
	}

	function GDLib_maxMegaPixels(){
		$memLimitMByte = (int) substr(ini_get('memory_limit'), 0, -1);
		return $memLimitMByte / $this->GDlib_colorBits;
	}

	function imageManipulatorIsAvailable(){
		return $this->GDlibIsAvailable();
	}

	function imageRatioIsOk($oldWidth, $oldHeight, $newWidth, $newHeight){
		$ratioOld = $oldWidth / $oldHeight;
		$ratioNew = $newWidth / $newHeight;

		if($ratioNew - $ratioOld < 0.05){
			return true;
		}

		return false;
	}

	function determineImageManipulator(){
		$method = $this->libGenericStorage->loadValue('base_core', 'imagemanipulator');

		if($method == 1 || $method == 2){
			return $method;
		}

		return 1;
	}

	function checkDirectoryEscape($path){
		//parameter check
		return preg_match('/\.\./', $path);
	}

	//Resize--------------------------------------------------------------------

	function resizeImage($imagePath, $newWidth, $newHeight){
		//parameter check
		if($this->checkDirectoryEscape($imagePath)){
			return;
		}

		list($imageWidth, $imageHeight) = getimagesize($imagePath);

		if($this->imageRatioIsOk($imageWidth, $imageHeight, $newWidth, $newHeight)){
			switch($this->determineImageManipulator()){
				case 1: $this->resizeImage_GDlib($imagePath, $newWidth, $newHeight); break;
				case 2: $this->resizeImage_ImageMagick($imagePath, $newWidth, $newHeight); break;
			}
		} else {
			return -1;
		}
	}

	function resizeImage_GDlib($imagePath, $newWidth, $newHeight){
		global $libGlobal;

		//parameter check
		if($this->checkDirectoryEscape($imagePath)){
			return;
		}

		//$libGlobal->notificationTexts[] = 'Modifiziere Foto mit GDLib.';

		list($imageWidth, $imageHeight) = getimagesize($imagePath);

		// resample
		$newImage = imagecreatetruecolor($newWidth, $newHeight);
		$image = imagecreatefromjpeg($imagePath);
		imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $imageWidth, $imageHeight);

		// output
		imagejpeg($newImage, $imagePath, 80);
		ImageDestroy($newImage);
		ImageDestroy($image);
	}

	function resizeImage_ImageMagick($imagePath, $newWidth, $newHeight){
		global $libGlobal;

		//parameter check
		if($this->checkDirectoryEscape($imagePath)){
			return;
		}

		//$libGlobal->notificationTexts[] = 'Modifiziere Foto mit ImageMagick.';
		system('convert -geometry ' .escapeshellarg($newWidth). 'x' .escapeshellarg($newHeight). " -quality 75 '".escapeshellarg($imagePath)."' '".escapeshellarg($imagePath)."'");
	}


	//Rotation------------------------------------------------------------------

	function rotateImage($imagePath, $degree){
		//parameter check
		if($this->checkDirectoryEscape($imagePath)){
			return;
		}

		switch($this->determineImageManipulator()){
			case 1: $this->rotateImage_GDlib($imagePath, $degree); break;
			case 2: $this->rotateImage_ImageMagick($imagePath, $degree); break;
		}
	}

	function rotateImage_GDlib($imagePath, $degree){
		global $libGlobal;

		//parameter check
		if($this->checkDirectoryEscape($imagePath)){
			return;
		}

		//$libGlobal->notificationTexts[] = 'Rotiere Foto mit GDLib.';

		$image = imagecreatefromjpeg($imagePath);
		list($imageWidth, $imageHeight) = getimagesize($imagePath);

		if($degree == 90 || $degree == 270){
        	$newWidth = $imageHeight;
        	$newHeight = $imageWidth;
   		} else {
        	$newWidth = $imageWidth;
        	$newHeight = $imageHeight;
    	}

		$newImage=imagecreatetruecolor($newWidth, $newHeight);

		switch($degree){
       		case 90:
       		for ($x = 0; $x < ($imageWidth); $x++) {
            	for ($y = 0; $y < ($imageHeight); $y++) {
                    $color = imagecolorat($image, $x, $y);
                    imagesetpixel($newImage, $newWidth - $y - 1, $x, $color);
                }
            }
            break;
        	case 270:
       		for ($x = 0; $x < ($imageWidth); $x++) {
            	for ($y = 0; $y < ($imageHeight); $y++) {
                    $color = imagecolorat($image, $x, $y);
                    imagesetpixel($newImage, $y, $newHeight - $x - 1, $color);
                }
            }
            break;
        	case 180:
       		for ($x = 0; $x < ($imageWidth); $x++) {
            	for ($y = 0; $y < ($imageHeight); $y++) {
                    $color = imagecolorat($image, $x, $y);
                    imagesetpixel($newImage, $newWidth - $x - 1, $newHeight - $y - 1, $color);
                }
            }
            break;
        	default: $newImage = $image;
		}

		imagejpeg($newImage, $imagePath, 80);
		ImageDestroy($newImage);
		ImageDestroy($image);
	}

	function rotateImage_ImageMagick($imagePath, $degree){
		global $libGlobal;

		//parameter check
		if($this->checkDirectoryEscape($imagePath)){
			return;
		}

		//$libGlobal->notificationTexts[] = 'Rotiere Foto mit ImageMagick.';
		system('convert -rotate ' .escapeshellarg($degree). " '".escapeshellarg($imagePath)."' '".escapeshellarg($imagePath)."'");
	}

	//Image Upload------------------------------------------------------------------

	function saveImageByFilesArray($tmpFileVarName, $targetDirectory, $targetFilename, $maxWidth, $maxHeight, $copy = false){
		global $libGlobal;

		//parameter check
		if($tmpFileVarName == ''){
			return;
		}

		//no file uploaded?
		if(!isset($_FILES[$tmpFileVarName]) || !isset($_FILES[$tmpFileVarName]['tmp_name']) ||
				$_FILES[$tmpFileVarName]['tmp_name'] == ''){
			return;
		}

		$tmpFilename = $_FILES[$tmpFileVarName]['tmp_name'];
		$this->saveImage($tmpFilename, $targetDirectory, $targetFilename, $maxWidth, $maxHeight, $copy);
	}

	function saveImage($tmpFilename, $targetDirectory, $targetFilename, $maxWidth, $maxHeight, $copy = false){
		global $libGlobal;

		//parameter check
		if($tmpFilename == '' ||
				$targetDirectory == '' || $this->checkDirectoryEscape($targetDirectory) ||
				$targetFilename == '' || $this->checkDirectoryEscape($targetFilename)){
			return;
		}

		//no file uploaded?
		if($tmpFilename == '' || !is_file($tmpFilename)){
			return;
		}

		$imageInfoArray = getimagesize($tmpFilename);
		$imageType = $imageInfoArray[2];
		$width = $imageInfoArray[0];
		$height = $imageInfoArray[1];

		//check image type
		if($imageType != 2){ //liegt kein JPG vor?
			$libGlobal->errorTexts[] = 'Fehler: Das Bild ist kein Jpeg.';
			return;
		}

		//does a file with this name already exist?
		if(is_file($targetDirectory.'/'.$targetFilename)){
			$libGlobal->errorTexts[] = 'Fehler: Unter diesem Dateinamen existiert bereits ein Bild.';
			return;
		}

		//create dir
		if(!is_dir($targetDirectory)){
			mkdir($targetDirectory);
		}

		//copy or move image to destination
		if($copy){
			copy($tmpFilename, $targetDirectory.'/'.$targetFilename);
		} else {
			move_uploaded_file($tmpFilename, $targetDirectory.'/'.$targetFilename);
		}

		//adjust width and height
		$widthRatio = $width / $maxWidth;
		$heightRatio = $height / $maxHeight;

		$ratio = $width / $height;

		//landscape
		if($widthRatio > $heightRatio){
			$newWidth = $maxWidth;
			$newHeight = round($newWidth / $ratio);
		}
		//portrait
		else {
			$newHeight = $maxHeight;
			$newWidth = round($newHeight * $ratio);
		}

		$this->resizeImage($targetDirectory.'/'.$targetFilename, $newWidth, $newHeight);

		$libGlobal->notificationTexts[] = 'Das Bild wurde gespeichert.';
	}

	function deleteImage($directory, $filename){
		global $libGlobal;

		//parameter check
		if($directory == '' || $this->checkDirectoryEscape($directory) ||
				$filename == '' || $this->checkDirectoryEscape($filename)){
			return;
		}

		if(is_file($directory.'/'.$filename)){
			if(unlink($directory.'/'.$filename)){
				$libGlobal->notificationTexts[] = 'Das Bild wurde gelöscht.';
			}
		}
	}

	//specific functions for image types-----------------------

	function saveSemesterCoverByFilesArray($semesterString, $tmpFileVarName){
		//parameter check
		if(!$this->libTime->isValidSemesterString($semesterString)){
			return;
		}

		$semesterCoverFilename = strtolower($semesterString).'.jpg';

		//delete old image
		$this->deleteSemesterCover($semesterString);

		$this->saveImageByFilesArray($tmpFileVarName, 'custom/semestercover', $semesterCoverFilename, $this->semesterCoverWidth, $this->semesterCoverHeight);
	}

	function deleteSemesterCover($semesterString){
		//parameter check
		if(!$this->libTime->isValidSemesterString($semesterString)){
			return;
		}

		$semesterCoverFilename = strtolower($semesterString).'.jpg';
		$this->deleteImage('custom/semestercover', $semesterCoverFilename);
	}

	function savePersonFotoByFilesArray($personId, $tmpFileVarName){
		//parameter check
		if(!is_numeric($personId) || !preg_match('/^[0-9]+$/', $personId)){
			return;
		}

		$personFotoFilename = $personId.'.jpg';

		//delete old image
		$this->deletePersonFoto($personId);

		$this->saveImageByFilesArray($tmpFileVarName, 'custom/intranet/mitgliederfotos', $personFotoFilename, $this->personFotoWidth, $this->personFotoHeight);
	}

	function deletePersonFoto($personId){
		//parameter check
		if(!is_numeric($personId) || !preg_match('/^[0-9]+$/', $personId)){
			return;
		}

		$personFotoFilename = $personId.'.jpg';
		$this->deleteImage('custom/intranet/mitgliederfotos', $personFotoFilename);
	}

	function saveStartseitenBildByFilesArray($nachrichtId, $tmpFileVarName){
		//parameter check
		if(!is_numeric($nachrichtId) || !preg_match('/^[0-9]+$/', $nachrichtId)){
			return;
		}

		$nachrichtFotoFilename = $nachrichtId.'.jpg';

		//delete old image
		$this->deleteStartseitenBild($nachrichtId);

		$this->saveImageByFilesArray($tmpFileVarName, 'modules/mod_internet_home/custom/bilder', $nachrichtFotoFilename, $this->startseiteFotoWidth, $this->startseiteFotoHeight);
	}

	function deleteStartseitenBild($nachrichtId){
		//parameter check
		if(!is_numeric($nachrichtId) || !preg_match('/^[0-9]+$/', $nachrichtId)){
			return;
		}

		$nachrichtFotoFilename = $nachrichtId.'.jpg';
		$this->deleteImage('modules/mod_internet_home/custom/bilder', $nachrichtFotoFilename);
	}


	function saveVeranstaltungsFotoByFilesArray($veranstaltungId, $tmpFileVarName){
		//parameter check
		if(!is_numeric($veranstaltungId) || !preg_match('/^[0-9]+$/', $veranstaltungId) ||
				$tmpFileVarName == '' || !isset($_FILES[$tmpFileVarName]) ||
				substr($_FILES[$tmpFileVarName]['name'], 0, 1) == '.'){
			return;
		}

		$fotoFileName = preg_replace('/[^A-Za-z0-9\._]/', '', $_FILES[$tmpFileVarName]['name']);
		$fotoThumbFileName = preg_replace('/[^A-Za-z0-9\._]/', '', 'thumb_'.$_FILES[$tmpFileVarName]['name']);

		$this->saveImageByFilesArray($tmpFileVarName, 'custom/veranstaltungsfotos/'.$veranstaltungId, $fotoFileName, $this->galleryImageWidth, $this->galleryImageHeight, true);
		$this->saveImageByFilesArray($tmpFileVarName, 'custom/veranstaltungsfotos/'.$veranstaltungId.'/thumbs', $fotoThumbFileName, $this->galleryThumbWidth, $this->galleryThumbHeight);
	}

	function saveVeranstaltungsFotoByAjax($veranstaltungId, $targetFilename, $tmpFilename){
		//parameter check
		if(!is_numeric($veranstaltungId) || !preg_match('/^[0-9]+$/', $veranstaltungId) ||
				$tmpFilename == '' ||
				substr($targetFilename, 0, 1) == '.'){
			return;
		}

		$fotoFileName = preg_replace('/[^A-Za-z0-9\._]/', '', $targetFilename);
		$fotoThumbFileName = preg_replace('/[^A-Za-z0-9\._]/', '', 'thumb_'.$targetFilename);

		$this->saveImage($tmpFilename, 'custom/veranstaltungsfotos/'.$veranstaltungId, $fotoFileName, $this->galleryImageWidth, $this->galleryImageHeight, true);
		$this->saveImage($tmpFilename, 'custom/veranstaltungsfotos/'.$veranstaltungId.'/thumbs', $fotoThumbFileName, $this->galleryThumbWidth, $this->galleryThumbHeight, true);
	}


	function deleteVeranstaltungsFoto($veranstaltungId, $fotoFileName){
		//parameter check
		if(!is_numeric($veranstaltungId) || !preg_match('/^[0-9]+$/', $veranstaltungId) ||
				preg_match('/[^A-Za-z0-9\._-]/', $fotoFileName)){
			return;
		}

		$fotoThumbFileName = 'thumb_'.$fotoFileName;

		$this->deleteImage('custom/veranstaltungsfotos/'.$veranstaltungId, $fotoFileName);
		$this->deleteImage('custom/veranstaltungsfotos/'.$veranstaltungId.'/thumbs', $fotoThumbFileName);

		if(count(@scandir('custom/veranstaltungsfotos/'.$veranstaltungId.'/thumbs')) == 2){
			rmdir('custom/veranstaltungsfotos/'.$veranstaltungId.'/thumbs');
		}

		if(count(@scandir('custom/veranstaltungsfotos/'.$veranstaltungId)) == 2){
			rmdir('custom/veranstaltungsfotos/'.$veranstaltungId);
		}
	}
}
?>