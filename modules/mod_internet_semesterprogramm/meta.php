<?php
$moduleName = "Semesterprogramm";
$version = "2.43";
$styleSheet = "";
$installScript = "";
$uninstallScript = "";
$updateScript = "";

$ar = new LibAccessRestriction(array("F", "B", "P"), "");

$pages[] = new LibPage("semesterprogramm_calendar", "scripts/", "calendar.php", "", "Semesterprogramm");
$pages[] = new LibPage("semesterprogramm_event", "scripts/", "event.php", "", "Veranstaltung");
$pages[] = new LibPage("semesterprogramm_admin_galerienliste", "scripts/admin/", "gallery_list.php", $ar, "Foto-Verwaltung");
$pages[] = new LibPage("semesterprogramm_admin_galerie", "scripts/admin/", "gallery.php", $ar, "Galerie");
$menuElementsInternet[] = new LibMenuEntry("semesterprogramm_calendar", "Veranstaltungen", 200);
$menuElementsIntranet[] = new LibMenuEntry("semesterprogramm_admin_galerienliste", "Fotos", 30000);
$menuElementsAdministration = array();
$dependencies[] = new LibMinDependency("Login-Modul", "base_internet_login", 1.0);
$includes[] = new LibInclude("semesterprogramm_icalendar", "scripts/", "icalendar.php", "");
$includes[] = new LibInclude("semesterprogramm_picture", "scripts/", "picture.php", "");
$includes[] = new LibInclude("semesterprogramm_admin_galerie_upload", "scripts/admin/", "gallery_upload.php", $ar);
$headerStrings = array();
?>