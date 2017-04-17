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


echo '<h1>Reservierung durchführen</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<div class="panel panel-default">';
echo '<form action="index.php?pid=intranet_reservations" method="post" class="form-horizontal">';
echo '<fieldset>';

$libForm->printTextInput('datum', 'Datum', date("Y-m-d"), 'date');
$libForm->printTextarea('beschreibung', 'Beschreibung', 'Bitte Räumlichkeit, Tageszeit und Art der Nutzung angeben. Bei einem Filmabend Filmtitel nennen.');
$libForm->printSubmitButton('<i class="fa fa-pencil-square-o" aria-hidden="true"></i> Reservierung speichern');

echo '</fieldset>';
echo '</form>';
echo '</div>';
