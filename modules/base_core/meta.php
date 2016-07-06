<?php
$moduleName = "Basisdaten des Systems";
$version = "2.26";
$styleSheet = "";
$installScript = "install/install.php";
$uninstallScript = "";
$updateScript = "install/update.php";

$pages[] = new LibPage("configuration", "scripts/", "configuration.php", new LibAccessRestriction("", array("internetwart")), "Konfiguration");
$menuElementsInternet = array();
$menuElementsIntranet = array();
$menuElementsAdministration[] = new LibMenuEntry("configuration", "Konfiguration", 3);
$dependencies = array();
$includes[] = new LibInclude("base_intranet_personenbild", "scripts/", "person_image.php","");
$headerStrings = array();
?>