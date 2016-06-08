<?php
$moduleName = "Homepage";
$version = "2.61";
$styleSheet = "";
$installScript = "install/install.php";
$uninstallScript = "";
$updateScript = "";

$pages[] = new LibPage("home", "scripts/", "home.php", "", "Startseite");
$pages[] = new LibPage("intranet_internethome_nachricht_adminliste", "scripts/admin/", "announcement_list.php", new LibAccessRestriction("", array("senior", "consenior", "scriptor", "fuchsmajor", "fuchsmajor2", "quaestor", "jubelsenior", "internetwart")), "Ankündigungen");
$pages[] = new LibPage("intranet_internethome_nachricht_adminankuendigung", "scripts/admin/", "announcement.php", new LibAccessRestriction("", array("senior", "consenior", "scriptor", "fuchsmajor", "fuchsmajor2", "quaestor", "jubelsenior", "internetwart")), "Ankündigung");

$menuElementsIntranet = array();
$menuElementsInternet[] = new LibMenuEntry("home", "Home", 100);
$menuElementsAdministration[] = new LibMenuEntry("intranet_internethome_nachricht_adminliste", "Start", 200);
$dependencies = array();
$includes = array();
$headerStrings = array();
?>