<?php
$moduleName = "Intranet Home";
$version = "2.08";
$styleSheet = "custom/style.css";
$installScript = "";
$uninstallScript = "";
$updateScript = "install/update.php";

$pages[] = new LibPage("intranet_home", "scripts/", "home.php", new LibAccessRestriction(array("F", "B", "P", "C", "G", "W", "Y"), ""), "Intranet");
$dependencies[] = new LibMinDependency("Dependency zum Login-Modul", "base_internet_login", 1.0);
$menuElementsInternet = array();
$menuElementsIntranet[] = new LibMenuEntry("intranet_home", "Home", 50);
$menuElementsAdministration = array();
$includes[] = new LibInclude("intranet_kalender_geburtstageaktivitas", "scripts/", "icalendar_birthdays_aktivitas.php", "");
$includes[] = new LibInclude("intranet_kalender_todestage", "scripts/", "icalendar_obituary.php", "");
$headerStrings = array();
?>