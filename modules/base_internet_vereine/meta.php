<?php
$moduleName = "Base Internet Vereine";
$version = "2.06";
$styleSheet = "";
$installScript = "";
$uninstallScript = "";
$updateScript = "";

$pages[] = new LibPage("dachverband_vereindetail", "scripts/", "association.php", new LibAccessRestriction(array("F","B","P","C","G","W","Y"), ""), "Verein");
$dependencies = array();

$menuElementsInternet = array();
$menuElementsIntranet = array();
$menuElementsAdministration = array();
$includes = array();
$headerStrings = array();
?>