<?php
$moduleName = "Login";
$version = "2.14";
$styleSheet = "";
$installScript = "";
$uninstallScript = "";
$updateScript = "";

$pages[] = new LibPage("login_login", "scripts/", "login.php", "", "Login");
$pages[] = new LibPage("login_registrierung", "scripts/", "registration.php", "", "Registrierung");
$pages[] = new LibPage("login_resetpassword", "scripts/", "resetpassword.php", "", "Passwort zurücksetzen");
$menuElementsInternet[] = new LibMenuEntryLogin("login_login", "Login", "Logout", 99999);
$menuElementsIntranet = array();
$menuElementsAdministration = array();
$dependencies = array();
$includes = array();
$headerStrings = array();
?>