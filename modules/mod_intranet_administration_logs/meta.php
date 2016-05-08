<?php
$moduleName = "Intranet Administration Logs";
$version = "2.05";
$styleSheet = "";
$installScript = "";
$uninstallScript = "";
$updateScript = "";

$pages[] = new LibPage("intranet_logs_logs", "scripts/", "logs.php", new LibAccessRestriction("", array("internetwart")), "Logs");

$menuElementsInternet = array();
$menuElementsIntranet = array();
$menuElementsAdministration[] = new LibMenuEntry("intranet_logs_logs", "Logs", 229);

$includes = array();
$headerStrings = array();
$dependencies[] = new LibMinDependency("Login-Modul", "base_internet_login", 1.0);
?>