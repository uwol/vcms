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

if(!is_object($libGlobal))
	exit();


$headerVideoPath = $libModuleHandler->getModuleDirectory(). '/custom/header.mp4';
$headerVideoAbsolutePath = $libFilesystem->getAbsolutePath($headerVideoPath);
$headerVideoExists = is_file($headerVideoAbsolutePath);

if($headerVideoExists){
	echo '<video autoplay muted loop class="hero hidden-xs">';
	echo '<source src="' .$headerVideoPath. '" type="video/mp4"/>';
	echo '</video>';
}

echo '<header>';
echo '<div class="header-content">';
echo '<div class="header-content-inner">';
echo '<h1 id="homeHeading">Willkommen</h1>';
echo '<a class="btn btn-circle hidden-xs" href="#pastevents">';
echo '<i class="fa fa-angle-double-down"></i>';
echo '</a>';
echo '</div>';
echo '</div>';
echo '</header>';
?>
