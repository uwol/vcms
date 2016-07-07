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

/*
* register autoloaders
*/
require_once(__DIR__ . '/autoload.php');
require_once(__DIR__ . '/../phpmailer/PHPMailerAutoload.php');
require_once(__DIR__ . '/../phpass/autoload.php');
require_once(__DIR__ . '/../httpclient/autoload.php');
require_once(__DIR__ . '/../pear/autoload.php');

/*
* deprecated
*/
class LibAuth extends vcms\LibAuth{}
class LibInclude extends vcms\module\LibInclude{}
class LibPage extends vcms\module\LibPage{}
class LibAccessRestriction extends vcms\module\LibAccessRestriction{}
class LibMinDependency{}

class LibMenuEntry extends vcms\menu\LibMenuEntry{}
class LibMenuEntryLogin extends vcms\menu\LibMenuEntryLogin{}
class LibMenuEntryExternalLink extends vcms\menu\LibMenuEntryExternalLink{}
class LibMenuFolder extends vcms\menu\LibMenuFolder{}

/*
* append parameter PHPSESSID to the URL by &amp; instead of & for XHTML compatibility
*/
ini_set('arg_separator.output', '&amp;');


/*
* set up session
*/
@session_start();

if((isset($_REQUEST['session_destroy']) && $_REQUEST['session_destroy'] == 1) ||
		(isset($_SESSION['session_timeout_timestamp']) &&
		($_SESSION['session_timeout_timestamp'] == "" || $_SESSION['session_timeout_timestamp'] < time()))){
	@session_destroy();
	@session_start();
}

$_SESSION['session_timeout_timestamp'] = time() + 14400;


/*
* instantiate libraries
*/
$libConfig = new LibConfig();
$libGlobal = new vcms\LibGlobal();
$libString = new vcms\LibString();
$libFilesystem = new vcms\LibFilesystem(__DIR__ . '/../..');
$libForm = new vcms\LibForm();
$libGallery = new vcms\LibGallery();
$libSecurityManager = new vcms\LibSecurityManager();
$libTime = new vcms\LibTime();
$libEvent = new vcms\LibEvent();

$libDb = new vcms\LibDb($libConfig, $libString);
$libGenericStorage = new vcms\LibGenericStorage($libDb, $libString);
$libImage = new vcms\LibImage($libTime, $libGenericStorage);
$libVerein = new vcms\LibAssociation($libDb, $libTime);
$libMitglied = new vcms\LibMember($libTime, $libDb, $libConfig);
$libModuleHandler = new vcms\LibModuleHandler();


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
if(isset($_SESSION['libAuth'])){
	$libAuth = $_SESSION['libAuth'];
} else {
	$libAuth = new vcms\LibAuth();
}

/*
* authenticate, if credentials are provided
*/
if(isset($_POST['intranet_login_username']) && isset($_POST['intranet_login_password'])){
	$_SESSION['libAuth'] = new vcms\LibAuth();
	$libAuth = $_SESSION['libAuth'];
	$libAuth->login($_POST['intranet_login_username'], $_POST['intranet_login_password']);
}