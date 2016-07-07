<?php
$moduleName = 'Login';
$version = '';
$installScript = '';
$uninstallScript = '';
$updateScript = '';

$pages[] = new \vcms\module\LibPage('login_login', 'scripts/', 'login.php', '', 'Login');
$pages[] = new \vcms\module\LibPage('login_registrierung', 'scripts/', 'registration.php', '', 'Registrierung');
$pages[] = new \vcms\module\LibPage('login_resetpassword', 'scripts/', 'resetpassword.php', '', 'Passwort zurücksetzen');
$menuElementsInternet[] = new \vcms\menu\LibMenuEntryLogin('login_login', 'Login', 'Logout', 99999);
$menuElementsIntranet = array();
$menuElementsAdministration = array();
$includes = array();
$headerStrings = array();