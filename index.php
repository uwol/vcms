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

if(is_file('installer.php')){
	die('Um das VCMS zu nutzen, muss nach der Installation die Datei installer.php entfernt werden.');
}

require_once('custom/systemconfig.php');
require_once('vendor/vcms/initialize.php');


$libDb->connect();
$libCronjobs->executeDueJobs();


if(isset($_POST['intranet_login_email']) && isset($_POST['intranet_login_password'])){
	$_SESSION['libAuth'] = new \vcms\LibAuth();
	$libAuth = $_SESSION['libAuth'];
	$libAuth->login($_POST['intranet_login_email'], $_POST['intranet_login_password']);
}


$libMenuInternet = $libModuleHandler->getMenuInternet();
$libMenuIntranet = $libModuleHandler->getMenuIntranet();
$libMenuAdministration = $libModuleHandler->getMenuAdministration();


if(!isset($_GET['pid']) || $_GET['pid'] == ''){
	$defaultHomeExists = $libModuleHandler->pageExists($libConfig->defaultHome);

	if($defaultHomeExists){
		$libGlobal->pid = $libConfig->defaultHome;
	} else {
		$libGlobal->pid = 'login';
	}
} else {
	$libGlobal->pid = $_GET['pid'];
}


if(!$libModuleHandler->pageExists($libGlobal->pid)){
	http_response_code(404);
	die('HTTP-Fehler 404: Seite nicht gefunden.');
} elseif(!$libSecurityManager->hasAccess($libModuleHandler->getPage($libGlobal->pid), $libAuth)){
	http_response_code(403);
}


$libGlobal->page = $libModuleHandler->getPage($libGlobal->pid);
$libGlobal->module = $libModuleHandler->getModuleByPageid($libGlobal->pid);


require_once('vendor/vcms/layout/header.php');

if(is_object($libGlobal->page) && $libSecurityManager->hasAccess($libGlobal->page, $libAuth)){
	if(is_file($libGlobal->page->getPath())){
		require_once($libGlobal->page->getPath());
	}
} else {
	echo '<h1>Zugriffsfehler</h1>';
	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();
	echo '<p>Für diese Seite ist eine <a href="index.php?pid=login">Anmeldung im Intranet</a> nötig.</p>';
}

require_once('vendor/vcms/layout/footer.php');
