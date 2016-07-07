<?php
$moduleName = 'Dachverband';
$version = '1.0';
$installScript = '';
$uninstallScript = '';
$updateScript = '';

$pages[] = new LibPage('dv_geschichte', 'custom/', 'geschichte.html', '', 'Geschichte');
$pages[] = new LibPage('dv_symbole', 'custom/', 'symbole.html', '', 'Symbole');

$menuFolder = new LibMenuFolder('dv_geschichte', 'Dachverband', 700);
$menuFolder->addElement(new LibMenuEntry('dv_geschichte', 'Geschichte', 200));
$menuFolder->addElement(new LibMenuEntry('dv_symbole', 'Symbole', 300));
$menuElementsInternet[] = $menuFolder;
$menuElementsIntranet = array();
$menuElementsAdministration = array();
$includes = array();
$headerStrings = array();