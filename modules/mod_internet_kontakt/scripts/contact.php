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
* action
*/
if (!$libGenericStorage->attributeExistsInCurrentModule('show_senior')) {
    $libGenericStorage->saveValueInCurrentModule('show_senior', 0);
}

if (!$libGenericStorage->attributeExistsInCurrentModule('show_jubelsenior')) {
    $libGenericStorage->saveValueInCurrentModule('show_jubelsenior', 0);
}

if (!$libGenericStorage->attributeExistsInCurrentModule('show_consenior')) {
    $libGenericStorage->saveValueInCurrentModule('show_consenior', 0);
}

if (!$libGenericStorage->attributeExistsInCurrentModule('show_fuchsmajor')) {
    $libGenericStorage->saveValueInCurrentModule('show_fuchsmajor', 0);
}

if (!$libGenericStorage->attributeExistsInCurrentModule('show_fuchsmajor2')) {
    $libGenericStorage->saveValueInCurrentModule('show_fuchsmajor2', 0);
}

if (!$libGenericStorage->attributeExistsInCurrentModule('show_scriptor')) {
    $libGenericStorage->saveValueInCurrentModule('show_scriptor', 0);
}

if (!$libGenericStorage->attributeExistsInCurrentModule('show_quaestor')) {
    $libGenericStorage->saveValueInCurrentModule('show_quaestor', 0);
}

if (!$libGenericStorage->attributeExistsInCurrentModule('show_form')) {
    $libGenericStorage->saveValueInCurrentModule('show_form', 1);
}

if (!$libGenericStorage->attributeExistsInCurrentModule('show_haftungshinweis')) {
    $libGenericStorage->saveValueInCurrentModule('show_haftungshinweis', 0);
}


$mailsent = false;

if ($libGenericStorage->loadValueInCurrentModule('show_form')) {
    if (isset($_POST['name']) && isset($_POST['phone']) && isset($_POST['emailaddress']) && isset($_POST['message'])) {
        $error_emailaddress = false;
        $error_message = false;

        if (!$libString->isValidEmail($_POST['emailaddress'])) {
            $error_emailaddress = true;
            $libGlobal->errorTexts[] = 'Die angegebene E-Mail-Adresse ist nicht korrekt.';
        }

        if (trim($_POST['message']) == '') {
            $error_message = true;
            $libGlobal->errorTexts[] = 'Es wurde keine Nachricht eingegeben.';
        }

        if (!$error_emailaddress && !$error_message) {
            $message = $_POST['name']. ' mit der Telefonnummer ' .$_POST['phone']. ' und der E-Mail-Adresse ' .$_POST['emailaddress']. ' hat über das Kontaktformular folgende Nachricht geschrieben:' . PHP_EOL;
            $message .= PHP_EOL;
            $message .= $_POST['message'];

            $mail = $libMail->createPHPMailer();

            $mail->addAddress($libConfig->emailInfo);
            // The mail is plain text, therefore no HTML escaping
            $mail->Subject = 'E-Mail von ' .$_POST['name']. ' über ' . $libGlobal->getSiteUrl();
            $mail->Body = $message;
            $mail->addReplyTo($_POST['emailaddress']);

            if ($mail->send()) {
                $mailsent = true;
                $libGlobal->notificationTexts[] = 'Vielen Dank, Ihre Nachricht wurde weitergeleitet.';
            } else {
                $libGlobal->errorTexts[] = $mail->ErrorInfo;
            }
        }
    }
}

/*
* output
*/
$associationSchema = $libAssociation->getAssociationSchema();

echo '<script type="application/ld+json">';
echo str_replace(['<', '>', '&'], ['\u003c', '\u003e', '\u0026'], json_encode($associationSchema));
echo '</script>';


echo '<h1>Kontakt und Impressum</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<div class="row">';
echo '<div class="col-sm-6">';
echo '<section class="address-box mb-5">';

echo '<p class="mb-4">' .$libConfig->verbindungName. '</p>';
echo '<address class="contact-address mb-4">';

if ($libConfig->verbindungZusatz != '') {
    echo '<span>' .$libConfig->verbindungZusatz. '</span><br />';
}

echo '<span>' .$libConfig->verbindungStrasse. '</span><br />';
echo '<span>' .$libConfig->verbindungPlz. '</span> <span>' .$libConfig->verbindungOrt. '</span><br />';
echo '<span>' .$libConfig->verbindungLand. '</span><br />';
echo '<i class="fa fa-phone fa-fw" aria-hidden="true"></i> <span>' .$libConfig->verbindungTelefon. '</span><br />';
echo '<i class="fa fa-envelope-o fa-fw" aria-hidden="true"></i> <span>' .$libConfig->emailInfo. '</span><br />';
echo '</address>';

echo '<p class="contact-vorstand mb-4">';

$board = $libAssociation->getContactableActiveBoardIds();

if ($libGenericStorage->loadValueInCurrentModule('show_senior') && $board['senior']) {
    echo 'Senior: ' .$libString->protectXSS($libPerson->getNameString($board['senior'], 0)). '<br />';
}

if ($libGenericStorage->loadValueInCurrentModule('show_jubelsenior') && $board['jubelsenior']) {
    echo 'Jubelsenior: ' .$libString->protectXSS($libPerson->getNameString($board['jubelsenior'], 0)). '<br />';
}

if ($libGenericStorage->loadValueInCurrentModule('show_consenior') && $board['consenior']) {
    echo 'Consenior: ' .$libString->protectXSS($libPerson->getNameString($board['consenior'], 0)). '<br />';
}

if ($libGenericStorage->loadValueInCurrentModule('show_fuchsmajor') && $board['fuchsmajor']) {
    echo 'Fuchsmajor: ' .$libString->protectXSS($libPerson->getNameString($board['fuchsmajor'], 0)). '<br />';
}

if ($libGenericStorage->loadValueInCurrentModule('show_fuchsmajor2') && $board['fuchsmajor2']) {
    echo 'Fuchsmajor 2: ' .$libString->protectXSS($libPerson->getNameString($board['fuchsmajor2'], 0)). '<br />';
}

if ($libGenericStorage->loadValueInCurrentModule('show_scriptor') && $board['scriptor']) {
    echo 'Scriptor: ' .$libString->protectXSS($libPerson->getNameString($board['scriptor'], 0)). '<br />';
}

if ($libGenericStorage->loadValueInCurrentModule('show_quaestor') && $board['quaestor']) {
    echo 'Quaestor: ' .$libString->protectXSS($libPerson->getNameString($board['quaestor'], 0)). '<br />';
}

echo '</p>';
echo '</section>';
echo '</div>';

echo '<aside class="col-sm-6">';
echo '<div class="card reveal mb-5">';
echo '<div class="card card-img">';
echo '<img src="' .$libModuleHandler->getModuleDirectory(). '/custom/img/haus.jpg" alt="" class="img-fluid d-block mx-auto reveal" />';
echo '</div>';
echo '</div>';
echo '</aside>';

echo '</div>';

if ($libGenericStorage->loadValueInCurrentModule('show_form')) {
    echo '<h2>Kontakt aufnehmen</h2>';

    echo '<div class="row">';
    echo '<div class="col-sm-12">';
    echo '<section class="contact-form-box mb-5">';

    if ($mailsent) {
        echo '<p class="mb-4">Vielen Dank, Ihre Nachricht wurde weitergeleitet.</p>';
    } else {
        $name = '';

        if (isset($_POST['name']) && $_POST['name'] != '') {
            $name = $_POST['name'];
        }

        $email = '';

        if (isset($_POST['emailaddress']) && $_POST['emailaddress'] != '') {
            $email = $_POST['emailaddress'];
        }

        $phone = '';

        if (isset($_POST['phone']) && $_POST['phone'] != '') {
            $phone = $_POST['phone'];
        }

        $message = '';

        if (isset($_POST['message']) && $_POST['message'] != '') {
            $message = $_POST['message'];
        }

        echo '<div class="card">';
        echo '<div class="card-body">';
        echo '<form action="index.php?pid=kontakt" method="post">';
        echo '<fieldset>';

        $libForm->printTextInput('name', 'Name', $name, 'text', false, true);
        $libForm->printTextInput('emailaddress', 'E-Mail-Adresse', $email, 'email', false, true);
        $libForm->printTextInput('phone', 'Telefonnummer', $phone, 'tel', false, true);
        $libForm->printTextarea('message', 'Nachricht', $message, false, true);
        $libForm->printSubmitButton('<i class="fa fa-envelope-o" aria-hidden="true"></i> Abschicken');

        echo '</fieldset>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
    }

    echo '</section>';
    echo '</div>';
    echo '</div>';
}

if ($libGenericStorage->loadValueInCurrentModule('show_haftungshinweis')) {
    echo '<h2>Haftungshinweis</h2>';

    echo '<div class="row">';
    echo '<div class="col-md-12">';
    echo '<section class="disclaimer-box">';
    echo '<p class="mb-4">Haftungshinweis: Trotz sorgfältiger inhaltlicher Kontrolle übernehmen wir keine Haftung für die Inhalte externer Links. Für den Inhalt der verlinkten Seiten sind ausschließlich deren Betreiber verantwortlich.</p>';
    echo '</section>';
    echo '</div>';
    echo '</div>';
}

echo '<h2>VCMS</h2>';
echo '<section class="cms-box">';
echo '<p class="mb-4">Content Management System: <a href="http://www.' .$libGlobal->vcmsHostname. '">VCMS</a> (GNU General Public License)</p>';
echo '</section>';
