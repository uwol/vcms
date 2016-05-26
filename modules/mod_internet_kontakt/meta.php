<?php
$moduleName = "Kontakt";
$version = "2.11";
$styleSheet = "";
$installScript = "";
$uninstallScript = "";
$updateScript = "";

$pages[] = new LibPage("kontakt_kontakt", "scripts/", "contact.php", "", "Kontakt");
$menuElementsInternet[] = new LibMenuEntry("kontakt_kontakt", "Kontakt", 30000);
$menuElementsIntranet = array();
$menuElementsAdministration = array();
$dependencies = array();
$includes = array();
$headerStrings[] = '<meta name="robots" content="noindex" />';
?>