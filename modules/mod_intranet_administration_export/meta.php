<?php
$moduleName = "Intranet Administration für Export";
$version = "2.07";
$styleSheet = "";
$installScript = "";
$uninstallScript = "";
$updateScript = "";

$vorstand = array("senior", "jubelsenior", "consenior", "fuchsmajor", "fuchsmajor2", "scriptor", "quaestor");
$vorort = array("vop", "vvop", "vopxx", "vopxxx", "vopxxxx");
$ahvorstand = array("ahv_senior", "ahv_consenior", "ahv_scriptor", "ahv_quaestor", "hv_vorsitzender", "hv_kassierer");
$internetwart = array("internetwart");

$ar = new LibAccessRestriction("", array_merge($vorstand, $vorort, $ahvorstand, $internetwart));

$pages[] = new LibPage("intranet_admin_export", "scripts/", "export.php", $ar, "Export");

$includes[] = new LibInclude("intranet_admin_export_daten_adressen", "scripts/", "data_addresses.php", $ar);
$includes[] = new LibInclude("intranet_admin_export_daten_geburtstage", "scripts/", "data_birthdays.php", $ar);
$includes[] = new LibInclude("intranet_admin_export_daten_rundegeburtstage", "scripts/", "data_big_birthdays.php", $ar);
$includes[] = new LibInclude("intranet_admin_export_daten_jubilaeen", "scripts/", "data_anniversaries.php", $ar);

$menuElementsInternet = array();
$menuElementsIntranet = array();
$menuElementsAdministration[] = new LibMenuEntry("intranet_admin_export", "Export", 210);

$headerStrings = array();
$dependencies[] = new LibMinDependency("Login-Modul", "base_internet_login", 1.0);
?>