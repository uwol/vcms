<?php
/*
This file is part of VCMS.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();

if(!$libGenericStorage->attributeExistsInCurrentModule("userNameICalendar") || !$libGenericStorage->attributeExistsInCurrentModule("passwordICalendar")){
	$libGenericStorage->saveValueInCurrentModule("userNameICalendar", $libString->randomAlphaNumericString(40));
	$libGenericStorage->saveValueInCurrentModule("passwordICalendar", $libString->randomAlphaNumericString(40));
}

echo '<h1>Intranet</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<div class="row">';
echo '<aside class="col-md-3">';

require_once("elements/events.php");

if($libModuleHandler->moduleIsAvailable("mod_intranet_chargierkalender")){
	require_once("elements/chargierkalender.php");
}

require_once("elements/birthdays.php");
require_once("elements/wifi.php");

echo '</aside>';
echo '<section class="col-md-9">';

require_once("elements/system.php");

if($libModuleHandler->moduleIsAvailable("mod_intranet_news")){
	require_once("elements/news.php");
}

if($libModuleHandler->moduleIsAvailable("mod_intranet_reservierungen")){
	require_once("elements/reservations.php");
}

echo '</section>';
echo '</div>';