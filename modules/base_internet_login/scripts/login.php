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
?>
<h1>Intranet-Login</h1>
<?php
echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();
?>

<p>Bitte zum Anmelden den Benutzernamen und das Passwort eingeben.</p>

<form method="post" action="<?php
if($libConfig->sitePath != ""){
	if($libGenericStorage->loadValueInCurrentModule('useHttps') == '1'){
		$sslProxyUrl = $libGenericStorage->loadValueInCurrentModule('sslProxyUrl');

		if($sslProxyUrl != '')
			echo 'https://' . $sslProxyUrl . '/' . $libConfig->sitePath . '/';
		else
			echo 'https://' . $libConfig->sitePath . '/';
	}
}?>index.php?pid=intranet_home">
	<fieldset>
		<input type="text" name="intranet_login_username" style="width:13em" /> Benutzername<br />
		<input type="password" name="intranet_login_password" style="width:13em" /> Passwort<br />
		<div style="text-align:center"><input type="submit" value="Login" /></div>
	</fieldset>
</form>

<h2>Registrierung</h2>

<p>Um in das Intranet zu gelangen, wird ein Zugang benötigt, der von Mitgliedern auf der <a href="index.php?pid=login_registrierung">Registrierungsseite</a> angefordert werden kann.</p>

<h2>Passwort vergessen?</h2>

<p>Falls Du bereits einen Intranetzugang hast, aber das Passwort vergessen hast, kannst Du Dir <a href="index.php?pid=login_resetpassword">ein neues Passwort</a> per Email zuschicken lassen.</p>