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

if(!$libGenericStorage->attributeExistsInCurrentModule('useHttps')){
	$libGenericStorage->saveValueInCurrentModule('useHttps', 0);
}

if(!$libGenericStorage->attributeExistsInCurrentModule('sslProxyUrl')){
	$libGenericStorage->saveValueInCurrentModule('sslProxyUrl', '');
}

if(!$libGenericStorage->attributeExistsInCurrentModule('smtpEnable')){
	$libGenericStorage->saveValueInCurrentModule('smtpEnable', 0);
}

if(!$libGenericStorage->attributeExistsInCurrentModule('smtpHost')){
	$libGenericStorage->saveValueInCurrentModule('smtpHost', '');
}

if(!$libGenericStorage->attributeExistsInCurrentModule('smtpUsername')){
	$libGenericStorage->saveValueInCurrentModule('smtpUsername', '');
}

if(!$libGenericStorage->attributeExistsInCurrentModule('smtpPassword')){
	$libGenericStorage->saveValueInCurrentModule('smtpPassword', '');
}


echo '<h1>Intranet-Login</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<p>Bitte zum Anmelden den Benutzernamen und das Passwort eingeben.</p>';


$urlPrefix = '';

if($libConfig->sitePath != ""){
	if($libGenericStorage->loadValueInCurrentModule('useHttps') == '1'){
		$sslProxyUrl = $libGenericStorage->loadValueInCurrentModule('sslProxyUrl');

		if($sslProxyUrl != ''){
			$urlPrefix = 'https://' . $sslProxyUrl . '/' . $libConfig->sitePath . '/';
		} else {
			$urlPrefix = 'https://' . $libConfig->sitePath . '/';
		}
	}
}


echo '<form action="' .$urlPrefix. 'index.php?pid=intranet_home" method="post" class="form-horizontal">';
echo '<fieldset>';

echo '<div class="form-group">';
echo '<label for="intranet_login_username" class="col-sm-2 control-label">Benutzername</label>';
echo '<div class="col-sm-10"><input type="text" id="intranet_login_username" name="intranet_login_username" class="form-control" /></div>';
echo '</div>';

echo '<div class="form-group">';
echo '<label for="intranet_login_password" class="col-sm-2 control-label">Passwort</label>';
echo '<div class="col-sm-10"><input type="password" name="intranet_login_password" class="form-control" /></div>';
echo '</div>';

echo '<div class="form-group">';
echo '<div class="col-sm-offset-2 col-sm-10">';
echo '<button type="submit" class="btn btn-default">Anmelden</button>';
echo '</div>';
echo '</div>';

echo '</fieldset>';
echo '</form>';


echo '<h2>Registrierung</h2>';

echo '<p>Um in das Intranet zu gelangen, wird ein Zugang benötigt, der von Mitgliedern auf der <a href="index.php?pid=login_registrierung">Registrierungsseite</a> angefordert werden kann.</p>';


echo '<h2>Passwort vergessen?</h2>';

echo '<p>Falls Du bereits einen Intranetzugang hast, aber das Passwort vergessen hast, kannst Du Dir <a href="index.php?pid=login_resetpassword">ein neues Passwort</a> per Email zuschicken lassen.</p>';