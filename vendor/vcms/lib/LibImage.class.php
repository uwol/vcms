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

class LibImage
{
    public $galleryImageWidth = 1280;
    public $galleryImageHeight = 960;

    public $personPhotoWidth = 480;
    public $personPhotoHeight = 640;

    public $homeImageWidth = 1280;
    public $homeImageHeight = 960;

    public $semesterCoverWidth = 1440;
    public $semesterCoverHeight = 1080;

    public $GDlib_colorBits = 5;

    //Checks--------------------------------------------------------------------

    public function GDlib_isAvailable()
    {
        return function_exists('gd_info');
    }

    public function GDlib_imageIsTooBig($width, $height)
    {
        $memLimitMByte = (int) substr(ini_get('memory_limit'), 0, -1);
        $memRequiredByte = $width * $height * $this->GDlib_colorBits;

        if ($memRequiredByte > ($memLimitMByte * 1000000)) {
            return true;
        }

        return false;
    }

    public function GDLib_maxMegaPixels()
    {
        $memLimitMByte = (int) substr(ini_get('memory_limit'), 0, -1);
        return $memLimitMByte / $this->GDlib_colorBits;
    }

    public function imageRatioIsOk($oldWidth, $oldHeight, $newWidth, $newHeight)
    {
        $ratioOld = $oldWidth / $oldHeight;
        $ratioNew = $newWidth / $newHeight;

        if ($ratioNew - $ratioOld < 0.05) {
            return true;
        }

        return false;
    }

    public function determineImageLib()
    {
        global $libGenericStorage;

        $method = $libGenericStorage->loadValue('base_core', 'image_lib');

        if ($method == 1 || $method == 2) {
            return $method;
        }

        return 1;
    }

    public function checkDirectoryEscape($path)
    {
        //parameter check
        return preg_match('/\.\./', $path);
    }

    //Resize--------------------------------------------------------------------

    public function resizeImage($imagePath, $newWidth, $newHeight)
    {
        //parameter check
        if ($this->checkDirectoryEscape($imagePath)) {
            return;
        }

        $imageInfoArray = getimagesize($imagePath);

        if (!is_array($imageInfoArray)) {
            return;
        }

        $imageWidth = $imageInfoArray[0];
        $imageHeight = $imageInfoArray[1];

        if ($this->imageRatioIsOk($imageWidth, $imageHeight, $newWidth, $newHeight)) {
            switch ($this->determineImageLib()) {
                case 1:
                    $this->resizeImage_GDlib($imagePath, $newWidth, $newHeight);
                    break;
                case 2:
                    $this->resizeImage_ImageMagick($imagePath, $newWidth, $newHeight);
                    break;
            }
        } else {
            return -1;
        }
    }

    public function resizeImage_GDlib($imagePath, $newWidth, $newHeight)
    {
        global $libGlobal;

        //parameter check
        if ($this->checkDirectoryEscape($imagePath)) {
            return;
        }

        //$libGlobal->notificationTexts[] = 'Modifying photo with GDLib.';

        $imageInfoArray = getimagesize($imagePath);

        if (!is_array($imageInfoArray)) {
            return;
        }

        $imageWidth = $imageInfoArray[0];
        $imageHeight = $imageInfoArray[1];

        // resample
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        $image = imagecreatefromjpeg($imagePath);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $imageWidth, $imageHeight);

        // output
        imagejpeg($newImage, $imagePath, 80);
    }

    public function resizeImage_ImageMagick($imagePath, $newWidth, $newHeight)
    {
        global $libGlobal;

        //parameter check
        if ($this->checkDirectoryEscape($imagePath)) {
            return;
        }

        //$libGlobal->notificationTexts[] = 'Modifying photo with ImageMagick.';
        system('convert -strip -geometry ' .escapeshellarg($newWidth). 'x' .escapeshellarg($newHeight). " -quality 75 '".escapeshellarg($imagePath)."' '".escapeshellarg($imagePath)."'");
    }


    //Rotation------------------------------------------------------------------

    public function rotateImage($imagePath, $degree)
    {
        //parameter check
        if ($this->checkDirectoryEscape($imagePath)) {
            return;
        }

        switch ($this->determineImageLib()) {
            case 1: $this->rotateImage_GDlib($imagePath, $degree); break;
            case 2: $this->rotateImage_ImageMagick($imagePath, $degree); break;
        }
    }

    public function rotateImage_GDlib($imagePath, $degree)
    {
        global $libGlobal;

        //parameter check
        if ($this->checkDirectoryEscape($imagePath)) {
            return;
        }

        //$libGlobal->notificationTexts[] = 'Rotating photo with GDLib.';

        $imageInfoArray = getimagesize($imagePath);

        if (!is_array($imageInfoArray)) {
            return;
        }

        $image = imagecreatefromjpeg($imagePath);
        $imageWidth = $imageInfoArray[0];
        $imageHeight = $imageInfoArray[1];

        if ($degree == 90 || $degree == 270) {
            $newWidth = $imageHeight;
            $newHeight = $imageWidth;
        } else {
            $newWidth = $imageWidth;
            $newHeight = $imageHeight;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        switch ($degree) {
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
            default:
                $newImage = $image;
        }

        imagejpeg($newImage, $imagePath, 80);
    }

    public function rotateImage_ImageMagick($imagePath, $degree)
    {
        global $libGlobal;

        //parameter check
        if ($this->checkDirectoryEscape($imagePath)) {
            return;
        }

        //$libGlobal->notificationTexts[] = 'Rotating photo with ImageMagick.';
        system('convert -strip -rotate ' .escapeshellarg($degree). " '".escapeshellarg($imagePath)."' '".escapeshellarg($imagePath)."'");
    }

    //Image Upload------------------------------------------------------------------

    public function saveImageByFilesArray($tmpFileVarName, $targetDirectory, $targetFilename, $maxWidth, $maxHeight, $copy = false)
    {
        global $libGlobal;

        //parameter check
        if ($tmpFileVarName == '') {
            return;
        }

        //no file uploaded?
        if (!isset($_FILES[$tmpFileVarName]) || !isset($_FILES[$tmpFileVarName]['tmp_name']) ||
                $_FILES[$tmpFileVarName]['tmp_name'] == '') {
            return;
        }

        $tmpFilename = $_FILES[$tmpFileVarName]['tmp_name'];
        $this->saveImage($tmpFilename, $targetDirectory, $targetFilename, $maxWidth, $maxHeight, $copy);
    }

    public function saveImage($tmpFilename, $targetDirectory, $targetFilename, $maxWidth, $maxHeight, $copy = false)
    {
        global $libGlobal;

        //parameter check
        if ($tmpFilename == '' ||
                $targetDirectory == '' || $this->checkDirectoryEscape($targetDirectory) ||
                $targetFilename == '' || $this->checkDirectoryEscape($targetFilename)) {
            return;
        }

        //no file uploaded?
        if ($tmpFilename == '' || !is_file($tmpFilename)) {
            return;
        }

        $imageInfoArray = getimagesize($tmpFilename);

        //check for a readable image
        if (!is_array($imageInfoArray)) {
            $libGlobal->errorTexts[] = 'Das Bild konnte nicht gelesen werden.';
            return;
        }

        $imageType = $imageInfoArray[2];
        $width = $imageInfoArray[0];
        $height = $imageInfoArray[1];

        //check image type
        if ($imageType != 2) { // No JPEG present?
            $libGlobal->errorTexts[] = 'Das Bild ist kein Jpeg.';
            return;
        }

        //does a file with this name already exist?
        if (is_file($targetDirectory. '/' .$targetFilename)) {
            $libGlobal->errorTexts[] = 'Unter diesem Dateinamen existiert bereits ein Bild.';
            return;
        }

        //create dir
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0777, true);
        }

        //copy or move image to destination
        if ($copy) {
            copy($tmpFilename, $targetDirectory. '/' .$targetFilename);
        } else {
            move_uploaded_file($tmpFilename, $targetDirectory. '/' .$targetFilename);
        }

        //adjust width and height
        $widthRatio = $width / $maxWidth;
        $heightRatio = $height / $maxHeight;

        $ratio = $width / $height;

        //landscape
        if ($widthRatio > $heightRatio) {
            $newWidth = $maxWidth;
            $newHeight = round($newWidth / $ratio);
        }
        //portrait
        else {
            $newHeight = $maxHeight;
            $newWidth = round($newHeight * $ratio);
        }

        $this->resizeImage($targetDirectory. '/' .$targetFilename, $newWidth, $newHeight);

        $libGlobal->notificationTexts[] = 'Das Bild wurde gespeichert.';
    }

    public function deleteImage($directory, $filename)
    {
        global $libGlobal;

        //parameter check
        if ($directory == '' || $this->checkDirectoryEscape($directory) ||
                $filename == '' || $this->checkDirectoryEscape($filename)) {
            return;
        }

        if (is_file($directory. '/' .$filename)) {
            if (unlink($directory. '/' .$filename)) {
                $libGlobal->notificationTexts[] = 'Das Bild wurde gelöscht.';
            }
        }
    }

    //specific functions for image types-----------------------

    public function saveSemesterCoverByFilesArray($semesterString, $tmpFileVarName)
    {
        global $libTime;

        //parameter check
        if (!$libTime->isValidSemesterString($semesterString)) {
            return;
        }

        $semesterCoverFilename = strtolower($semesterString). '.jpg';

        //delete old image
        $this->deleteSemesterCover($semesterString);

        $this->saveImageByFilesArray($tmpFileVarName, 'custom/semestercover', $semesterCoverFilename, $this->semesterCoverWidth, $this->semesterCoverHeight);
    }

    public function deleteSemesterCover($semesterString)
    {
        global $libTime;

        //parameter check
        if (!$libTime->isValidSemesterString($semesterString)) {
            return;
        }

        $semesterCoverFilename = strtolower($semesterString). '.jpg';
        $this->deleteImage('custom/semestercover', $semesterCoverFilename);
    }

    public function savePersonPhotoByFilesArray($personId, $tmpFileVarName)
    {
        //parameter check
        if (!is_numeric($personId) || !preg_match('/^[0-9]+$/', $personId)) {
            return;
        }

        $personPhotoFilename = $personId. '.jpg';

        //delete old image
        $this->deletePersonPhoto($personId);

        $this->saveImageByFilesArray($tmpFileVarName, 'custom/intranet/mitgliederfotos', $personPhotoFilename, $this->personPhotoWidth, $this->personPhotoHeight);
    }

    public function deletePersonPhoto($personId)
    {
        //parameter check
        if (!is_numeric($personId) || !preg_match('/^[0-9]+$/', $personId)) {
            return;
        }

        $personPhotoFilename = $personId.'.jpg';
        $this->deleteImage('custom/intranet/mitgliederfotos', $personPhotoFilename);
    }

    public function saveHomeImageByFilesArray($announcementId, $tmpFileVarName)
    {
        //parameter check
        if (!is_numeric($announcementId) || !preg_match('/^[0-9]+$/', $announcementId)) {
            return;
        }

        $announcementPhotoFilename = $announcementId. '.jpg';

        //delete old image
        $this->deleteHomeImage($announcementId);

        $this->saveImageByFilesArray($tmpFileVarName, 'modules/mod_internet_home/custom/img', $announcementPhotoFilename, $this->homeImageWidth, $this->homeImageHeight);
    }

    public function deleteHomeImage($announcementId)
    {
        //parameter check
        if (!is_numeric($announcementId) || !preg_match('/^[0-9]+$/', $announcementId)) {
            return;
        }

        $announcementPhotoFilename = $announcementId.'.jpg';
        $this->deleteImage('modules/mod_internet_home/custom/img', $announcementPhotoFilename);
    }


    public function saveEventPhotoByFilesArray($eventId, $tmpFileVarName)
    {
        //parameter check
        if (!is_numeric($eventId) || !preg_match('/^[0-9]+$/', $eventId) ||
                $tmpFileVarName == '' || !isset($_FILES[$tmpFileVarName]['name']) ||
                substr((string) $_FILES[$tmpFileVarName]['name'], 0, 1) == '.') {
            return;
        }

        $photoFileName = preg_replace('/[^A-Za-z0-9\._]/', '', (string) $_FILES[$tmpFileVarName]['name']);

        $this->saveImageByFilesArray($tmpFileVarName, 'custom/veranstaltungsfotos/' .$eventId, $photoFileName, $this->galleryImageWidth, $this->galleryImageHeight, true);
    }

    public function saveEventPhotoByAjax($eventId, $targetFilename, $tmpFilename)
    {
        //parameter check
        if (!is_numeric($eventId) || !preg_match('/^[0-9]+$/', $eventId) ||
                $tmpFilename == '' ||
                substr((string) $targetFilename, 0, 1) == '.') {
            return;
        }

        $photoFileName = preg_replace('/[^A-Za-z0-9\._]/', '', (string) $targetFilename);

        $this->saveImage($tmpFilename, 'custom/veranstaltungsfotos/' .$eventId, $photoFileName, $this->galleryImageWidth, $this->galleryImageHeight, true);
    }


    public function deleteEventPhoto($eventId, $photoFileName)
    {
        //parameter check
        if (!is_numeric($eventId) || !preg_match('/^[0-9]+$/', $eventId) ||
                preg_match('/[^A-Za-z0-9\._-]/', $photoFileName)) {
            return;
        }

        $this->deleteImage('custom/veranstaltungsfotos/' .$eventId, $photoFileName);

        $photos = array_diff(scandir('custom/veranstaltungsfotos/' .$eventId), ['.', '..']);

        if (empty($photos)) {
            rmdir('custom/veranstaltungsfotos/' .$eventId);
        }
    }
}
