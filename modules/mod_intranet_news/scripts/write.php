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


echo '<h1>Neuer Nachrichtenbeitrag</h1>';

echo '<div class="panel panel-default">';
echo '<div class="panel-body">';
echo '<form action="index.php?pid=intranet_news" method="post" class="form-horizontal">';
echo '<fieldset>';

$libForm->printTextarea('text', 'Nachricht', '');

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

$libForm->printMitgliederDropDownBox('betroffenesmitglied', 'Betroffenes Mitglied', '');
$libForm->printSubmitButton('<i class="fa fa-pencil-square-o" aria-hidden="true"></i> Beitrag speichern');

echo '</fieldset>';
echo '</form>';
echo '</div>';
echo '</div>';
