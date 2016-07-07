<?php
$moduleName = 'Intranet-Portal';
$version = '';
$installScript = '';
$uninstallScript = '';
$updateScript = '';

$pages[] = new \vcms\module\LibPage('intranet_home', 'scripts/', 'home.php', new LibAccessRestriction(array('F', 'B', 'P', 'C', 'G', 'W', 'Y'), ''), 'Portal');
$menuElementsInternet = array();
$menuElementsIntranet[] = new \vcms\menu\LibMenuEntry('intranet_home', 'Portal', 50);
$menuElementsAdministration = array();
$includes[] = new \vcms\module\LibInclude('intranet_kalender_geburtstageaktivitas', 'scripts/', 'icalendar_birthdays_aktivitas.php', '');
$includes[] = new \vcms\module\LibInclude('intranet_kalender_todestage', 'scripts/', 'icalendar_obituary.php', '');
$headerStrings = array();