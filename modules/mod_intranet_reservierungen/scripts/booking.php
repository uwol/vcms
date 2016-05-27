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


echo '<h1>Reservierung durchführen</h1>';
echo '<p>Bitte die Daten der Reservierung eingeben.</p>';

echo '<form action="index.php?pid=intranet_reservierung_liste" method="post">';
echo '<fieldset>';

echo '<div class="form-group">';
echo '<label for="datum" class="col-sm-2 control-label">Datum</label>';
echo '<div class="col-sm-10"><input type="date" id="datum" name="datum" class="form-control" value="' .date("Y-m-d"). '" /></div>';
echo '</div>';


echo '<div class="form-group">';
echo '<label for="beschreibung" class="col-sm-2 control-label">Beschreibung</label>';
echo '<div class="col-sm-10"><textarea id="beschreibung" name="beschreibung" rows="7" class="form-control" placeholder="Bitte Räumlichkeit, Tageszeit und Art der Nutzung angeben. Bei einem Filmabend Filmtitel nennen."></textarea></div>';
echo '</div>';


echo '<div class="form-group">';
echo '<div class="col-sm-offset-2 col-sm-10">';
echo '<button type="submit" class="btn btn-default">Reservierung vornehmen</button>';
echo '</div>';
echo '</div>';

echo '</fieldset>';
echo '</form>';