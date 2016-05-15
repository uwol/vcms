<?php
$moduleName = "Chargierkalender";
$version = "2.10";
$styleSheet = "";
$installScript = "install/install.php";
$uninstallScript = "";
$updateScript = "";

$adminar = new LibAccessRestriction("", array("senior", "consenior", "scriptor", "fuchsmajor", "fuchsmajor2", "quaestor", "jubelsenior", "internetwart"));

$pages[] = new LibPage("intranet_chargierkalender_kalender", "scripts/", "calendar.php", new LibAccessRestriction(array("F", "B", "P"), ""), "Chargierkalender");
$pages[] = new LibPage("intranet_chargierkalender_adminliste", "scripts/admin/", "list.php", $adminar, "Chargierveranstaltungen");
$pages[] = new LibPage("intranet_chargierkalender_adminveranstaltung", "scripts/admin/", "event.php", $adminar, "Chargierveranstaltung");

$menuElementsIntranet[] = new LibMenuEntry("intranet_chargierkalender_kalender", "Chargieren", 10525);
$menuElementsInternet = array();
$menuElementsAdministration[] = new LibMenuEntry("intranet_chargierkalender_adminliste", "Chargiereintrag", 560);
$dependencies = array();
$includes = array();
$headerStrings = array();
?>