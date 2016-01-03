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

?>
<h1>Rundbrief an Mitglieder verschicken</h1>
<p>Auf dieser Seite kann per Email ein Rundbrief an diejenigen Mitglieder verschickt werden, die sich nicht aus dem Verteiler ausgetragen haben.</p>

<form method="post" action="index.php?pid=intranet_rundbrief_senden" onsubmit="return confirm('Willst Du die Nachricht wirklich verschicken?');">

Nachricht senden an: <br />
<table style="width:100%">
<tr>
<td style="width:50%"><input type="checkbox" name="fuchsia" checked="checked" /> <?php echo $anzahlFuechse; ?> Füchse + Fuchsmajor 1 &amp; 2</td>
<td style="width:50%"><input type="checkbox" name="couleurdamen" /> <?php echo $anzahlCouleurdamen; ?> Couleurdamen</td>
</tr>
<tr>
<td><input type="checkbox" name="burschen" checked="checked" /> <?php echo $anzahlBurschen; ?> Burschen</td>
<td><input type="checkbox" name="gattinnen_interessiert" /> <?php echo $anzahlBesondersInteressierteGattinnen; ?> besonders interessierte Gattinnen</td>
</tr>
<tr>
<td><input type="checkbox" name="ahah_interessiert" <?php
if($libGenericStorage->loadValueInCurrentModule('preselectInteressierteAHAH') == 1)
	echo 'checked="checked"';
?> /> <?php echo $anzahlBesondersInteressierteAhah; ?> besonders interessierte alte Herren</td>
<td><input type="checkbox" name="gattinnen" /> <?php echo $anzahlGattinnen; ?> Gattinnen</td>
</tr>
<tr>
<td><input type="checkbox" name="ahah" /> <?php echo $anzahlAhah; ?> alte Herren</td>
</tr>
</table>
<br />
Auf Region beschränken:<br />
<?php echo $libForm->getRegionDropDownBox("region", "Region", ""); ?>
<br /><br />
Absendername: <?php
echo $libMitglied->formatMitgliedNameString($libAuth->getAnrede(), $libAuth->getTitel(), '', $libAuth->getVorname(), $libAuth->getPraefix(), $libAuth->getNachname(), $libAuth->getSuffix(), 4);
?><br />
Antwortadresse: <?php

$stmt = $libDb->prepare("SELECT email FROM base_person WHERE id=:id");
$stmt->bindValue(':id', $libAuth->getId(), PDO::PARAM_INT);
$stmt->execute();
$stmt->bindColumn('email', $email);
$stmt->fetch();

echo $email;
?><br /><br />
Betreff: <input type="text" name="subject" value="Bitte Betreff eingeben" size="50" /><br />
Nachrichtentext: <span id="wordcountdisplay"></span><br />
<textarea name="nachricht" cols="68" rows="20"></textarea><br />
<input type="submit" value="Nachricht verschicken" />
</form>