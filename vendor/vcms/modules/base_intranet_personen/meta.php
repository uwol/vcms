<?php
$moduleName = 'Intranet Personen';
$version = '';
$installScript = '';
$uninstallScript = '';
$updateScript = '';

$pages[] = new \vcms\module\LibPage('intranet_mitglied_listelebende', 'scripts/', 'member_list.php', new \vcms\module\LibAccessRestriction(array('F', 'B', 'P', 'C', 'G', 'W', 'Y'), ''), 'Lebende Mitglieder');
$pages[] = new \vcms\module\LibPage('intranet_mitglied_regionalzirkel', 'scripts/', 'regional_groups.php', new \vcms\module\LibAccessRestriction(array('F', 'B', 'P', 'C', 'G', 'W', 'Y'), ''), 'Regionalzirkel');
$pages[] = new \vcms\module\LibPage('intranet_mitglied_listeverstorbene', 'scripts/', 'member_list_deceased.php', new \vcms\module\LibAccessRestriction(array('F', 'B', 'P', 'C', 'G', 'W', 'Y'), ''), 'Verstorbene');
$pages[] = new \vcms\module\LibPage('intranet_person_daten', 'scripts/', 'person_data.php', new \vcms\module\LibAccessRestriction(array('F', 'B', 'P', 'C', 'G', 'W', 'Y'), ''), 'Personenprofil');
$pages[] = new \vcms\module\LibPage('intranet_person_listedamenflor', 'scripts/', 'damenflor.php', new \vcms\module\LibAccessRestriction(array('F', 'B', 'P', 'C', 'G', 'W'), ''), 'Damenflor');
$pages[] = new \vcms\module\LibPage('intranet_person_statistics', 'scripts/', 'statistics.php', new \vcms\module\LibAccessRestriction(array('B', 'P'), ''), 'Statistik');
$pages[] = new \vcms\module\LibPage('intranet_person_stammbaum', 'scripts/', 'genealogy.php', new \vcms\module\LibAccessRestriction(array('F', 'B', 'P', 'C', 'G', 'W', 'Y'), ''), 'Stammbaum');

$menuElementsInternet = array();
$menuFolderMitglieder = new LibMenuFolder('', 'Personen', 3000);
$menuFolderMitglieder->addElement(new \vcms\menu\LibMenuEntry('intranet_person_daten', 'Mein Profil', 3001));
$menuFolderMitglieder->addElement(new \vcms\menu\LibMenuEntry('intranet_mitglied_listelebende', 'Lebende Mitglieder', 3002));
$menuFolderMitglieder->addElement(new \vcms\menu\LibMenuEntry('intranet_mitglied_listeverstorbene', 'Verstorbene Mitglieder', 3003));
$menuFolderMitglieder->addElement(new \vcms\menu\LibMenuEntry('intranet_person_listedamenflor', 'Damenflor', 3004));
$menuFolderMitglieder->addElement(new \vcms\menu\LibMenuEntry('intranet_mitglied_regionalzirkel', 'Regionalzirkel', 3005));
$menuFolderMitglieder->addElement(new \vcms\menu\LibMenuEntry('intranet_person_statistics', 'Statistik', 3006));
$menuElementsIntranet[] = $menuFolderMitglieder;
$menuElementsAdministration = array();

$includes = array();
$headerStrings[] = '<script src="styles/chart/Chart.min.js"></script>';