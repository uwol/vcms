<?php
/*
This file is part of VCMS.

VCMS is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

VCMS is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with VCMS. If not, see <http://www.gnu.org/licenses/>.
*/

if(!is_object($libGlobal))
	exit();


if(isset($_POST['email']) && $_POST['email'] != '' &&
		isset($_POST['geburtsdatum']) && $_POST['geburtsdatum'] != ''){

	if(!$libString->isValidEmail($_POST['email'])){
		$libGlobal->errorTexts[] = 'Die angegebene Adresse ist keine E-Mail-Adresse.';
	} else {
		$stmt = $libDb->prepare("SELECT id, email, datum_geburtstag FROM base_person WHERE email=:email AND gruppe != 'T' AND gruppe != 'X' AND gruppe != 'V' AND gruppe != '' LIMIT 0,1");
		$stmt->bindValue(':email', strtolower($_POST['email']));
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if(!is_array($row) || $row['id'] == '' || !is_numeric($row['id'])){
			//burn CPU-cycles
			$libAuth->encryptPassword('dummyPassword');
		} elseif($row['datum_geburtstag'] != '' && $row['datum_geburtstag'] != '0000-00-00' &&
				$row['datum_geburtstag'] != $libTime->assureMysqlDate($_POST['geburtsdatum'])){
			//burn CPU-cycles
			$libAuth->encryptPassword('dummyPassword');
		} elseif($row['id'] != '' && is_numeric($row['id']) &&
				($row['datum_geburtstag'] == '' || $row['datum_geburtstag'] == '0000-00-00' ||
				$row['datum_geburtstag'] == $libTime->assureMysqlDate($_POST['geburtsdatum']))){

			//generate new password
			$newPassword = $libString->randomAlphaNumericString(20);

			while(!$libAuth->isValidPassword($newPassword)){
				$newPassword = $libString->randomAlphaNumericString(20);
			}

			//save new password
			$libAuth->savePassword($row['id'], $newPassword, true);

			//send reset password
			$text = 'Auf ' .$libGlobal->getSiteUrl(). ' wurde das Passwort für den Benutzer mit der E-Mail-Adresse ' .$row['email']. ' zurückgesetzt. Das neue Passwort lautet ' .$newPassword. ' und kann im Intranet auf der Seite "Mein Profil" geändert werden.';

			$mail = new PHPMailer();
			$libMail->configurePHPMailer($mail);

			$mail->AddAddress($row['email']);
			$mail->Subject = '[' .$libConfig->verbindungName. '] Passwortänderung';
			$mail->Body = $text;
			$mail->AddReplyTo($libConfig->emailWebmaster);

			$mail->Send();
		}

		$libGlobal->notificationTexts[] =  'Falls das Geburtsdatum korrekt und die E-Mail-Adresse in Deinem Nutzerkonto eingetragen ist, wurde eine E-Mail mit einem neuen Passwort an die E-Mail-Adresse verschickt.';
	}
}

echo '<h1>Neues Passwort setzen</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<p>Auf dieser Seite kann ein neues Passwort vergeben werden, falls das alte vergessen wurde. Bitte gib Dein Geburtsdatum und die E-Mail-Adresse an, die in Deinem Intranetkonto angegeben ist. An diese E-Mail-Adresse wird das neue Passwort verschickt.</p>';

echo '<form action="index.php?pid=password" method="post" class="form-horizontal">';
echo '<fieldset>';

$libForm->printTextInput('email', 'E-Mail-Adresse', '', 'email');
$libForm->printTextInput('geburtsdatum', 'Geburtsdatum', '', 'date');
$libForm->printSubmitButton('<i class="fa fa-pencil-square-o" aria-hidden="true"></i> Neues Passwort anfordern', array('btn-danger'));

echo '</fieldset>';
echo '</form>';
