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

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();


echo '<h1>Versand der Nachricht</h1>';

$stmt = $libDb->prepare('SELECT email FROM base_person WHERE id=:id');
$stmt->bindValue(':id', $libAuth->getId(), PDO::PARAM_INT);
$stmt->execute();
$stmt->bindColumn('email', $email);
$stmt->fetch();


if(!isset($_POST['nachricht']) || $_POST['nachricht'] == '' || !isset($_POST['subject'])){
	$libGlobal->errorTexts[] = 'Es wurde kein Nachrichtentext eingegeben.';
} else {
	/*
	* build receiver string
	*/
	$betreffgruppenstring = '';

	if(isset($_POST['fuchsia']) && $_POST['fuchsia'] == 'on'){
		$betreffgruppen[] = 'Füchse';
	}

	if(isset($_POST['burschen']) && $_POST['burschen'] == 'on'){
		$betreffgruppen[] = 'Burschen';
	}

	if(isset($_POST['ahah_interessiert']) && $_POST['ahah_interessiert'] == 'on' && (!isset($_POST['ahah']) || $_POST['ahah'] != 'on')){
		$betreffgruppen[] = 'Int. AHAH';
	}

	if(isset($_POST['ahah']) && $_POST['ahah'] == 'on'){
		$betreffgruppen[] = 'AHAH';
	}

	if(isset($_POST['hausbewohner']) && $_POST['hausbewohner'] == 'on'){
		$betreffgruppen[] = 'Hausbewohner';
	}

	if(isset($_POST['couleurdamen']) && $_POST['couleurdamen'] == 'on'){
		$betreffgruppen[] = 'Couleurdamen';
	}

	if(isset($_POST['gattinnen_interessiert']) && $_POST['gattinnen_interessiert'] == 'on' && (!isset($_POST['gattinnen']) || $_POST['gattinnen'] != 'on')){
		$betreffgruppen[] = 'Int. Gattinnen';
	}

	if(isset($_POST['gattinnen']) && $_POST['gattinnen'] == 'on'){
		$betreffgruppen[] = 'Gattinnen';
	}

	if(count($betreffgruppen) == 0){
		$libGlobal->errorTexts[] = 'Es wurde keine Adressatengruppe ausgewählt.';
	}

	$betreffgruppenstring = '[' .implode(', ', $betreffgruppen). '] ';
	$betreffregionstring = '';

	if($_POST['region'] != '' && $_POST['region'] != 'NULL'){
		$stmt = $libDb->prepare('SELECT bezeichnung FROM base_region WHERE id=:id');
		$stmt->bindValue(':id', $_POST['region'], PDO::PARAM_INT);
		$stmt->execute();
		$stmt->bindColumn('bezeichnung', $region);
		$stmt->fetch();

		if($region != ''){
			$betreffregionstring = '[' .$region. '] ';
		}
	}

	/*
	* build subject
	*/
	$subject = '[' .$libConfig->verbindungName. '] ' .$betreffgruppenstring . $betreffregionstring . $_POST['subject'];

	/*
	* start output
	*/
	echo '<p>' .$libString->protectXss($subject). '</p>';
	echo '<p>' .nl2br($libString->protectXss($_POST['nachricht'])). '</p>';

	/*
	* build and send mail
	*/
	$sqlgruppen_string = '';
	$sqlgruppen = array();

	if(isset($_POST['fuchsia']) && $_POST['fuchsia'] == 'on'){
		$sqlgruppen[] = "gruppe='F'";
	}

	if(isset($_POST['burschen']) && $_POST['burschen'] == 'on'){
		$sqlgruppen[] = "gruppe='B'";
	}

	if(isset($_POST['ahah_interessiert']) && $_POST['ahah_interessiert'] == 'on'){
		$sqlgruppen[] = "(gruppe = 'P' AND interessiert = 1)";
	}

	if(isset($_POST['ahah']) && $_POST['ahah'] == 'on'){
		$sqlgruppen[] = "gruppe='P'";
	}

	if(isset($_POST['hausbewohner']) && $_POST['hausbewohner'] == 'on'){
		$sqlgruppen[] = "((gruppe='F' OR gruppe='B') AND plz1=:plz AND strasse1 LIKE :street)";
	}

	if(isset($_POST['couleurdamen']) && $_POST['couleurdamen'] == 'on'){
		$sqlgruppen[] = "gruppe='C'";
	}

	if(isset($_POST['gattinnen_interessiert']) && $_POST['gattinnen_interessiert'] == 'on'){
		$sqlgruppen[] = "((gruppe='G' OR gruppe='W') AND interessiert = 1)";
	}

	if(isset($_POST['gattinnen']) && $_POST['gattinnen'] == 'on'){
		$sqlgruppen[] = "(gruppe='G' OR gruppe='W')";
	}

	$sqlgruppen_string = ' AND ('.implode(' OR ',$sqlgruppen).') ';

	//evaluate regional restrictions
	$regionString = '';

	if($_POST['region'] != '' && $_POST['region'] != 'NULL'){
		$regionString = " AND (region1=:region OR region2=:region) ";
	}

	//build array of receivers
	$empfaengerArray = array();

	//add receivers
	$sql = "SELECT anrede, titel, rang, vorname, praefix, name, suffix, email FROM base_person, mod_rundbrief_empfaenger WHERE base_person.id = mod_rundbrief_empfaenger.id AND email != '' AND email IS NOT NULL AND empfaenger=1 ".$regionString.$sqlgruppen_string ." AND gruppe != 'X' AND gruppe != 'T' AND gruppe != 'V' ORDER BY name";
	$stmt = $libDb->prepare($sql);

	if($regionString != ''){
		$stmt->bindValue(':region', $_POST['region'], PDO::PARAM_INT);
	}

	if(isset($_POST['hausbewohner']) && $_POST['hausbewohner'] == 'on'){
		$streetNormalized = $libString->normalizeStreet($libConfig->verbindungStrasse);

		$stmt->bindValue(':plz', $libConfig->verbindungPlz);
		$stmt->bindValue(':street', '%' .$streetNormalized. '%');
	}

	$stmt->execute();

	$i = 0;

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		$empfaengerArray[$i][0] = $row['email'];
		$empfaengerArray[$i][1] = $libPerson->formatMitgliedNameString($row['anrede'], $row['titel'], $row['rang'], $row['vorname'], $row['praefix'], $row['name'], $row['suffix'], 0);
		$i++;
	}

	//add Fuchsmajor
	if(isset($_POST['fuchsia']) && $_POST['fuchsia'] == 'on' && (!isset($_POST['burschen']) || $_POST['burschen'] != 'on')){
		$vorstand = $libAssociation->getAnsprechbarerAktivenVorstandIds();

		$stmt = $libDb->prepare("SELECT anrede, titel, rang, vorname, praefix, name, suffix, email FROM base_person, mod_rundbrief_empfaenger WHERE (base_person.id = :fuchsmajor OR base_person.id = :fuchsmajor2) AND base_person.id = mod_rundbrief_empfaenger.id AND gruppe != 'X' AND gruppe != 'T' AND gruppe != 'V' AND empfaenger=1");
		$stmt->bindValue(':fuchsmajor', $vorstand['fuchsmajor'], PDO::PARAM_INT);
		$stmt->bindValue(':fuchsmajor2', $vorstand['fuchsmajor2'], PDO::PARAM_INT);
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($row['email'] != ''){
				$empfaengerArray[$i][0] = $row['email'];
				$empfaengerArray[$i][1] = $libPerson->formatMitgliedNameString($row['anrede'], $row['titel'], $row['rang'], $row['vorname'], $row['praefix'], $row['name'], $row['suffix'], 0);
				$i++;
			}
		}
	}

	//attachement
	$attachementFile = '';
	$attachementName = '';

	if(isset($_FILES['anhang']) && isset($_FILES['anhang']['tmp_name']) && isset($_FILES['anhang']['name'])){
		$attachementFile = $_FILES['anhang']['tmp_name'];
		$attachementName = $_FILES['anhang']['name'];
	}

	$empfangerPerMail = 15;
	$anzahlMails = ceil(count($empfaengerArray) / $empfangerPerMail);

	for($j=0; $j<$anzahlMails; $j++){
		$mailNumber = $j + 1;
		$subEmpfaengerArray = array_slice($empfaengerArray, $j*$empfangerPerMail, $empfangerPerMail);

		echo '<hr />';
		echo '<p>Sende E-Mail ' .$mailNumber;

		if(is_file($attachementFile)){
			echo ' mit Anhang';
		}

		echo ' an:</p>';
		echo '<p>';

		foreach($subEmpfaengerArray as $empfaenger){
			echo $empfaenger[1]. ' &lt;' .$empfaenger[0]. '&gt;<br />';
		}

		echo '</p>';

		sendMail(
			$libPerson->formatMitgliedNameString($libAuth->getAnrede(), $libAuth->getTitel(), '', $libAuth->getVorname(), $libAuth->getPraefix(), $libAuth->getNachname(), $libAuth->getSuffix(), 4),
			$subject, $email, $_POST['nachricht'], $subEmpfaengerArray, $attachementFile, $attachementName);
	}
}

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();


function sendMail($fromName, $subject, $replyEmail, $message, $empfaengerArray, $attachementFile, $attachementName){
	global $libAuth, $libMail;

	$mail = new PHPMailer();
	$libMail->configurePHPMailer($mail);

	$mail->FromName = $fromName;
	$mail->Subject = $subject;
	$mail->IsHTML(false);
	$mail->AddReplyTo($replyEmail);
	$mail->Body = stripslashes($message);

	if(!istImVorstand($libAuth->getAemter())){
		// low priority
		$mail->Priority = 5;
	}

	foreach($empfaengerArray as $empfaenger){
		$mail->AddBCC($empfaenger[0]);
	}

	if(is_file($attachementFile)){
		$mail->AddAttachment($attachementFile, $attachementName);
	}

	if(!$mail->Send()){
		echo '<p>Fehler beim Versand: ' .$mail->ErrorInfo. '</p>';
	}
}

function istImVorstand($aemter){
	if(!is_array($aemter)){
		return false;
	}

	$vorstandsaemter = array('senior', 'consenior', 'fuchsmajor', 'fuchsmajor2', 'scriptor', 'quaestor', 'jubelsenior', 'ahv_senior', 'ahv_consenior', 'ahv_keilbeauftragter', 'ahv_scriptor', 'ahv_quaestor');
	$vorstandsaemterderperson = array_intersect($aemter, $vorstandsaemter);

	if(count($vorstandsaemterderperson) > 0){
		return true;
	} else {
		return false;
	}
}
