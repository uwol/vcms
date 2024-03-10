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

/*
* register autoloaders
*/
require_once(__DIR__ . '/autoload.php');
require_once(__DIR__ . '/../autoload.php');
require_once(__DIR__ . '/../phpass/autoload.php');


/*
* append parameter PHPSESSID to the URL by &amp; instead of & for XHTML compatibility
*/
ini_set('arg_separator.output', '&amp;');


/*
* set up session
*/
if(isset($_COOKIE[session_name()])){
	session_start();
}

if((isset($_REQUEST['logout']) && $_REQUEST['logout'] == 1) ||
		(isset($_SESSION) && isset($_SESSION['session_timeout_timestamp']) &&
		($_SESSION['session_timeout_timestamp'] == '' || $_SESSION['session_timeout_timestamp'] < time()))){
	$_SESSION = array();
	session_destroy();
	setcookie(session_name(), '', time() - 86400);
}

if(isset($_COOKIE[session_name()])){
	$_SESSION['session_timeout_timestamp'] = time() + (3 * 24 * 60 * 60);
}


/*
* instantiate libraries
*/
$libConfig = new LibConfig();

$libAssociation = new \vcms\LibAssociation();
$libCronjobs = new \vcms\LibCronjobs();
$libDb = new \vcms\LibDb();
$libEvent = new \vcms\LibEvent();
$libFilesystem = new \vcms\LibFilesystem(__DIR__ . '/../..');
$libForm = new \vcms\LibForm();
$libGallery = new \vcms\LibGallery();
$libGenericStorage = new \vcms\LibGenericStorage();
$libGlobal = new \vcms\LibGlobal();
$libHttp = new \vcms\LibHttp();
$libImage = new \vcms\LibImage();
$libMail = new \vcms\LibMail();
$libPerson = new \vcms\LibPerson();
$libModuleHandler = new \vcms\LibModuleHandler();
$libModuleParser = new \vcms\LibModuleParser();
$libRepositoryClient = new \vcms\LibRepositoryClient();
$libSecurityManager = new \vcms\LibSecurityManager();
$libString = new \vcms\LibString();
$libTime = new \vcms\LibTime();


/*
* init modules
*/
$libModuleHandler->initModules();


/*
* set timezone
*/
if(isset($libConfig->timezone) && $libConfig->timezone != ''){
	date_default_timezone_set($libConfig->timezone);
} else {
	date_default_timezone_set('Europe/Berlin');
}


/*
* set the current semester
*/
if(isset($_REQUEST['semester']) && $libTime->isValidSemesterString($_REQUEST['semester'])){
	$libGlobal->semester = $_REQUEST['semester'];
} else {
	$libGlobal->semester = $libTime->getSemesterName();
}


/*
* instantiate authentication context
*/
if(isset($_SESSION) && isset($_SESSION['libAuth'])){
	$libAuth = $_SESSION['libAuth'];
} else {
	$libAuth = new \vcms\LibAuth();
}
