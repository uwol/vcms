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
	isset($_POST['registrierung_pwd1']) || isset($_POST['registrierung_pwd2'])){

	$formSent = true;

	if(!isset($_POST['registrierung_name']) || $_POST['registrierung_name'] == ''){
		$libGlobal->errorTexts[] = 'Bitte geben Sie einen Namen an.';
		$formError = true;
	}

	if(!isset($_POST['registrierung_telnr']) || $_POST['registrierung_telnr'] == ''){
		$libGlobal->errorTexts[] = 'Bitte geben Sie eine Telefonnummer an.';
		$formError = true;
	}

	if(!isset($_POST['registrierung_emailadresse']) || $_POST['registrierung_emailadresse'] == ''){
		$libGlobal->errorTexts[] = 'Bitte geben Sie eine E-Mail-Adresse an.';
		$formError = true;
	} elseif(isset($_POST['registrierung_emailadresse']) && !$libString->isValidEmail($_POST['registrierung_emailadresse'])){
		$libGlobal->errorTexts[] = 'Die E-Mail-Adresse ist nicht gültig.';
		$formError = true;
	}

	if(!isset($_POST['registrierung_pwd1']) || trim($_POST['registrierung_pwd1']) == ''){
		$libGlobal->errorTexts[] = 'Bitte geben Sie ein Passwort ein.';
		$formError = true;
	} elseif(!$libAuth->isValidPassword($_POST['registrierung_pwd1'])){
		$libGlobal->errorTexts[] = 'Das Passwort ist nicht komplex genug. ' .$libAuth->getPasswordRequirements();
		$formError = true;
	} else {
		if(!isset($_POST['registrierung_pwd2']) || trim($_POST['registrierung_pwd2']) == ''){
			$libGlobal->errorTexts[] = 'Bitte geben Sie das Passwort ein zweites Mal ein.';
			$formError = true;
		} else {
			if($_POST['registrierung_pwd1'] != $_POST['registrierung_pwd2']){
				$libGlobal->errorTexts[] = 'Die beiden Passwörter stimmen nicht überein.';
				$formError = true;
			}
		}
	}
}


/*
* output
*/


if($formSent && !$formError){
	require_once('lib/thirdparty/class.phpmailer.php');

	$password_hash = $libAuth->encryptPassword($_POST['registrierung_pwd1']);

	$text = 'Auf ' .$libConfig->sitePath. ' wurde folgende Registrierungsanfrage für das Intranet gestellt: ' . PHP_EOL;
	$text .= PHP_EOL;
	$text .= 'Name: ' .$libString->protectXSS($_POST['registrierung_name']) . PHP_EOL;
	$text .= 'E-Mail-Adresse: ' .$libString->protectXSS(strtolower($_POST['registrierung_emailadresse'])) . PHP_EOL;
	$text .= 'Telefonnummer: ' .$libString->protectXSS($_POST['registrierung_telnr']) . PHP_EOL;
	$text .= 'Geburtsdatum: ' .$libString->protectXSS($_POST['registrierung_geburtsdatum']) . PHP_EOL;
	$text .= 'Passwort-Hash: ' .$password_hash. PHP_EOL;
	$text .= PHP_EOL;
	$text .= 'Die Freischaltung für das Intranet erfolgt, indem der Internetwart die Daten nach einer Plausibilitätsprüfung im Personenprofil speichert.' . PHP_EOL;
	$text .= 'Im Fall einer Freischaltung lautet die Antwortmail:' . PHP_EOL;
	$text .= PHP_EOL;
	$text .= PHP_EOL;
	$text .= 'Lieber Bb ' .$libString->protectXSS($_POST['registrierung_name']). ',' . PHP_EOL;
	$text .= PHP_EOL;
	$text .= 'Du wurdest mit der E-Mail-Adresse ' .$libString->protectXSS($_POST['registrierung_emailadresse']). ' für das Intranet freigeschaltet.' . PHP_EOL;
	$text .= PHP_EOL;
	$text .= 'MBuH,';

	$mail = new PHPMailer();
	$mail->From = $libConfig->emailWebmaster;
	$mail->AddAddress($libConfig->emailWebmaster);
	$mail->Subject = '[' .$libConfig->verbindungName. '] Intranet-Registrierung';
	$mail->Body = $text;
	$mail->AddReplyTo($_POST['registrierung_emailadresse']);
	$mail->CharSet = 'UTF-8';

	/*
	* SMTP mode
	*/
	if($libGenericStorage->loadValue('base_core', 'smtpEnable') == 1){
		$mail->IsSMTP();
		$mail->SMTPAuth = true;
		$mail->Host = $libGenericStorage->loadValue('base_core', 'smtpHost');
		$mail->Username = $libGenericStorage->loadValue('base_core', 'smtpUsername');
		$mail->Password = $libGenericStorage->loadValue('base_core', 'smtpPassword');
	}

	if($mail->Send()){
		echo '<h1>E-Mail verschickt</h1>';
		echo '<p>Die Daten wurden weitergeleitet. Der Internetwart wird die Registrierung bearbeiten und über den Status der Aktivierung per E-Mail informieren. Bitte achten Sie auch in Ihrem Spam-Ordner auf Nachrichten vom Internetwart.</p>';
	} else {
		echo '<h1>Fehler</h1>';
		echo '<p>Die Nachricht konnte nicht verschickt werden. Bitte schreiben Sie direkt an die E-Mail-Adresse ' .$libConfig->emailWebmaster. '</p>';
	}
} else {
	echo '<h1>Registrierung</h1>';

	echo $libString->getErrorBoxText();
	echo $libString->getNotificationBoxText();

	echo '<p>Mit diesem Formular kann man sich für das Intranet registrieren. Nachdem der Intranetwart den Zugang freigeschaltet hat, wird an die E-Mail-Adresse eine Benachrichtigung geschickt. Das Passwort wird automatisch verschlüsselt, bevor es an den Internetwart weitergeleitet wird.</p>';
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

	$urlPrefix = '';

	if($libConfig->sitePath != ''){
		if($libGenericStorage->loadValueInCurrentModule('useHttps') == '1'){
			$sslProxyUrl = $libGenericStorage->loadValueInCurrentModule('sslProxyUrl');

			if($sslProxyUrl != ''){
				$urlPrefix = 'https://' .$sslProxyUrl. '/' .$libConfig->sitePath. '/';
			} else {
				$urlPrefix = 'https://' .$libConfig->sitePath. '/';
			}
		}
	}

	echo '<form method="post" action="' .$urlPrefix. 'index.php?pid=login_registrierung" class="form-horizontal">';
	echo '<fieldset>';

	$libForm->printTextInput('registrierung_name', 'Vorname und Nachname', $libString->protectXSS($registrierung_name));
	$libForm->printTextInput('registrierung_telnr', 'Telefonnummer', $libString->protectXSS($registrierung_telnr), 'tel');
	$libForm->printTextInput('registrierung_emailadresse', 'E-Mail-Adresse', $libString->protectXSS($registrierung_emailadresse), 'email');
	$libForm->printTextInput('registrierung_geburtsdatum', 'Geburtsdatum', $libString->protectXSS($registrierung_geburtsdatum), 'date');
	$libForm->printTextInput('registrierung_pwd1', 'Passwort', '', 'password');
	$libForm->printTextInput('registrierung_pwd2', 'Passwort-Wiederholung', '', 'password');
	$libForm->printSubmitButton('Abschicken');

	echo '</fieldset>';
	echo '</form>';
}
?>