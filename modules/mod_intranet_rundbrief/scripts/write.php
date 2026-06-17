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


// synchronize tables
$libDb->query("INSERT INTO mod_rundbrief_empfaenger (id, empfaenger) SELECT id, 1 FROM base_person WHERE (SELECT COUNT(*) FROM mod_rundbrief_empfaenger WHERE id=base_person.id) = 0");

/*
* receiver counters
*/
$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person, mod_rundbrief_empfaenger WHERE base_person.id = mod_rundbrief_empfaenger.id AND email != '' AND email IS NOT NULL AND empfaenger=1 AND gruppe='F'");
$stmt->execute();
$stmt->bindColumn('number', $anzahlFuechse);
$stmt->fetch();

$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person, mod_rundbrief_empfaenger WHERE base_person.id = mod_rundbrief_empfaenger.id AND email != '' AND email IS NOT NULL AND empfaenger=1 AND gruppe='B'");
$stmt->execute();
$stmt->bindColumn('number', $anzahlBurschen);
$stmt->fetch();

$streetNormalized = $libString->normalizeStreet($libConfig->verbindungStrasse);

$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person, mod_rundbrief_empfaenger WHERE base_person.id = mod_rundbrief_empfaenger.id AND email != '' AND email IS NOT NULL AND empfaenger=1 AND (gruppe='F' OR gruppe='B') AND plz1=:plz AND strasse1 LIKE :street");
$stmt->bindValue(':plz', $libConfig->verbindungPlz);
$stmt->bindValue(':street', '%' .$streetNormalized. '%');
$stmt->execute();
$stmt->bindColumn('number', $anzahlHausbewohner);
$stmt->fetch();

$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person, mod_rundbrief_empfaenger WHERE base_person.id = mod_rundbrief_empfaenger.id AND email != '' AND email IS NOT NULL AND empfaenger=1 AND gruppe='P'");
$stmt->execute();
$stmt->bindColumn('number', $anzahlAhah);
$stmt->fetch();

$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person, mod_rundbrief_empfaenger WHERE base_person.id = mod_rundbrief_empfaenger.id AND email != '' AND email IS NOT NULL AND empfaenger=1 AND gruppe='P' AND interessiert= 1");
$stmt->execute();
$stmt->bindColumn('number', $anzahlBesondersInteressierteAhah);
$stmt->fetch();

$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person, mod_rundbrief_empfaenger WHERE base_person.id = mod_rundbrief_empfaenger.id AND email != '' AND email IS NOT NULL AND empfaenger=1 AND gruppe='C'");
$stmt->execute();
$stmt->bindColumn('number', $anzahlCouleurdamen);
$stmt->fetch();

$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person, mod_rundbrief_empfaenger WHERE base_person.id = mod_rundbrief_empfaenger.id AND email != '' AND email IS NOT NULL AND empfaenger=1 AND (gruppe='G' OR gruppe='W')");
$stmt->execute();
$stmt->bindColumn('number', $anzahlGattinnen);
$stmt->fetch();

$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person, mod_rundbrief_empfaenger WHERE base_person.id = mod_rundbrief_empfaenger.id AND email != '' AND email IS NOT NULL AND empfaenger=1 AND (gruppe='G' OR gruppe='W') AND interessiert=1");
$stmt->execute();
$stmt->bindColumn('number', $anzahlBesondersInteressierteGattinnen);
$stmt->fetch();


/*
* configuration
*/

if(!$libGenericStorage->attributeExistsInCurrentModule('preselect_int_ahah')){
	$libGenericStorage->saveValueInCurrentModule('preselect_int_ahah', 1);
}


echo '<h1>Rundbrief an Mitglieder verschicken</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();


echo '<div class="card">';
echo '<div class="card-body">';
echo '<form action="index.php?pid=intranet_rundbrief_senden" method="post" enctype="multipart/form-data" onsubmit="return confirm(\'Willst Du die Nachricht wirklich verschicken?\');" class="">';
echo '<fieldset>';

echo '<div class="mb-3 row">';
echo '<label class="col-sm-3 col-form-label">Adressaten</label>';
echo '<div class="col-sm-4">';

echo '<div class="form-check">';
echo '<input class="form-check-input" type="checkbox" id="recipient_fuchsia" name="fuchsia" checked="checked">';
echo '<label class="form-check-label" for="recipient_fuchsia">';
echo $anzahlFuechse. ' Füchse &amp; Fuchsmajor';
echo '</label></div>';

echo '<div class="form-check">';
echo '<input class="form-check-input" type="checkbox" id="recipient_burschen" name="burschen" checked="checked">';
echo '<label class="form-check-label" for="recipient_burschen">';
echo $anzahlBurschen. ' Burschen';
echo '</label></div>';

$ahahInteressiertChecked = '';

if($libGenericStorage->loadValueInCurrentModule('preselect_int_ahah') == 1){
	$ahahInteressiertChecked = 'checked="checked"';
}

echo '<div class="form-check">';
echo '<input class="form-check-input" type="checkbox" id="recipient_ahah_interessiert" name="ahah_interessiert" ' .$ahahInteressiertChecked. '>';
echo '<label class="form-check-label" for="recipient_ahah_interessiert">';
echo $anzahlBesondersInteressierteAhah. ' besonders interessierte alte Herren';
echo '</label></div>';

echo '<div class="form-check">';
echo '<input class="form-check-input" type="checkbox" id="recipient_ahah" name="ahah">';
echo '<label class="form-check-label" for="recipient_ahah">';
echo $anzahlAhah. ' alte Herren';
echo '</label></div>';

echo '</div>';
echo '<div class="col-sm-4">';

echo '<div class="form-check">';
echo '<input class="form-check-input" type="checkbox" id="recipient_hausbewohner" name="hausbewohner">';
echo '<label class="form-check-label" for="recipient_hausbewohner">';
echo $anzahlHausbewohner. ' Hausbewohner';
echo '</label></div>';

echo '<div class="form-check">';
echo '<input class="form-check-input" type="checkbox" id="recipient_couleurdamen" name="couleurdamen">';
echo '<label class="form-check-label" for="recipient_couleurdamen">';
echo $anzahlCouleurdamen. ' Couleurdamen';
echo '</label></div>';

echo '<div class="form-check">';
echo '<input class="form-check-input" type="checkbox" id="recipient_gattinnen_interessiert" name="gattinnen_interessiert">';
echo '<label class="form-check-label" for="recipient_gattinnen_interessiert">';
echo $anzahlBesondersInteressierteGattinnen. ' besonders interessierte Gattinnen';
echo '</label></div>';

echo '<div class="form-check">';
echo '<input class="form-check-input" type="checkbox" id="recipient_gattinnen" name="gattinnen">';
echo '<label class="form-check-label" for="recipient_gattinnen">';
echo $anzahlGattinnen. ' Gattinnen';
echo '</label></div>';

echo '</div></div>';

$libForm->printRegionDropDownBox("region", "Nach Region", "");

echo '<hr />';

$formattedMitgliedNameString = $libPerson->formatNameString($libAuth->getAnrede(), $libAuth->getTitel(), '', $libAuth->getVorname(), $libAuth->getPraefix(), $libAuth->getNachname(), $libAuth->getSuffix(), 4);

$stmt = $libDb->prepare("SELECT email FROM base_person WHERE id=:id");
$stmt->bindValue(':id', $libAuth->getId(), PDO::PARAM_INT);
$stmt->execute();
$stmt->bindColumn('email', $email);
$stmt->fetch();

$formattedSenderString = $formattedMitgliedNameString. ' &lt;' .$email. '&gt;';

$libForm->printStaticText('Absender', $formattedSenderString);
$libForm->printTextInput('subject', 'Betreff', '');
$libForm->printFileInput('anhang', 'Anhang');
$libForm->printTextarea('nachricht', 'Nachricht', '');
$libForm->printSubmitButton('<i class="fa fa-envelope-o" aria-hidden="true"></i> Nachricht verschicken');

echo '</fieldset>';
echo '</form>';
echo '</div>';
echo '</div>';
