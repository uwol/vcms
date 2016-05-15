<?php
$moduleName = "Login";
$version = "2.08";
$styleSheet = "";
$installScript = "";
$uninstallScript = "";
$updateScript = "";

$pages[] = new LibPage("login_login", "scripts/", "login.php", "", "Login");
$pages[] = new LibPage("login_registrierung", "scripts/", "registration.php", "", "Registrierung");
$pages[] = new LibPage("login_resetpassword", "scripts/", "resetpassword.php", "", "Passwort zurücksetzen");
$menuElementsInternet[] = new LibMenuEntry("login_login", "Login", 99999);
$menuElementsIntranet = array();
$menuElementsAdministration = array();
$dependencies = array();
$includes = array();
$headerStrings = array();
?>