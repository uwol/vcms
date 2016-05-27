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


echo '<h1>Neuer Nachrichtenbeitrag</h1>';

/*
* input form
*/
echo '<form action="index.php?pid=intranet_news_news" method="post" class="form-horizontal">';
echo '<fieldset>';

echo '<div class="form-group">';
echo '<label for="text" class="col-sm-2 control-label">Nachricht</label>';
echo '<div class="col-sm-10"><textarea id="text" name="text" rows="10" class="form-control"></textarea></div>';
echo '</div>';


echo '<div class="form-group">';
echo '<label for="kategorie" class="col-sm-2 control-label">Kategorie</label>';
echo '<div class="col-sm-10"><select id="kategorie" name="kategorie" class="form-control">';

$stmt = $libDb->prepare('SELECT * FROM mod_news_kategorie ORDER BY bezeichnung');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<option value="' .$row['id']. '">' .$row['bezeichnung']. '</option>';
}

echo '</select></div>';
echo '</div>';


echo '<div class="form-group">';
echo '<label for="betroffenesmitglied" class="col-sm-2 control-label">Betroffenes Mitglied</label>';
echo '<div class="col-sm-10"><select id="betroffenesmitglied" name="betroffenesmitglied" class="form-control">';
echo '<option value="">---------------------------</option>';

$stmt = $libDb->prepare('SELECT * FROM base_person ORDER BY name, vorname');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<option value="' .$row['id']. '">' .$libMitglied->formatMitgliedNameString($row['anrede'], $row['titel'], $row['rang'], $row['vorname'], $row['praefix'], $row['name'], $row['suffix'], 1). '</option>';
}

echo '</select></div>';
echo '</div>';


echo '<p class="col-sm-offset-2 col-sm-10">Bei einer Nachricht mit Bezug zu einem Mitglied wie z. B. einer Hochzeit oder einem Todesfall kann das betroffene Mitglied angeben werden.</p>';

echo '<div class="form-group">';
echo '<div class="col-sm-offset-2 col-sm-10">';
echo '<button type="submit" class="btn btn-default">Beitrag abschicken</button>';
echo '</div>';
echo '</div>';

echo '</fieldset>';
echo '</form>';
?>