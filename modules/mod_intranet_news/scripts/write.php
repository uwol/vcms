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

?>
<h1>Neuer Nachrichtenbeitrag</h1>
<?php

/*
* input form
*/
echo '<form method="post" action="index.php?pid=intranet_news_news">'."\n";
echo '<textarea name="text" cols="65" rows="20"></textarea>'."\n";

echo '<p>';
echo '<select name="kategorie">'."\n";


$stmt = $libDb->prepare("SELECT * FROM mod_news_kategorie ORDER BY bezeichnung");
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<option value="' .$row['id']. '">' .$row['bezeichnung']. '</option>'."\n";
}

echo '</select> Kategorie'."\n";
echo '</p>';

echo '<p>';
echo '<select name="betroffenesmitglied">'."\n";
echo '<option value="">---------------------------</option>'."\n";

$stmt = $libDb->prepare("SELECT * FROM base_person ORDER BY name,vorname");
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<option value="' .$row['id']. '">' .$libMitglied->formatMitgliedNameString($row['anrede'], $row['titel'], $row['rang'], $row['vorname'], $row['praefix'], $row['name'], $row['suffix'], 1). '</option>'."\n";
}

echo '</select> Betroffenes Mitglied'."\n";
echo '</p>';

echo '<p>Bei z. B. einem Todesfall, einem Austritt oder einer Hochzeit kann das betroffene Mitglied angeben werden.</p>';

echo '<input type="submit" value="Beitrag abschicken">'."\n";
echo '</form>'."\n";
?>