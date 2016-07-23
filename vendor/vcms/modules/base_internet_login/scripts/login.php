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

/*
* configuration
*/

if(!$libGenericStorage->attributeExistsInCurrentModule('sslProxyUrl')){
	$libGenericStorage->saveValueInCurrentModule('sslProxyUrl', '');
}


echo '<h1>Intranet-Login</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<p>Bitte zum Anmelden die E-Mail-Adresse und das Passwort eingeben.</p>';

$urlPrefix = '';

if($libConfig->sitePath != ""){
	$sslProxyUrl = $libGenericStorage->loadValueInCurrentModule('sslProxyUrl');

	if($sslProxyUrl != ''){
		$urlPrefix = 'https://' . $sslProxyUrl . '/' . $libConfig->sitePath . '/';
	} else {
		$urlPrefix = 'https://' . $libConfig->sitePath . '/';
	}
}


echo '<form action="' .$urlPrefix. 'index.php?pid=intranet_home" method="post" class="form-horizontal">';
echo '<fieldset>';

$libForm->printTextInput('intranet_login_username', 'E-Mail-Adresse', '', 'email');
$libForm->printTextInput('intranet_login_password', 'Passwort', '', 'password');
$libForm->printSubmitButton('<i class="fa fa-sign-in" aria-hidden="true"></i> Anmelden');

echo '</fieldset>';
echo '</form>';

echo '<h2>Registrierung</h2>';
echo '<p>Um in das Intranet zu gelangen, wird ein Zugang benötigt, der von Mitgliedern auf der <a href="index.php?pid=login_registrierung">Registrierungsseite</a> angefordert werden kann.</p>';

echo '<h2>Passwort vergessen?</h2>';
echo '<p>Falls Du bereits einen Intranetzugang hast, aber das Passwort vergessen hast, kannst Du Dir <a href="index.php?pid=login_resetpassword">ein neues Passwort</a> per Email zuschicken lassen.</p>';