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

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();


if($libAuth->isLoggedin() &&
		isset($_REQUEST['veranstaltungId']) && is_numeric($_REQUEST['veranstaltungId']) &&
		preg_match("/^[0-9]+$/", $_REQUEST['veranstaltungId']) &&
		isset($_REQUEST['qqfile'])){

	$allowedExtensions = array('jpg', 'jpeg');
	$result = handleUpload($allowedExtensions);

	// to pass data through iframe you will need to encode all html tags
	echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
}



/*
 * Returns array('success'=>true) or array('error'=>'error message')
 */
function handleUpload($allowedExtensions){
	global $libGlobal, $libImage;

	//extract filename and extension
	$pathinfo = pathinfo($_REQUEST['qqfile']);
	$filename = $pathinfo['filename'];
	$ext = $pathinfo['extension'];

	//check extension
	if(is_array($allowedExtensions) && !in_array(strtolower($ext), $allowedExtensions)){
		$allowedExtensionsString = implode(', ', $allowedExtensions);
		return array('error' => 'Die Dateiendung ist nicht korrekt. Erlaubt sind '. $allowedExtensionsString . '.');
	}

	//save stream
	$tempFilename = tempnam(sys_get_temp_dir(), '');

	$tempHandle = fopen($tempFilename, "w");
	$inputHandle = fopen("php://input", "r");
	stream_copy_to_stream($inputHandle, $tempHandle);

	fclose($inputHandle);
	fclose($tempHandle);

	//save image
	$libImage->saveVeranstaltungsFotoByAjax($_REQUEST['veranstaltungId'], $filename . '.' . $ext, $tempFilename);

	unlink($tempFilename);

	if(count($libGlobal->errorTexts) > 0){
		$errorText = implode(' ', $libGlobal->errorTexts);
		return array('error' => $errorText);
	} else {
		return array('success' => true);
	}
}
?>
