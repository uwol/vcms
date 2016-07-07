<?php
$moduleName = 'Intranet Zipfelranking';
$version = '2.10';
$installScript = 'install/install.php';
$uninstallScript = '';
$updateScript = '';

$pages[] = new LibPage('intranet_zipfelranking', 'scripts/', 'zipfelranking.php', new LibAccessRestriction(array('F', 'B', 'P', 'C', 'G', 'W', 'Y'), ''), 'Zipfelranking');
$menuElementsInternet = array();
$menuElementsIntranet[] = new LibMenuEntry('intranet_zipfelranking', 'Zipfel', 10600);
$menuElementsAdministration = array();
$includes = array();
$headerStrings = array();