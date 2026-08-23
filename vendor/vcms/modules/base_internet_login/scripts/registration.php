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

if (!is_object($libGlobal)) {
    exit();
}


/*
* actions
*/

$formSent = false;
$formError = false;

if (isset($_POST['registrationName']) || isset($_POST['registrationPhone']) ||
    isset($_POST['registrationEmail']) || isset($_POST['registrationBirthdate']) ||
    isset($_POST['registrationPassword1']) || isset($_POST['registrationPassword2'])) {

    $formSent = true;

    if (!isset($_POST['registrationName']) || $_POST['registrationName'] == '') {
        $libGlobal->errorTexts[] = 'Bitte geben Sie einen Namen an.';
        $formError = true;
    }

    if (!isset($_POST['registrationPhone']) || $_POST['registrationPhone'] == '') {
        $libGlobal->errorTexts[] = 'Bitte geben Sie eine Telefonnummer an.';
        $formError = true;
    }

    if (!isset($_POST['registrationEmail']) || $_POST['registrationEmail'] == '') {
        $libGlobal->errorTexts[] = 'Bitte geben Sie eine E-Mail-Adresse an.';
        $formError = true;
    } elseif (isset($_POST['registrationEmail']) && !$libString->isValidEmail($_POST['registrationEmail'])) {
        $libGlobal->errorTexts[] = 'Die E-Mail-Adresse ist nicht gültig.';
        $formError = true;
    }

    if (!isset($_POST['registrationPassword1']) || trim($_POST['registrationPassword1']) == '') {
        $libGlobal->errorTexts[] = 'Bitte geben Sie ein Passwort ein.';
        $formError = true;
    } elseif (!$libAuth->isValidPassword($_POST['registrationPassword1'])) {
        $libGlobal->errorTexts[] = 'Das Passwort ist nicht komplex genug. ' .$libAuth->getPasswordRequirements();
        $formError = true;
    } else {
        if (!isset($_POST['registrationPassword2']) || trim($_POST['registrationPassword2']) == '') {
            $libGlobal->errorTexts[] = 'Bitte geben Sie das Passwort ein zweites Mal ein.';
            $formError = true;
        } else {
            if ($_POST['registrationPassword1'] != $_POST['registrationPassword2']) {
                $libGlobal->errorTexts[] = 'Die beiden Passwörter stimmen nicht überein.';
                $formError = true;
            }
        }
    }
}


/*
* output
*/


if ($formSent && !$formError) {
    $password_hash = $libAuth->encryptPassword($_POST['registrationPassword1']);

    // The mail is plain text, therefore no HTML escaping
    $text = 'Auf ' .$libGlobal->getSiteUrl(). ' wurde folgende Registrierungsanfrage für das Intranet gestellt: ' . PHP_EOL;
    $text .= PHP_EOL;
    $text .= 'Name: ' .$_POST['registrationName'] . PHP_EOL;
    $text .= 'E-Mail-Adresse: ' .strtolower($_POST['registrationEmail']) . PHP_EOL;
    $text .= 'Telefonnummer: ' .$_POST['registrationPhone'] . PHP_EOL;
    $text .= 'Geburtsdatum: ' .$_POST['registrationBirthdate'] . PHP_EOL;
    $text .= 'Passwort-Hash: ' .$password_hash. PHP_EOL;
    $text .= PHP_EOL;
    $text .= 'Die Freischaltung für das Intranet erfolgt, indem der Internetwart die Daten nach einer Plausibilitätsprüfung im Personenprofil speichert.' . PHP_EOL;
    $text .= 'Im Fall einer Freischaltung lautet die Antwortmail:' . PHP_EOL;
    $text .= PHP_EOL;
    $text .= PHP_EOL;
    $text .= 'Lieber Bb ' .$_POST['registrationName']. ',' . PHP_EOL;
    $text .= PHP_EOL;
    $text .= 'Du wurdest mit der E-Mail-Adresse ' .$_POST['registrationEmail']. ' für das Intranet freigeschaltet.' . PHP_EOL;
    $text .= PHP_EOL;
    $text .= 'MBuH,';

    $mail = $libMail->createPHPMailer();

    $mail->addAddress($libConfig->emailWebmaster);
    $mail->Subject = '[' .$libConfig->verbindungName. '] Intranet-Registrierung';
    $mail->Body = $text;
    $mail->addReplyTo($_POST['registrationEmail']);

    $mailsent = false;

    if ($mail->send()) {
        $mailsent = true;
    } else {
        $libGlobal->errorTexts[] = $mail->ErrorInfo;
    }

    if ($mailsent) {
        echo '<h1>E-Mail verschickt</h1>';

        echo $libString->getErrorBoxText();
        echo $libString->getNotificationBoxText();

        echo '<p class="mb-4">Die Daten wurden weitergeleitet. Der Internetwart wird die Registrierung bearbeiten und über den Status der Aktivierung per E-Mail informieren. Bitte achten Sie auch in Ihrem Spam-Ordner auf Nachrichten vom Internetwart.</p>';
    } else {
        echo '<h1>Fehler</h1>';

        echo $libString->getErrorBoxText();
        echo $libString->getNotificationBoxText();

        echo '<p class="mb-4">Die Nachricht konnte nicht verschickt werden. Bitte schreiben Sie direkt an die E-Mail-Adresse ' .$libConfig->emailWebmaster. '</p>';
    }
} else {
    echo '<h1>Registrierung</h1>';

    echo $libString->getErrorBoxText();
    echo $libString->getNotificationBoxText();

    echo '<div class="mb-4">';
    echo '<p class="mb-4">Mit diesem Formular kann man sich für das Intranet registrieren. Nachdem der Intranetwart den Zugang freigeschaltet hat, wird an die E-Mail-Adresse eine Benachrichtigung geschickt. Das Passwort wird automatisch verschlüsselt, bevor es an den Internetwart weitergeleitet wird.</p>';
    echo '<p class="mb-4">' .$libAuth->getPasswordRequirements(). '</p>';
    echo '</div>';

    $registrationName = '';
    if (isset($_POST['registrationName'])) {
        $registrationName = $_POST['registrationName'];
    }

    $registrationPhone = '';
    if (isset($_POST['registrationPhone'])) {
        $registrationPhone = $_POST['registrationPhone'];
    }

    $registrationEmail = '';
    if (isset($_POST['registrationEmail'])) {
        $registrationEmail = $_POST['registrationEmail'];
    }

    $registrationBirthdate = '';
    if (isset($_POST['registrationBirthdate'])) {
        $registrationBirthdate = $_POST['registrationBirthdate'];
    }

    $urlPrefix = '';

    if ($libGlobal->getSiteUrlAuthority() != '') {
        $sslProxyUrl = $libGenericStorage->loadValueInCurrentModule('ssl_proxy_url');

        if ($sslProxyUrl != '') {
            $urlPrefix = 'https://' .$sslProxyUrl. '/' .$libGlobal->getSiteUrlAuthority(). '/';
        }
    }

    echo '<div class="panel panel-default">';
    echo '<div class="panel-body">';
    echo '<form method="post" action="' .$libString->protectXSS($urlPrefix). 'index.php?pid=registration" class="form-horizontal">';
    echo '<fieldset>';

    $libForm->printTextInput('registrationName', 'Vorname und Nachname', $registrationName, 'text', false, true);
    $libForm->printTextInput('registrationPhone', 'Telefonnummer', $registrationPhone, 'tel', false, true);
    $libForm->printTextInput('registrationEmail', 'E-Mail-Adresse', $registrationEmail, 'email', false, true);
    $libForm->printDateInput('registrationBirthdate', 'Geburtsdatum', $registrationBirthdate, false, true, [], '', date('Y-m-d'));
    $libForm->printTextInput('registrationPassword1', 'Passwort', '', 'password', false, true);
    $libForm->printTextInput('registrationPassword2', 'Passwort-Wiederholung', '', 'password', false, true);
    $libForm->printSubmitButton('<i class="fa fa-pencil-square-o" aria-hidden="true"></i> Abschicken');

    echo '</fieldset>';
    echo '</form>';
    echo '</div>';
    echo '</div>';
}
