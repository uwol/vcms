<?php
$moduleName = "Intranet Rundbrief";
$version = "2.10";
$styleSheet = "";
$installScript = "install/install.php";
$uninstallScript = "";
$updateScript = "install/update.php";

$ar = new LibAccessRestriction(array("F", "B", "P", "C", "G", "W"), "");

$pages[] = new LibPage("intranet_rundbrief_schreiben", "scripts/", "write.php", $ar, "Rundbrief");
$pages[] = new LibPage("intranet_rundbrief_senden", "scripts/", "send.php", $ar, "Rundbrief");
$dependencies[] = new LibMinDependency("Dependency zum Login-Modul", "base_internet_login", 1.0);
$dependencies[] = new LibMinDependency("Dependency zum Mitglied-Modul", "base_intranet_personen", 1.0);
$menuElementsInternet = array();
$menuElementsIntranet[] = new LibMenuEntry("intranet_rundbrief_schreiben", "Rundbrief", 4000);
$menuElementsAdministration = array();
$includes = array();
$headerStrings = array();
?>