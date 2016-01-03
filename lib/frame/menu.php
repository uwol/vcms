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
?>
      <nav id="menucontainer">
<?php
//menu
$libMenuRenderer = new LibMenuRenderer();
echo $libMenuRenderer->getMenuHtml($libMenu, $libGlobal->pid, $libAuth->getGruppe(), $libAuth->getAemter());

//semester cover
$semesterCoverString = $libTime->getSemesterCoverString($libGlobal->semester);

if($semesterCoverString != ""){
	echo '        <div id="cover">'."\r\n";
	echo '          '.$semesterCoverString."\r\n";
	echo '        </div>'."\r\n";
}

unset($semesterCoverString);
?>
      </nav>
      <main id="content">
