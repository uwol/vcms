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

if(!is_object($libGlobal))
	exit();


/*
* actions
*/

$formSent = false;
$formError = false;

if(isset($_POST['registrierung_name']) || isset($_POST['registrierung_telnr']) ||
	isset($_POST['registrierung_mail']) || isset($_POST['registrierung_geburtsdatum']) ||
	isset($_POST['registrierung_loginname']) ||
	isset($_POST['registrierung_pwd1']) || isset($_POST['registrierung_pwd2'])){

	$formSent = true;

	if(!isset($_POST['registrierung_name']) || $_POST['registrierung_name'] == ""){
		$libGlobal->errorTexts[] = "Bitte geben Sie einen Namen an.";
		$formError = true;
	}

	if(!isset($_POST['registrierung_telnr']) || $_POST['registrierung_telnr'] == ""){
		$libGlobal->errorTexts[] = "Bitte geben Sie eine Telefonnummer an.";
		$formError = true;
	}

	if(!isset($_POST['registrierung_emailadresse']) || $_POST['registrierung_emailadresse'] == ""){
		$libGlobal->errorTexts[] = "Bitte geben Sie eine E-Mailadresse an.";
		$formError = true;
	} elseif(isset($_POST['registrierung_emailadresse']) && !$libString->isValidEmail($_POST['registrierung_emailadresse'])){
		$libGlobal->errorTexts[] = "Die E-Mailadresse ist nicht gültig.";
		$formError = true;
	}

	if(!isset($_POST['registrierung_loginname']) || $_POST['registrierung_loginname'] == ""){
		$libGlobal->errorTexts[] = "Bitte geben Sie einen Loginnamen an.";
		$formError = true;
	}

	if(!isset($_POST['registrierung_pwd1']) || trim($_POST['registrierung_pwd1']) == ""){
		$libGlobal->errorTexts[] = "Bitte geben Sie ein Passwort ein.";
		$formError = true;
	} elseif(!$libAuth->isValidPassword($_POST['registrierung_pwd1'])){
		$libGlobal->errorTexts[] = "Das Passwort ist nicht komplex genug. ". $libAuth->getPasswordRequirements();
		$formError = true;
	} else {
		if(!isset($_POST['registrierung_pwd2']) || trim($_POST['registrierung_pwd2']) == ""){
			$libGlobal->errorTexts[] = "Bitte geben Sie das Passwort ein zweites Mal ein.";
			$formError = true;
		} else {
			if($_POST['registrierung_pwd1'] != $_POST['registrierung_pwd2']){
				$libGlobal->errorTexts[] = "Die beiden Passwörter stimmen nicht überein.";
				$formError = true;
			}
		}
	}
}


/*
* output
*/


if($formSent && !$formError){
	require_once("lib/thirdparty/class.phpmailer.php");

	$password_hash = $libAuth->encryptPassword($_POST['registrierung_pwd1']);

	$text = "Eine Person hat die folgende Registrierung für das Intranet abgeschickt. Falls die Person durch persönliche Ansprache als das Mitglied ".$libString->protectXSS($_POST['registrierung_name'])." identifiziert werden kann, können in den Datensatz von ".$libString->protectXSS($_POST['registrierung_name'])." in der Mitgliedertabelle folgende Daten eingetragen werden:

username: ".$libString->protectXSS($_POST['registrierung_loginname'])."
password_hash: ".$password_hash."
Geburtsdatum: " .$libString->protectXSS($_POST['registrierung_geburtsdatum']). "

Die Person hat zur Identifizierung die Telefonnummer ".$libString->protectXSS($_POST['registrierung_telnr'])." und die E-Mailadresse ".$libString->protectXSS($_POST['registrierung_emailadresse'])." angegeben. Vor einer Kontaktierung sind die Kontaktdaten und das Geburtsdatum auf Plausibilität zu prüfen. Im Fall einer Freischaltung lautet die Antwortmail:

------------------------

Lieber Bb ".$libString->protectXSS($_POST['registrierung_name']).",

Du wurdest mit dem Benutzernamen
".$libString->protectXSS($_POST['registrierung_loginname'])."
für das Intranet freigeschaltet.

MBuH, ";

	$mail = new PHPMailer();
	$mail->AddAddress($libConfig->emailWebmaster);
	$mail->Subject = "[".$libConfig->verbindungName."] Intranet-Registrierung";
	$mail->Body = $text;
	$mail->AddReplyTo($_POST['registrierung_emailadresse']);
	$mail->CharSet = "UTF-8";

	/*
	* SMTP mode
	*/
	if($libGenericStorage->loadValueInCurrentModule('smtpEnable') == 1){
		$mail->IsSMTP();
		$mail->SMTPAuth = true;
		$mail->Host = $libGenericStorage->loadValueInCurrentModule('smtpHost');
		$mail->Username = $libGenericStorage->loadValueInCurrentModule('smtpUsername');
		$mail->Password = $libGenericStorage->loadValueInCurrentModule('smtpPassword');
	}

	if($mail->Send()){
		echo "<h1>E-Mail verschickt</h1><p class=text>Die Daten wurden weitergeleitet. Der Webmaster wird die Registrierung bearbeiten und über den Status der Aktivierung per E-Mail informieren. Bitte achten Sie auch in Ihrem Spam-Ordner auf Nachrichten vom Webmaster.</p>";
	} else {
		echo "<h1>Fehler</h1>Die Nachricht konnte nicht verschickt werden. Bitte schreiben Sie direkt an die E-Mailadresse ". $libConfig->emailWebmaster;
	}
} else {
	echo '<h1>Registrierung</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo '<p>Mit diesem Formular kann man sich für das Intranet registrieren. Nachdem der Intranetwart den Zugang freigeschaltet hat, wird an die E-Mailadresse eine Benachrichtigung geschickt. Das Passwort wird automatisch verschlüsselt, bevor es an den Webmaster weitergeleitet wird.</p>';

	echo '<p>' .$libAuth->getPasswordRequirements(). '</p>';

	$registrierung_name = '';
	if(isset($_POST['registrierung_name'])){
		$registrierung_name = $_POST['registrierung_name'];
	}

	$registrierung_telnr = '';
	if(isset($_POST['registrierung_telnr'])){
		$registrierung_telnr = $_POST['registrierung_telnr'];
	}

	$registrierung_emailadresse = '';
	if(isset($_POST['registrierung_emailadresse'])){
		$registrierung_emailadresse = $_POST['registrierung_emailadresse'];
	}

	$registrierung_geburtsdatum = '';
	if(isset($_POST['registrierung_geburtsdatum'])){
		$registrierung_geburtsdatum = $_POST['registrierung_geburtsdatum'];
	}

	$registrierung_loginname = '';
	if(isset($_POST['registrierung_loginname'])){
		$registrierung_loginname = $_POST['registrierung_loginname'];
	}
	
	
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

	echo '<form method="post" action="' .$urlPrefix. 'index.php?pid=login_registrierung" class="form-horizontal">';
	echo '<fieldset>';

	echo '<div class="form-group">';
	echo '<label for="registrierung_name" class="col-sm-2 control-label">Vorname und Nachname</label>';
	echo '<div class="col-sm-10"><input type="text" id="registrierung_name" name="registrierung_name" value="' .$libString->protectXSS($registrierung_name). '" class="form-control" /></div>';
	echo '</div>';

	echo '<div class="form-group">';
	echo '<label for="registrierung_telnr" class="col-sm-2 control-label">Telefonnummer</label>';
	echo '<div class="col-sm-10"><input type="tel" id="registrierung_telnr" name="registrierung_telnr" value="' .$libString->protectXSS($registrierung_telnr). '" class="form-control" /></div>';
	echo '</div>';

	echo '<div class="form-group">';
	echo '<label for="registrierung_emailadresse" class="col-sm-2 control-label">E-Mail-Adresse</label>';
	echo '<div class="col-sm-10"><input type="text" id="registrierung_emailadresse" name="registrierung_emailadresse" value="' .$libString->protectXSS($registrierung_emailadresse). '" class="form-control" /></div>';
	echo '</div>';

	echo '<div class="form-group">';
	echo '<label for="registrierung_geburtsdatum" class="col-sm-2 control-label">Geburtsdatum</label>';
	echo '<div class="col-sm-10"><input type="date" id="registrierung_geburtsdatum" name="registrierung_geburtsdatum" value="' .$libString->protectXSS($registrierung_geburtsdatum). '" class="form-control" /></div>';
	echo '</div>';

	echo '<div class="form-group">';
	echo '<label for="registrierung_loginname" class="col-sm-2 control-label">Benutzername</label>';
	echo '<div class="col-sm-10"><input type="text" id="registrierung_loginname" name="registrierung_loginname" value="' .$libString->protectXSS($registrierung_loginname). '" class="form-control" /></div>';
	echo '</div>';

	echo '<div class="form-group">';
	echo '<label for="registrierung_pwd1" class="col-sm-2 control-label">Passwort</label>';
	echo '<div class="col-sm-10"><input type="password" id="registrierung_pwd1" name="registrierung_pwd1" class="form-control" /></div>';
	echo '</div>';

	echo '<div class="form-group">';
	echo '<label for="registrierung_pwd2" class="col-sm-2 control-label">Passwort-Wiederholung</label>';
	echo '<div class="col-sm-10"><input type="password" id="registrierung_pwd2" name="registrierung_pwd2" class="form-control" /></div>';
	echo '</div>';

	echo '<div class="form-group">';
	echo '<div class="col-sm-offset-2 col-sm-10">';
	echo '<button type="submit" class="btn btn-default">Abschicken</button>';
	echo '</div>';
	echo '</div>';

	echo '</fieldset>';
	echo '</form>';
}
?>