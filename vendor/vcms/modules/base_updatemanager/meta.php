<?php
$moduleName = 'Modul-Manager';
$version = '';
$installScript = '';
$uninstallScript = '';
$updateScript = '';

$pages[] = new \vcms\module\LibPage('updater_liste', 'scripts/', 'module_manager.php', new \vcms\module\LibAccessRestriction('', array('internetwart')), 'Module');
$menuElementsInternet = array();
$menuElementsIntranet = array();
$menuElementsAdministration[] = new \vcms\menu\LibMenuEntry('updater_liste', 'Module', 2);
$includes = array();
$headerStrings = array();