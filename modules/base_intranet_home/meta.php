<?php
$moduleName = "Intranet-Portal";
$version = "2.54";
$styleSheet = "";
$installScript = "";
$uninstallScript = "";
$updateScript = "install/update.php";

$pages[] = new LibPage("intranet_home", "scripts/", "home.php", new LibAccessRestriction(array("F", "B", "P", "C", "G", "W", "Y"), ""), "Portal");
$dependencies[] = new LibMinDependency("Login-Modul", "base_internet_login", 1.0);
$menuElementsInternet = array();
$menuElementsIntranet[] = new LibMenuEntry("intranet_home", "Portal", 50);
$menuElementsAdministration = array();
$includes[] = new LibInclude("intranet_kalender_geburtstageaktivitas", "scripts/", "icalendar_birthdays_aktivitas.php", "");
$includes[] = new LibInclude("intranet_kalender_todestage", "scripts/", "icalendar_obituary.php", "");
$headerStrings = array();
?>