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

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();


$libForm = new LibForm();

// synchronize tables
$libDb->query("INSERT INTO mod_rundbrief_empfaenger (id, empfaenger) SELECT id, 1 FROM base_person WHERE (SELECT COUNT(*) FROM mod_rundbrief_empfaenger WHERE id=base_person.id) = 0");

/*
* receiver counters
*/
$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person, mod_rundbrief_empfaenger WHERE base_person.id = mod_rundbrief_empfaenger.id AND email != '' AND email IS NOT NULL AND empfaenger=1 AND gruppe ='F'");
$stmt->execute();
$stmt->bindColumn('number', $anzahlFuechse);
$stmt->fetch();

$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person, mod_rundbrief_empfaenger WHERE base_person.id = mod_rundbrief_empfaenger.id AND email != '' AND email IS NOT NULL AND empfaenger=1 AND gruppe ='B'");
$stmt->execute();
$stmt->bindColumn('number', $anzahlBurschen);
$stmt->fetch();

$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person, mod_rundbrief_empfaenger WHERE base_person.id = mod_rundbrief_empfaenger.id AND email != '' AND email IS NOT NULL AND empfaenger=1 AND gruppe ='P'");
$stmt->execute();
$stmt->bindColumn('number', $anzahlAhah);
$stmt->fetch();

$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person, mod_rundbrief_empfaenger WHERE base_person.id = mod_rundbrief_empfaenger.id AND email != '' AND email IS NOT NULL AND empfaenger=1 AND gruppe ='P' AND interessiert = 1");
$stmt->execute();
$stmt->bindColumn('number', $anzahlBesondersInteressierteAhah);
$stmt->fetch();

$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person, mod_rundbrief_empfaenger WHERE base_person.id = mod_rundbrief_empfaenger.id AND email != '' AND email IS NOT NULL AND empfaenger=1 AND gruppe ='C'");
$stmt->execute();
$stmt->bindColumn('number', $anzahlCouleurdamen);
$stmt->fetch();

$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person, mod_rundbrief_empfaenger WHERE base_person.id = mod_rundbrief_empfaenger.id AND email != '' AND email IS NOT NULL AND empfaenger=1 AND (gruppe = 'G' OR gruppe = 'W')");
$stmt->execute();
$stmt->bindColumn('number', $anzahlGattinnen);
$stmt->fetch();

$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person, mod_rundbrief_empfaenger WHERE base_person.id = mod_rundbrief_empfaenger.id AND email != '' AND email IS NOT NULL AND empfaenger=1 AND (gruppe = 'G' OR gruppe = 'W') AND interessiert = 1");
$stmt->execute();
$stmt->bindColumn('number', $anzahlBesondersInteressierteGattinnen);
$stmt->fetch();


/*
* configuration
*/

if(!$libGenericStorage->attributeExistsInCurrentModule('preselectInteressierteAHAH')){
	$libGenericStorage->saveValueInCurrentModule('preselectInteressierteAHAH', 1);
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


echo '<h1>Rundbrief an Mitglieder verschicken</h1>';
echo '<p>Auf dieser Seite kann per E-Mail ein Rundbrief an diejenigen Mitglieder verschickt werden, die sich nicht aus dem Verteiler ausgetragen haben.</p>';

echo '<form action="index.php?pid=intranet_rundbrief_senden" method="post" enctype="multipart/form-data" onsubmit="return confirm(\'Willst Du die Nachricht wirklich verschicken?\');" class="form-horizontal">';
echo '<fieldset>';



echo '<div class="form-group">';
echo '<label class="col-sm-2 control-label">Adressaten</label>';
echo '<div class="col-sm-4">';

echo '<div class="checkbox"><label><input type="checkbox" name="fuchsia" checked="checked">';
echo $anzahlFuechse. ' Füchse + Fuchsmajor 1 &amp; 2';
echo '</label></div>';

echo '<div class="checkbox"><label><input type="checkbox" name="burschen" checked="checked">';
echo $anzahlBurschen. ' Burschen';
echo '</label></div>';

$ahahInteressiertChecked = '';

if($libGenericStorage->loadValueInCurrentModule('preselectInteressierteAHAH') == 1){
	$ahahInteressiertChecked = 'checked="checked"';
}

echo '<div class="checkbox"><label><input type="checkbox" name="ahah_interessiert" ' .$ahahInteressiertChecked. '>';
echo $anzahlBesondersInteressierteAhah. ' besonders interessierte alte Herren';
echo '</label></div>';

echo '<div class="checkbox"><label><input type="checkbox" name="ahah">';
echo $anzahlAhah. ' alte Herren';
echo '</label></div>';

echo '</div>';
echo '<div class="col-sm-4">';

echo '<div class="checkbox"><label><input type="checkbox" name="couleurdamen">';
echo $anzahlCouleurdamen. ' Couleurdamen';
echo '</label></div>';

echo '<div class="checkbox"><label><input type="checkbox" name="gattinnen_interessiert">';
echo $anzahlBesondersInteressierteGattinnen. ' besonders interessierte Gattinnen';
echo '</label></div>';

echo '<div class="checkbox"><label><input type="checkbox" name="gattinnen">';
echo $anzahlGattinnen. ' Gattinnen';
echo '</label></div>';

echo '</div></div>';


$libForm->printRegionDropDownBox("region", "Region", "");


echo '<hr />';

$formattedMitgliedNameString = $libMitglied->formatMitgliedNameString($libAuth->getAnrede(), $libAuth->getTitel(), '', $libAuth->getVorname(), $libAuth->getPraefix(), $libAuth->getNachname(), $libAuth->getSuffix(), 4);

$stmt = $libDb->prepare("SELECT email FROM base_person WHERE id=:id");
$stmt->bindValue(':id', $libAuth->getId(), PDO::PARAM_INT);
$stmt->execute();
$stmt->bindColumn('email', $email);
$stmt->fetch();

$formattedSenderString = $formattedMitgliedNameString. ' &lt;' .$email. '&gt;';

$libForm->printStaticText('Absender', $formattedSenderString);

echo '<hr />';

$libForm->printTextInput('subject', 'Betreff', '');
$libForm->printTextarea('nachricht', 'Nachricht', '');
$libForm->printFileInput('anhang', 'Anhang');
$libForm->printSubmitButton('Nachricht verschicken');

echo '</fieldset>';
echo '</form>';