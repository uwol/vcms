<?php
$moduleName = "Intranet Reservierungen";
$version = "2.29";
$styleSheet = "";
$installScript = "install/install.php";
$uninstallScript = "";
$updateScript = "";

$ar = new LibAccessRestriction(array("F", "B", "P", "C", "G", "W", "Y"), "");

$pages[] = new LibPage("intranet_reservierung_liste", "scripts/", "list.php", $ar, "Reservierungen");
$pages[] = new LibPage("intranet_reservierung_buchen", "scripts/", "booking.php", $ar, "Reservierung");
$dependencies[] = new LibMinDependency("Login-Modul", "base_internet_login", 1.0);
$menuElementsInternet = array();
$menuElementsIntranet[] = new LibMenuEntry("intranet_reservierung_liste", "Reservierung", 4000);
$menuElementsAdministration = array();
$includes = array();
$headerStrings = array();
?>