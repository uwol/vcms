<?php
$moduleName = "Semesterhistorie";
$version = "2.13";
$styleSheet = "";
$installScript = "";
$uninstallScript = "";
$updateScript = "";

$pages[] = new LibPage("semesterhistorie_liste", "scripts/", "history.php", new LibAccessRestriction(array("F", "B", "P", "C", "G", "W", "Y"), ""), "Semesterhistorie");
$menuElementsInternet = array();
$menuElementsIntranet[] = new LibMenuEntry("semesterhistorie_liste", "Historie", 8000);
$menuElementsAdministration = array();
$dependencies[] = new LibMinDependency("Login-Modul", "base_internet_login", 1.0);
$dependencies[] = new LibMinDependency("Mitglieds-Modul", "base_intranet_personen", 1.0);
$includes = array();
$headerStrings = array();
?>