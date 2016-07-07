<?php
$moduleName = 'Intranet Downloadbereich';
$version = '2.22';
$installScript = '';
$uninstallScript = '';
$updateScript = '';

$ar = new LibAccessRestriction(array('F', 'B', 'P', 'C', 'G', 'W', 'Y'), '');

$pages[] = new LibPage('intranet_download_directories', 'scripts/', 'directories.php', $ar, 'Dateien');
$menuElementsInternet = array();
$menuElementsIntranet[] = new LibMenuEntry('intranet_download_directories', 'Dateien', 10050);
$menuElementsAdministration = array();
$includes[] = new LibInclude('intranet_downloads_download', 'scripts/', 'download.php', $ar);
$headerStrings = array();