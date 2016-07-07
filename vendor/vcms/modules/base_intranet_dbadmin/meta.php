<?php
$moduleName = 'Intranet Verwaltung';
$version = '';
$installScript = '';
$uninstallScript = '';
$updateScript = '';

$vorstand = array('senior', 'jubelsenior', 'consenior', 'fuchsmajor', 'fuchsmajor2', 'scriptor', 'quaestor', 'dachverbandsberichterstatter');
$internetwart = array('internetwart');

//Datenbank
$pages[] = new \vcms\module\LibPage('intranet_admin_db_personenliste', 'scripts/person/', 'personen.php', new \vcms\module\LibAccessRestriction('', array_merge($vorstand, $internetwart)), 'Personen');
$pages[] = new \vcms\module\LibPage('intranet_admin_db_person', 'scripts/person/', 'person.php', new \vcms\module\LibAccessRestriction('', array_merge($vorstand, $internetwart)), 'Person');
$pages[] = new \vcms\module\LibPage('intranet_admin_db_semesterliste', 'scripts/semester/', 'semesters.php', new \vcms\module\LibAccessRestriction('', $internetwart), 'Semester');
$pages[] = new \vcms\module\LibPage('intranet_admin_db_semester', 'scripts/semester/', 'semester.php', new \vcms\module\LibAccessRestriction('', $internetwart), 'Semester');
$pages[] = new \vcms\module\LibPage('intranet_admin_db_veranstaltungsliste', 'scripts/veranstaltung/', 'veranstaltungen.php', new \vcms\module\LibAccessRestriction('', array_merge($vorstand, $internetwart)), 'Veranstaltungen');
$pages[] = new \vcms\module\LibPage('intranet_admin_db_veranstaltung', 'scripts/veranstaltung/', 'veranstaltung.php', new \vcms\module\LibAccessRestriction('', array_merge($vorstand, $internetwart)), 'Veranstaltung');
$pages[] = new \vcms\module\LibPage('intranet_admin_db_vereinsliste', 'scripts/verein/', 'vereine.php', new \vcms\module\LibAccessRestriction('', array_merge($vorstand, $internetwart)), 'Vereine');
$pages[] = new \vcms\module\LibPage('intranet_admin_db_verein', 'scripts/verein/', 'verein.php', new \vcms\module\LibAccessRestriction('', array_merge($vorstand, $internetwart)), 'Verein');
$pages[] = new \vcms\module\LibPage('intranet_admin_db_vipliste', 'scripts/vip/', 'vips.php', new \vcms\module\LibAccessRestriction('', array_merge($vorstand, $internetwart)), 'Vips');
$pages[] = new \vcms\module\LibPage('intranet_admin_db_vip', 'scripts/vip/', 'vip.php', new \vcms\module\LibAccessRestriction('', array_merge($vorstand, $internetwart)), 'Vip');
$pages[] = new \vcms\module\LibPage('intranet_admin_db_vereinsmitgliedschaftenliste', 'scripts/vereinsmitgliedschaft/', 'vereinsmitgliedschaften.php', new \vcms\module\LibAccessRestriction('', array_merge($vorstand, $internetwart)), 'Vereinsmitgliedschaften');
$pages[] = new \vcms\module\LibPage('intranet_admin_db_vereinsmitgliedschaft', 'scripts/vereinsmitgliedschaft/', 'vereinsmitgliedschaft.php', new \vcms\module\LibAccessRestriction('', array_merge($vorstand, $internetwart)), 'Vereinsmitgliedschaft');

$pages[] = new \vcms\module\LibPage('intranet_admin_db_gruppen', 'scripts/', 'gruppen.php', new \vcms\module\LibAccessRestriction('', $internetwart), 'Gruppen');
$pages[] = new \vcms\module\LibPage('intranet_admin_db_status', 'scripts/', 'status.php', new \vcms\module\LibAccessRestriction('', $internetwart), 'Status');
$pages[] = new \vcms\module\LibPage('intranet_admin_db_region', 'scripts/', 'regionen.php', new \vcms\module\LibAccessRestriction('', $internetwart), 'Regionen');


$menuFolderDatenbank = new LibMenuFolder('', 'Daten', 50);
$menuFolderDatenbank->addElement(new \vcms\menu\LibMenuEntry('intranet_admin_db_personenliste', 'Personen', 200));
$menuFolderDatenbank->addElement(new \vcms\menu\LibMenuEntry('intranet_admin_db_semesterliste', 'Semester', 300));
$menuFolderDatenbank->addElement(new \vcms\menu\LibMenuEntry('intranet_admin_db_veranstaltungsliste', 'Veranstaltungen', 400));
$menuFolderDatenbank->addElement(new \vcms\menu\LibMenuEntry('intranet_admin_db_vereinsliste', 'Vereine', 500));
$menuFolderDatenbank->addElement(new \vcms\menu\LibMenuEntry('intranet_admin_db_vipliste', 'Vips', 550));
$menuFolderDatenbank->addElement(new \vcms\menu\LibMenuEntry('intranet_admin_db_vereinsmitgliedschaftenliste', 'Mitgliedschaften', 501));
$menuFolderDatenbank->addElement(new \vcms\menu\LibMenuEntry('intranet_admin_db_gruppen', 'Gruppen', 800));
$menuFolderDatenbank->addElement(new \vcms\menu\LibMenuEntry('intranet_admin_db_status', 'Status', 900));
$menuFolderDatenbank->addElement(new \vcms\menu\LibMenuEntry('intranet_admin_db_region', 'Regionen', 1000));

$menuElementsInternet = array();
$menuElementsIntranet = array();
$menuElementsAdministration[] = $menuFolderDatenbank;

$includes = array();
$headerStrings = array();