<?php
echo '<!DOCTYPE html>' . PHP_EOL;
echo '<html lang="de">' . PHP_EOL;
echo '  <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# business: http://ogp.me/ns/business#">' . PHP_EOL;
echo '    <meta charset="utf-8" />' . PHP_EOL;
echo '    <meta http-equiv="X-UA-Compatible" content="IE=edge" />' . PHP_EOL;
echo '    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">' . PHP_EOL;
echo '    <title>' .$libGlobal->getPageTitle(). '</title>' . PHP_EOL;
echo '    <meta name="description" content="' .$libConfig->seiteBeschreibung. '" />' . PHP_EOL;
echo '    <meta name="keywords" content="' .$libConfig->seiteKeywords. '" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">' . PHP_EOL;
echo '    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">' . PHP_EOL;
echo '    <link rel="stylesheet" href="vendor/hover/hover-min.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="vendor/vcms/styles/bootstrap-override.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="vendor/vcms/styles/screen.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="vendor/vcms/styles/calendar/calendar.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="vendor/vcms/styles/event/event.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="vendor/vcms/styles/image/image.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="vendor/vcms/styles/navigation/navigation.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="vendor/vcms/styles/person/person.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="vendor/vcms/styles/timeline/timeline.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="custom/styles/screen.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Libre+Franklin">' . PHP_EOL;
echo '    <link rel="canonical" href="' .$libGlobal->getPageCanonicalUrl(). '"/>' . PHP_EOL;
echo '    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>' . PHP_EOL;
echo '    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>' . PHP_EOL;
echo '    <script src="vendor/scrollreveal/scrollreveal.min.js"></script>' . PHP_EOL;
echo '    <script src="vendor/vcms/styles/gallery/modal.js"></script>' . PHP_EOL;
echo '    <script src="vendor/vcms/styles/screen.js"></script>' . PHP_EOL;

if(is_array($libGlobal->module->getHeaderStrings())){
	foreach($libGlobal->module->getHeaderStrings() as $headerString){
		echo '    ' .$headerString. PHP_EOL;
	}
}

/*
* robots
*/
if($libGlobal->page->hasAccessRestriction()){
	echo '    <meta name="robots" content="noindex, nofollow, noarchive" />' . PHP_EOL;
} else {
	echo '    <meta name="robots" content="index, follow, noarchive" />' . PHP_EOL;
}

/*
* Opengraph / Facebook meta data
*/
if($libGenericStorage->loadValue('base_core', 'facebook_appid')){
	echo '    <meta property="fb:app_id" content="' .$libGenericStorage->loadValue('base_core', 'facebook_appid'). '"/>' . PHP_EOL;
}

echo '    <meta property="og:type" content="business.business"/>' . PHP_EOL;
echo '    <meta property="og:url" content="' .$libGlobal->getPageOgUrl(). '"/>' . PHP_EOL;
echo '    <meta property="og:title" content="' .$libGlobal->getPageTitle(). '"/>' . PHP_EOL;
echo '    <meta property="og:image" content="' .$libGlobal->getPageOgImageUrl(). '"/>' . PHP_EOL;
echo '    <meta property="og:image:type" content="image/jpeg" />' . PHP_EOL;
echo '    <meta property="og:image:height" content="265"/>' . PHP_EOL;
echo '    <meta property="og:image:width" content="265"/>' . PHP_EOL;
echo '    <meta property="og:site_name" content="' .$libGlobal->getSiteUrlAuthority(). '"/>' . PHP_EOL;
echo '    <meta property="og:description" content="' .$libConfig->seiteBeschreibung. '"/>' . PHP_EOL;
echo '    <meta property="business:contact_data:street_address" content="' .$libConfig->verbindungStrasse. '"/>' . PHP_EOL;
echo '    <meta property="business:contact_data:locality" content="' .$libConfig->verbindungOrt. '"/>' . PHP_EOL;
echo '    <meta property="business:contact_data:postal_code" content="' .$libConfig->verbindungPlz. '"/>' . PHP_EOL;
echo '    <meta property="business:contact_data:country_name" content="' .$libConfig->verbindungLand. '"/>' . PHP_EOL;

$analyticsFilePath = $libFilesystem->getAbsolutePath('custom/analyticstracking.php');

if(is_file($analyticsFilePath) && !$libAuth->isLoggedIn()){
	include_once($analyticsFilePath);
}

echo '  </head>' . PHP_EOL;
echo '  <body>' . PHP_EOL;

$libMenuRenderer = new \vcms\LibMenuRenderer();
$libMenuRenderer->printNavbar($libMenuInternet, $libMenuIntranet, $libMenuAdministration, $libGlobal->pid, $libAuth->getGruppe(), $libAuth->getAemter());

if($libGlobal->page->isContainerEnabled()){
	echo '    <main id="content">' . PHP_EOL;
	echo '      <div id="container" class="container">' . PHP_EOL;
}
