<?php
$moduleName = "Intranet Personen";
$version = "2.18";
$styleSheet = "";
$installScript = "";
$uninstallScript = "";
$updateScript = "";

$pages[] = new LibPage("intranet_mitglied_listelebende", "scripts/", "member_list.php", new LibAccessRestriction(array("F", "B", "P"), ""), "Lebende Mitglieder");
$pages[] = new LibPage("intranet_mitglied_regionalzirkel", "scripts/", "regional_groups.php", new LibAccessRestriction(array("F", "B", "P"), ""), "Regionalzirkel");
$pages[] = new LibPage("intranet_mitglied_listeverstorbene", "scripts/", "member_list_deceased.php", new LibAccessRestriction(array("F", "B", "P"), ""), "Verstorbene");
$pages[] = new LibPage("intranet_person_daten", "scripts/", "person_data.php", new LibAccessRestriction(array("F", "B", "P", "C", "G", "W", "Y"), ""), "Personenprofil");
$pages[] = new LibPage("intranet_person_listedamenflor", "scripts/", "damenflor.php", new LibAccessRestriction(array("F", "B", "P", "C", "G", "W"), ""), "Damenflor");
$pages[] = new LibPage("intranet_person_struktur", "scripts/", "structure.php", new LibAccessRestriction(array("B", "P"), ""), "Altersstrukturen");
$pages[] = new LibPage("intranet_person_stammbaum", "scripts/", "genealogy.php", new LibAccessRestriction(array("F", "B", "P"), ""), "Stammbaum");
$dependencies[] = new LibMinDependency("Login-Modul", "base_internet_login", 1.0);

$menuElementsInternet = array();
$menuFolderMitglieder = new LibMenuFolder("", "Personen", 3000);
$menuFolderMitglieder->addElement(new LibMenuEntry("intranet_person_daten", "Mein Profil", 3001));
$menuFolderMitglieder->addElement(new LibMenuEntry("intranet_mitglied_listelebende", "Lebende Mitglieder", 3002));
$menuFolderMitglieder->addElement(new LibMenuEntry("intranet_mitglied_listeverstorbene", "Verstorbene Mitglieder", 3003));
$menuFolderMitglieder->addElement(new LibMenuEntry("intranet_person_listedamenflor", "Damenflor", 3004));
$menuFolderMitglieder->addElement(new LibMenuEntry("intranet_mitglied_regionalzirkel", "Regionalzirkel", 3005));
$menuFolderMitglieder->addElement(new LibMenuEntry("intranet_person_struktur", "Struktur", 3006));
$menuElementsIntranet[] = $menuFolderMitglieder;
$menuElementsAdministration = array();

$includes = array();
$headerStrings = array();
?>