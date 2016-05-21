<!DOCTYPE html>
<html lang="de" prefix="og: http://ogp.me/ns#">
<?php
$pageTitle = $libConfig->verbindungName . ' - ' . $libGlobal->page->getTitle();

if($libGlobal->page->getPid() == 'semesterprogramm_event'){
	if(isset($_REQUEST['eventid'])){
		$stmt = $libDb->prepare("SELECT titel, datum FROM base_veranstaltung WHERE id=:id");
		$stmt->bindValue(':id', $_REQUEST['eventid'], PDO::PARAM_INT);
		$stmt->execute();
		$event = $stmt->fetch(PDO::FETCH_ASSOC);

		if($event['titel'] != ''){
			$pageTitle = $libConfig->verbindungName . ' - ' . $event['titel'] . ' am ' . $libTime->formatDateTimeString($event['datum'], 2);
		}

		unset($event);
		unset($stmt);
	}
}

echo '  <head>' . "\r\n";
echo '    <meta charset="utf-8" />' . "\r\n";
echo '    <meta http-equiv="X-UA-Compatible" content="IE=edge" />' . "\r\n";
echo '    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">' . "\r\n";
echo '    <title>' .$pageTitle. '</title>' . "\r\n";
echo '    <meta name="description" content="' .$libConfig->seiteBeschreibung. '" />' . "\r\n";
echo '    <meta name="keywords" content="' .$libConfig->seiteKeywords. '" />' . "\r\n";

/*
* stylesheets
*/
echo '    <link rel="stylesheet" href="styles/bootstrap/bootstrap.min.css">' . "\r\n";
echo '    <link rel="stylesheet" href="styles/screen.css" />' . "\r\n";

if($libGlobal->module->getStyleSheet() != ""){
	echo '    <link rel="stylesheet" href="' .$libModuleHandler->getModuleDirectory().$libGlobal->module->getStyleSheet(). '" />'."\r\n";
}

echo '    <link rel="stylesheet" href="custom/styles/screen.css" />' . "\r\n";

if($libGenericStorage->loadValue('base_core', 'showTrauerflor')){
	echo '    <style type="text/css">' . "\r\n";
	echo '      #container:before {' . "\r\n";
	echo '        content:url("data:image/svg+xml;utf8,<svg xmlns:svg=\'http://www.w3.org/2000/svg\' xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\' height=\'150\' width=\'150\'><path d=\'M 0,-25 125,100\' style=\'stroke:%23000;stroke-width:25\' /></svg>");' . "\r\n";
	echo '        position:absolute;' . "\r\n";
	echo '        right:0;' . "\r\n";
	echo '        top:0;' . "\r\n";
	echo '      }' . "\r\n";
	echo '    </style>' . "\r\n";
}

if(is_array($libGlobal->module->getHeaderStrings())){
	foreach($libGlobal->module->getHeaderStrings() as $headerString){
		echo '    ' .$headerString. "\r\n";
	}
}

/*
* jquery
*/
echo '    <script src="styles/jquery-2.2.3.min.js"></script>' . "\r\n";
echo '    <script src="styles/bootstrap/bootstrap.min.js"></script>' . "\r\n";

/*
* robots
*/
if($libGlobal->page->hasAccessRestriction()){
	echo '    <meta name="robots" content="noindex, nofollow, noarchive" />' . "\r\n";
} else {
	echo '    <meta name="robots" content="index, follow, noarchive" />' . "\r\n";
}

/*
* Opengraph / Facebook meta data
*/
if($libGlobal->pid == $libConfig->defaultHome){
	echo '    <link rel="canonical" href="http://' .$libConfig->sitePath. '/"/>' . "\r\n";
    echo '    <meta property="og:title" content="' .$libConfig->verbindungName. '"/>' . "\r\n";
    echo '    <meta property="og:type" content="non_profit"/>' . "\r\n";
    echo '    <meta property="og:url" content="http://' .$libConfig->sitePath. '/"/>' . "\r\n";
    echo '    <meta property="og:image" content="http://' .$libConfig->sitePath. '/custom/design/topleft.png"/>' . "\r\n";
    echo '    <meta property="og:site_name" content="' .$libConfig->sitePath. '"/>' . "\r\n";
    echo '    <meta property="fb:admins" content="' .$libGenericStorage->loadValueInCurrentModule('fb:admins'). '"/>' . "\r\n";
    echo '    <meta property="og:description" content="' .$libConfig->seiteBeschreibung. '"/>' . "\r\n";
    echo '    <meta property="og:street-address" content="' .$libConfig->verbindungStrasse. '"/>' . "\r\n";
    echo '    <meta property="og:locality" content="' .$libConfig->verbindungOrt. '"/>' . "\r\n";
    echo '    <meta property="og:postal-code" content="' .$libConfig->verbindungPlz. '"/>' . "\r\n";
    echo '    <meta property="og:country-name" content="' .$libConfig->verbindungLand. '"/>' . "\r\n";
    echo '    <meta property="og:email" content="' .$libConfig->emailInfo. '"/>' . "\r\n";
    echo '    <meta property="og:phone_number" content="' .$libConfig->verbindungTelefon. '"/>' . "\r\n";
}

echo '  </head>' . "\r\n";
echo '  <body>' . "\r\n";
echo '    <div id="container" class="container">' . "\r\n";
echo '      <div class="row">' . "\r\n";
echo '        <div id="logo" class="col-md-2 hidden-xs"></div>' . "\r\n";
echo '        <header id="header" class="col-md-10">' . "\r\n";
echo '          <h1><a href="index.php">' .$libConfig->verbindungName. '</a></h1>' . "\r\n";
echo '          <h2><a href="index.php">';

if(isset($libConfig->verbindungDachverband) && $libConfig->verbindungDachverband != ''){
	echo 'im ' .$libConfig->verbindungDachverband . ' ';
}

if($libConfig->verbindungOrt != ''){
	echo 'zu ' .$libConfig->verbindungOrt;
}

echo '</a></h2>' . "\r\n";

// sign out button
echo '          <span id="signout">';

if($libAuth->isLoggedin()){
	echo '            <a href="index.php?session_destroy=1">abmelden</a>';
}

echo '</span>' . "\r\n";

echo '        </header>' . "\r\n";
echo '      </div>' . "\r\n";
echo '      <div class="row">' . "\r\n";
echo '        <div class="col-md-12">' . "\r\n";

echo (new LibMenuRenderer())->getMenuHtml($libMenuInternet, $libMenuIntranet, $libMenuAdministration, $libGlobal->pid, $libAuth->getGruppe(), $libAuth->getAemter());

echo '        </div>' . "\r\n";
echo '      </div>' . "\r\n";
echo '      <div class="row">' . "\r\n";
echo '        <div class="col-md-12">' . "\r\n";
echo '          <main id="content">' . "\r\n";