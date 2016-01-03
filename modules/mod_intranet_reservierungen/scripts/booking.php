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
<h1>Reservierung durchführen</h1>
<p>Bitte die Daten der Reservierung eingeben.</p>

<form method="post" action="index.php?pid=intranet_reservierung_liste" class="text">
<p><input type="text" name="datum" value="<?php echo @date("Y-m-d"); ?>" size="12" /> Datum</p>

<p>Beschreibung<br />
<textarea name="beschreibung" cols="50" rows="10">Bitte Räumlichkeit, Tageszeit und Art der Nutzung angeben. Bei einem Filmabend die Filmtitel nennen.</textarea>
</p>

<p><input type="submit" value="Reservierung vornehmen" /></p>
</form>