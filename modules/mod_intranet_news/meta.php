<?php
$moduleName = 'Intranet Neuigkeiten';
$version = '2.27';
$installScript = 'install/install.php';
$uninstallScript = '';
$updateScript = '';

$pages[] = new LibPage('intranet_news_news', 'scripts/', 'list.php', new LibAccessRestriction(array('F', 'B', 'P', 'C', 'G', 'W', 'Y'), ''), 'Neuigkeiten');
$pages[] = new LibPage('intranet_news_schreiben', 'scripts/', 'write.php', new LibAccessRestriction(array('F', 'B', 'P', 'C', 'G', 'W', 'Y'), ''), 'Beitrag schreiben');
$menuElementsInternet = array();
$menuElementsIntranet[] = new LibMenuEntry('intranet_news_news', 'Neues', 500);
$menuElementsAdministration = array();
$includes = array();
$headerStrings = array();