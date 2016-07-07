<?php
$moduleName = 'Verein';
$version = '1.0';
$installScript = '';
$uninstallScript = '';
$updateScript = '';

$pages[] = new LibPage('v_ueberuns', 'custom/', 'wirueberuns.html', '', 'Über uns');
$pages[] = new LibPage('v_aktivitaeten', 'custom/', 'aktivitaeten.html', '', 'Aktivitäten');
$pages[] = new LibPage('v_haus', 'custom/', 'haus.html', '', 'Haus');
$pages[] = new LibPage('v_geschichte', 'custom/', 'geschichte.html', '', 'Geschichte');
$pages[] = new LibPage('v_prinzipien', 'custom/', 'prinzipien.html', '', 'Prinzipien');
$pages[] = new LibPage('v_symbole', 'custom/', 'symbole.html', '', 'Symbole');

$menuFolder = new LibMenuFolder('v_ueberuns', 'Verein', 600);
$menuFolder->addElement(new LibMenuEntry('v_ueberuns', 'Wir über uns', 100));
$menuFolder->addElement(new LibMenuEntry('v_aktivitaeten', 'Aktivitäten', 200));
$menuFolder->addElement(new LibMenuEntry('v_haus', 'Haus', 300));
$menuFolder->addElement(new LibMenuEntry('v_geschichte', 'Geschichte', 400));
$menuFolder->addElement(new LibMenuEntry('v_prinzipien', 'Prinzipien', 500));
$menuFolder->addElement(new LibMenuEntry('v_symbole', 'Symbole', 600));
$menuElementsInternet[] = $menuFolder;
$menuElementsIntranet = array();
$menuElementsAdministration = array();
$includes = array();
$headerStrings = array();