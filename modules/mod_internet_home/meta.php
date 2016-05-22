<?php
$moduleName = "Homepage";
$version = "2.32";
$styleSheet = "";
$installScript = "install/install.php";
$uninstallScript = "";
$updateScript = "";

$pages[] = new LibPage("home", "scripts/", "home.php", "", "Startseite");
$pages[] = new LibPage("home_ankuendigungen", "scripts/", "announcement_list.php", "", "Ankündigungen");
$pages[] = new LibPage("intranet_internethome_nachricht_adminliste", "scripts/admin/", "announcement_list.php", new LibAccessRestriction("", array("senior", "consenior", "scriptor", "fuchsmajor", "fuchsmajor2", "quaestor", "jubelsenior", "internetwart")), "Ankündigungen");
$pages[] = new LibPage("intranet_internethome_nachricht_adminankuendigung", "scripts/admin/", "announcement.php", new LibAccessRestriction("", array("senior", "consenior", "scriptor", "fuchsmajor", "fuchsmajor2", "quaestor", "jubelsenior", "internetwart")), "Ankündigung");

$menuElementsIntranet = array();
$menuElementsInternet[] = new LibMenuEntry("home", "Home", 100);
$menuElementsAdministration[] = new LibMenuEntry("intranet_internethome_nachricht_adminliste", "Startseite", 200);
$dependencies = array();
$includes[] = new LibInclude("internet_home_rssfeed", "scripts/", "rssfeed.php", "");
$headerStrings[] = '<link rel="alternate" type="application/rss+xml" title="Internet-Neuigkeiten als RSS-Feed" href="http://'.$libConfig->sitePath.'/inc.php?iid=internet_home_rssfeed" />';
?>