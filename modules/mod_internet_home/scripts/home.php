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

if(!is_object($libGlobal))
	exit();

if(!$libGenericStorage->attributeExistsInCurrentModule('fb:admins')){
	$libGenericStorage->saveValueInCurrentModule('fb:admins', 0);
}

echo '<h1>Willkommen</h1>';

echo '<div class="row">';
echo '<section class="col-md-8">';

include("elements/announcements.php");

echo '</section>';
echo '<aside class="col-md-4">';

include("elements/nextevent.php");
include("elements/socialmedia.php");

echo '</aside>';
echo '</div>';

include("elements/randomimage.php");