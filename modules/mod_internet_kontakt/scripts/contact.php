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
* action
*/
if($libGenericStorage->loadValueInCurrentModule('showSenior') == ''){
	$libGenericStorage->saveValueInCurrentModule('showSenior', 0);
}

if($libGenericStorage->loadValueInCurrentModule('showJubelsenior') == ''){
	$libGenericStorage->saveValueInCurrentModule('showJubelsenior', 0);
}

if($libGenericStorage->loadValueInCurrentModule('showConsenior') == ''){
	$libGenericStorage->saveValueInCurrentModule('showConsenior', 0);
}

if($libGenericStorage->loadValueInCurrentModule('showFuchsmajor') == ''){
	$libGenericStorage->saveValueInCurrentModule('showFuchsmajor', 0);
}

if($libGenericStorage->loadValueInCurrentModule('showFuchsmajor2') == ''){
	$libGenericStorage->saveValueInCurrentModule('showFuchsmajor2', 0);
}

if($libGenericStorage->loadValueInCurrentModule('showScriptor') == ''){
	$libGenericStorage->saveValueInCurrentModule('showScriptor', 0);
}

if($libGenericStorage->loadValueInCurrentModule('showQuaestor') == ''){
	$libGenericStorage->saveValueInCurrentModule('showQuaestor', 0);
}

if($libGenericStorage->loadValueInCurrentModule('showHaftungshinweis') == ''){
	$libGenericStorage->saveValueInCurrentModule('showHaftungshinweis', 1);
}

$mailsent = false;

//mail to send?
if(isset($_POST['name']) && isset($_POST['telefon']) && isset($_POST['emailaddress']) && isset($_POST['nachricht'])){
	$error_emailaddress = false;
	$error_message = false;

	if(!$libString->isValidEmail($_POST['emailaddress'])){
		$error_emailaddress = true;
		$libGlobal->errorTexts[] = "Die angegebene E-Mailadresse ist nicht korrekt.";
	}

	if(trim($_POST['nachricht']) == ''){
		$error_message = true;
		$libGlobal->errorTexts[] = "Es wurde keine Nachricht eingegeben.";
	}

	if(!$error_emailaddress && !$error_message) {
		require_once("lib/thirdparty/class.phpmailer.php");

		$nachricht = $_POST['name'] ." mit der Telefonnummer ".$_POST['telefon']." und der E-Mailadresse ".$_POST['emailaddress']." hat über das Kontaktformular folgende Nachricht geschrieben\n\r".$_POST['nachricht'];

		$mail = new PHPMailer();
		$mail->AddAddress($libConfig->emailInfo);
		$mail->FromName = $libConfig->verbindungName ." Mailer";
		$mail->Subject = 'Mail von ' .$libString->protectXSS($_POST['name']). ' über ' . $libConfig->sitePath;
		$mail->Body = $libString->protectXSS($nachricht);
		$mail->AddReplyTo($_POST['emailaddress']);
		$mail->CharSet = "UTF-8";

		if($mail->Send()){
			$mailsent = true;
			$libGlobal->notificationTexts[] = "Vielen Dank, Ihre Nachricht wurde weitergeleitet.";
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
echo '<img src="' . $libModuleHandler->getModuleDirectory() . 'custom/img/haus.jpg" alt="Haus" style="float:right;height:200px;" />';

echo $libConfig->verbindungName .'<br /><br />';

$vorstand = $libVerein->getAnsprechbarerAktivenVorstandIds();

if($libGenericStorage->loadValueInCurrentModule('showSenior') && $vorstand['senior']){
	echo "Senior: ".$libMitglied->getMitgliedNameString($vorstand['senior'],0)."<br />";
}

if($libGenericStorage->loadValueInCurrentModule('showJubelsenior') && $vorstand['jubelsenior']){
	echo "Jubelsenior: ".$libMitglied->getMitgliedNameString($vorstand['jubelsenior'],0)."<br />";
}

if($libGenericStorage->loadValueInCurrentModule('showConsenior') && $vorstand['consenior']){
	echo "Consenior: ".$libMitglied->getMitgliedNameString($vorstand['consenior'],0)."<br />";
}

if($libGenericStorage->loadValueInCurrentModule('showFuchsmajor') && $vorstand['fuchsmajor']){
	echo "Fuchsmajor: ".$libMitglied->getMitgliedNameString($vorstand['fuchsmajor'],0)."<br />";
}

if($libGenericStorage->loadValueInCurrentModule('showFuchsmajor2') && $vorstand['fuchsmajor2']){
	echo "Fuchsmajor 2: ".$libMitglied->getMitgliedNameString($vorstand['fuchsmajor2'],0)."<br />";
}

if($libGenericStorage->loadValueInCurrentModule('showScriptor') && $vorstand['scriptor']){
	echo "Scriptor: ".$libMitglied->getMitgliedNameString($vorstand['scriptor'],0)."<br />";
}

if($libGenericStorage->loadValueInCurrentModule('showQuaestor') && $vorstand['quaestor']){
	echo "Quaestor: ".$libMitglied->getMitgliedNameString($vorstand['quaestor'],0)."<br />";
}

if($libConfig->verbindungZusatz != ""){
	echo $libConfig->verbindungZusatz."<br />";
}

echo '<br />';

echo $libConfig->verbindungStrasse . '<br />';
echo $libConfig->verbindungPlz ." ".$libConfig->verbindungOrt . '<br />';
echo $libConfig->verbindungLand . '<br />';
echo $libConfig->verbindungTelefon . '<br />';
echo $libConfig->emailInfo . '<br />';


echo '<h2>Kontakt aufnehmen</h2>';
echo '<div style="text-align: center;">';

if($mailsent){
	echo '<p>Vielen Dank, Ihre Nachricht wurde weitergeleitet.</p>';
} else {
	$name = "Name";

	if(isset($_POST['name']) && $_POST['name'] != ""){
		$name = $_POST['name'];
	}

	$email = "E-Mailadresse";

	if(isset($_POST['emailaddress']) && $_POST['emailaddress'] != ""){
		$email = $_POST['emailaddress'];
	}

	$telefon = "Telefonnummer";

	if(isset($_POST['telefon']) && $_POST['telefon'] != ""){
		$telefon = $_POST['telefon'];
	}

	$nachricht = "";

	if(isset($_POST['nachricht']) && $_POST['nachricht'] != ""){
		$nachricht = $_POST['nachricht'];
	}

	echo '<form method="post" action="index.php?pid=kontakt_kontakt">';
	echo '<fieldset>';
	echo '<input type="text" name="name" size="44" value="' .$libString->protectXSS($name). '" /><br />';
	echo '<input type="text" name="emailaddress" size="44" value="' .$libString->protectXSS($email). '" /><br />';
	echo '<input type="text" name="telefon" size="44" value="' .$libString->protectXSS($telefon). '" /><br />';
	echo '<textarea name="nachricht" cols="44" rows="7">' .$libString->protectXSS($nachricht). '</textarea><br />';
	echo '<input type="submit" value="Abschicken" />';
	echo '</fieldset>';
	echo '</form>';
}

echo '</div>';

if($libGenericStorage->loadValueInCurrentModule('showHaftungshinweis') == 1){
	echo '<h2>Haftungshinweis</h2>';
	echo '<p>Haftungshinweis: Trotz sorgfältiger inhaltlicher Kontrolle übernehmen wir keine Haftung für die Inhalte externer Links. Für den Inhalt der verlinkten Seiten sind ausschließlich deren Betreiber verantwortlich.</p>';
}

echo '<h2>VCMS</h2>';
echo 'Content Management System: <a href="http://www.' .$libGlobal->vcmsHostname. '">VCMS</a> (GNU GPL Lizenz)';
?>