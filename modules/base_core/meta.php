<?php
$moduleName = "Basisdaten des Systems";
$version = "2.19";
$styleSheet = "";
$installScript = "install/install.php";
$uninstallScript = "";
$updateScript = "install/update.php";

$pages[] = new LibPage("intranet_verwaltung_overview", "scripts/", "intranet_admin_overview.php", new LibAccessRestriction("", array("senior", "consenior", "fuchsmajor", "fuchsmajor2", "scriptor", "quaestor", "jubelsenior", "ahv_senior", "ahv_consenior", "ahv_keilbeauftragter", "ahv_scriptor", "ahv_quaestor", "ahv_beisitzer1", "ahv_beisitzer2", "hv_vorsitzender", "hv_kassierer", "hv_beisitzer1", "hv_beisitzer2", "archivar", "redaktionswart", "hauswart", "bierwart", "kuehlschrankwart", "thekenwart", "internetwart", "technikwart", "fotowart", "wirtschaftskassenwart", "wichswart", "bootshauswart", "huettenwart", "fechtwart", "stammtischwart", "musikwart", "ausflugswart", "sportwart", "couleurartikelwart", "ferienordner","dachverbandsberichterstatter")), "Systemstatus");
$pages[] = new LibPage("configuration", "scripts/", "configuration.php", new LibAccessRestriction("", array("internetwart")), "Konfiguration");
$menuElementsInternet = array();
$menuElementsIntranet = array();
$menuElementsAdministration[] = new LibMenuEntry("configuration", "Konfiguration", 3);
$dependencies = array();
$includes[] = new LibInclude("base_intranet_personenbild", "scripts/", "person_image.php","");
$headerStrings = array();
?>