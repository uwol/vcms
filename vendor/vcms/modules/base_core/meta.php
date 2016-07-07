<?php
$moduleName = 'Basisdaten des Systems';
$version = '';
$installScript = '';
$uninstallScript = '';
$updateScript = '';

$pages[] = new \vcms\module\LibPage('configuration', 'scripts/', 'configuration.php', new \vcms\module\LibAccessRestriction('', array('internetwart')), 'Konfiguration');
$menuElementsInternet = array();
$menuElementsIntranet = array();
$menuElementsAdministration[] = new \vcms\menu\LibMenuEntry('configuration', 'Konfiguration', 3);
$includes[] = new \vcms\module\LibInclude('base_intranet_personenbild', 'scripts/', 'person_image.php','');
$headerStrings = array();