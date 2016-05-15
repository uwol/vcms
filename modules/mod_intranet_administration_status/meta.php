<?php
$moduleName = "Intranet Admin Statusänderungen";
$version = "2.08";
$styleSheet = "";
$installScript = "";
$uninstallScript = "";
$updateScript = "";

$ahvorstand = array("ahv_senior","ahv_consenior","ahv_keilbeauftragter","ahv_scriptor","ahv_quaestor","hv_vorsitzender","hv_kassierer");
$vorstand = array("senior","jubelsenior","consenior","fuchsmajor","fuchsmajor2","scriptor","quaestor");
$internetwart = array("internetwart");
$andereWarte = array("dachverbandsberichterstatter", "archivar", "redaktionswart", "ferienordner");

$pages[] = new LibPage("intranet_admin_statusaenderungen", "scripts/", "status_changes.php", new LibAccessRestriction("", array_merge($vorstand, $ahvorstand, $internetwart, $andereWarte)), "Statusänderungen");

$menuElementsInternet = array();
$menuElementsIntranet = array();
$menuElementsAdministration[] = new LibMenuEntry("intranet_admin_statusaenderungen", "Status", 220);

$includes = array();
$headerStrings = array();
$dependencies[] = new LibMinDependency("Login-Modul","base_internet_login",1.0);
?>