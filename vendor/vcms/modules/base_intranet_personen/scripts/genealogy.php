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


echo '<h1>Stammbaum</h1>';
echo '<p>Mitglieder der <span style="color:#0000FF">Aktivitas sind blau</span> gekennzeichnet, AHAH schwarz, <span style="color:#660000">verstorbene BbBb braun</span>, <span style="color:#C0C0C0">ausgetretene grau</span> und weitere <span style="color:#669933">grün</span>. Das im vorherigen Menü angewählte Mitglied ist <span style="background-color:red">rot</span> hinterlegt.</p>';

if(isset($_GET['mitgliedid']) && is_numeric($_GET['mitgliedid'])){
	$person = new \vcms\genealogy\LibGenealogyElement($_GET['mitgliedid'], $_GET['mitgliedid']);
	$root = $person->searchFirstLeibvater();
	$genealogy = new \vcms\genealogy\LibGenealogy($root, '0', $_GET['mitgliedid']);

	echo $genealogy->getString();
}
?>