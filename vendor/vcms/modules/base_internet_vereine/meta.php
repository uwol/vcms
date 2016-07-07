<?php
$moduleName = 'Base Internet Vereine';
$version = '';
$installScript = '';
$uninstallScript = '';
$updateScript = '';

$pages[] = new \vcms\module\LibPage('vereindetail', 'scripts/', 'association.php', '', 'Verein');

$menuElementsInternet = array();
$menuElementsIntranet = array();
$menuElementsAdministration = array();
$includes = array();
$headerStrings = array();