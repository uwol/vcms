<?php
$moduleName = "Modul-Manager";
$version = "2.13";
$styleSheet = "";
$installScript = "";
$uninstallScript = "";
$updateScript = "";

$pages[] = new LibPage("updater_liste", "scripts/", "module_manager.php", new LibAccessRestriction("", array("internetwart")), "Module");
$menuElementsInternet = array();
$menuElementsIntranet = array();
$menuElementsAdministration[] = new LibMenuEntry("updater_liste", "Module", 2);
$dependencies = array();
$includes = array();
$headerStrings = array();
?>