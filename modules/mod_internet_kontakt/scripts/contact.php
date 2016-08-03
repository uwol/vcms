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


/*
* action
*/
if(!$libGenericStorage->attributeExistsInCurrentModule('showSenior')){
	$libGenericStorage->saveValueInCurrentModule('showSenior', 0);
}

if(!$libGenericStorage->attributeExistsInCurrentModule('showJubelsenior')){
	$libGenericStorage->saveValueInCurrentModule('showJubelsenior', 0);
}

if(!$libGenericStorage->attributeExistsInCurrentModule('showConsenior')){
	$libGenericStorage->saveValueInCurrentModule('showConsenior', 0);
}

if(!$libGenericStorage->attributeExistsInCurrentModule('showFuchsmajor')){
	$libGenericStorage->saveValueInCurrentModule('showFuchsmajor', 0);
}

if(!$libGenericStorage->attributeExistsInCurrentModule('showFuchsmajor2')){
	$libGenericStorage->saveValueInCurrentModule('showFuchsmajor2', 0);
}

if(!$libGenericStorage->attributeExistsInCurrentModule('showScriptor')){
	$libGenericStorage->saveValueInCurrentModule('showScriptor', 0);
}

if(!$libGenericStorage->attributeExistsInCurrentModule('showQuaestor')){
	$libGenericStorage->saveValueInCurrentModule('showQuaestor', 0);
}

if(!$libGenericStorage->attributeExistsInCurrentModule('showHaftungshinweis')){
	$libGenericStorage->saveValueInCurrentModule('showHaftungshinweis', 1);
}

$mailsent = false;

//mail to send?
if(isset($_POST['name']) && isset($_POST['telefon']) && isset($_POST['emailaddress']) && isset($_POST['nachricht'])){
	$error_emailaddress = false;
	$error_message = false;

	if(!$libString->isValidEmail($_POST['emailaddress'])){
		$error_emailaddress = true;
		$libGlobal->errorTexts[] = 'Die angegebene E-Mail-Adresse ist nicht korrekt.';
	}

	if(trim($_POST['nachricht']) == ''){
		$error_message = true;
		$libGlobal->errorTexts[] = 'Es wurde keine Nachricht eingegeben.';
	}

	if(!$error_emailaddress && !$error_message) {
		$nachricht = $_POST['name'] .' mit der Telefonnummer '.$_POST['telefon'].' und der E-Mail-Adresse ' .$_POST['emailaddress']. ' hat über das Kontaktformular folgende Nachricht geschrieben:' . PHP_EOL;
		$nachricht .= PHP_EOL;
		$nachricht .= $_POST['nachricht'];

		$mail = new PHPMailer();
		$mail->From = $libConfig->emailWebmaster;
		$mail->AddAddress($libConfig->emailInfo);
		$mail->Subject = 'E-Mail von ' .$libString->protectXSS($_POST['name']). ' über ' . $libGlobal->getSiteUrl();
		$mail->Body = $libString->protectXSS($nachricht);
		$mail->AddReplyTo($_POST['emailaddress']);
		$mail->CharSet = 'UTF-8';

		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

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
			$mailsent = true;
			$libGlobal->notificationTexts[] = 'Vielen Dank, Ihre Nachricht wurde weitergeleitet.';
		}
	}
}

/*
* output
*/
echo '<h1>Kontakt und Impressum</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<h2>Kontaktadresse</h2>';

echo '<div class="row">';
echo '<div class="col-sm-8">';

echo '<div class="h-card">';
echo '<p class="p-name p-org">' .$libConfig->verbindungName. '</p>';

echo '<address class="p-adr">';

if($libConfig->verbindungZusatz != ''){
	echo '<span class="p-extended-address">' .$libConfig->verbindungZusatz.'</span><br />';
}

echo '<span class="p-street-address">' .$libConfig->verbindungStrasse. '</span><br />';
echo '<span class="p-postal-code">' .$libConfig->verbindungPlz. '</span> <span class="p-locality">' .$libConfig->verbindungOrt. '</span><br />';
echo '<span class="p-country-name">' .$libConfig->verbindungLand. '</span><br />';
echo '<i class="fa fa-phone fa-fw" aria-hidden="true"></i> <span class="p-tel">' .$libConfig->verbindungTelefon. '</span><br />';
echo '<i class="fa fa-envelope-o fa-fw" aria-hidden="true"></i> <span class="u-email">' .$libConfig->emailInfo. '</span><br />';

echo '</address>';
echo '</div>';

echo '<p>';

$vorstand = $libVerein->getAnsprechbarerAktivenVorstandIds();

if($libGenericStorage->loadValueInCurrentModule('showSenior') && $vorstand['senior']){
	echo 'Senior: '.$libMitglied->getMitgliedNameString($vorstand['senior'],0).'<br />';
}

if($libGenericStorage->loadValueInCurrentModule('showJubelsenior') && $vorstand['jubelsenior']){
	echo 'Jubelsenior: '.$libMitglied->getMitgliedNameString($vorstand['jubelsenior'],0).'<br />';
}

if($libGenericStorage->loadValueInCurrentModule('showConsenior') && $vorstand['consenior']){
	echo 'Consenior: '.$libMitglied->getMitgliedNameString($vorstand['consenior'],0).'<br />';
}

if($libGenericStorage->loadValueInCurrentModule('showFuchsmajor') && $vorstand['fuchsmajor']){
	echo 'Fuchsmajor: '.$libMitglied->getMitgliedNameString($vorstand['fuchsmajor'],0).'<br />';
}

if($libGenericStorage->loadValueInCurrentModule('showFuchsmajor2') && $vorstand['fuchsmajor2']){
	echo 'Fuchsmajor 2: '.$libMitglied->getMitgliedNameString($vorstand['fuchsmajor2'],0).'<br />';
}

if($libGenericStorage->loadValueInCurrentModule('showScriptor') && $vorstand['scriptor']){
	echo 'Scriptor: '.$libMitglied->getMitgliedNameString($vorstand['scriptor'],0).'<br />';
}

if($libGenericStorage->loadValueInCurrentModule('showQuaestor') && $vorstand['quaestor']){
	echo 'Quaestor: '.$libMitglied->getMitgliedNameString($vorstand['quaestor'],0).'<br />';
}

echo '</p>';
echo '</div>';

echo '<aside class="col-sm-4">';
echo '<img src="' . $libModuleHandler->getModuleDirectory() . '/custom/img/haus.jpg" alt="Haus" class="img-responsive center-block" />';
echo '</aside>';

echo '</div>';


echo '<h2>Kontakt aufnehmen</h2>';

echo '<div class="row">';
echo '<div class="col-md-12">';


if($mailsent){
	echo '<p>Vielen Dank, Ihre Nachricht wurde weitergeleitet.</p>';
} else {
	$name = '';

	if(isset($_POST['name']) && $_POST['name'] != ''){
		$name = $_POST['name'];
	}

	$email = '';

	if(isset($_POST['emailaddress']) && $_POST['emailaddress'] != ''){
		$email = $_POST['emailaddress'];
	}

	$telefon = '';

	if(isset($_POST['telefon']) && $_POST['telefon'] != ''){
		$telefon = $_POST['telefon'];
	}

	$nachricht = '';

	if(isset($_POST['nachricht']) && $_POST['nachricht'] != ''){
		$nachricht = $_POST['nachricht'];
	}


	echo '<form action="index.php?pid=kontakt_kontakt" method="post" class="form-horizontal">';
	echo '<fieldset>';

	$libForm->printTextInput('name', 'Name', $libString->protectXSS($name));
	$libForm->printTextInput('emailaddress', 'E-Mail-Adresse', $libString->protectXSS($email), 'email');
	$libForm->printTextInput('telefon', 'Telefonnummer', $libString->protectXSS($telefon), 'tel');
	$libForm->printTextarea('nachricht', 'Nachricht', $libString->protectXSS($nachricht));
	$libForm->printSubmitButton('<i class="fa fa-envelope-o" aria-hidden="true"></i> Abschicken');

	echo '</fieldset>';
	echo '</form>';
}

echo '</div>';
echo '</div>';

if($libGenericStorage->loadValueInCurrentModule('showHaftungshinweis') == 1){
	echo '<h2>Haftungshinweis</h2>';
	echo '<div class="row">';
	echo '<p class="col-md-12">';
	echo 'Haftungshinweis: Trotz sorgfältiger inhaltlicher Kontrolle übernehmen wir keine Haftung für die Inhalte externer Links. Für den Inhalt der verlinkten Seiten sind ausschließlich deren Betreiber verantwortlich.';
	echo '</p>';
	echo '</div>';
}

echo '<h2>VCMS</h2>';
echo 'Content Management System: <a href="http://www.' .$libGlobal->vcmsHostname. '">VCMS</a> (GNU GPL Lizenz)';
?>