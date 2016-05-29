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

if(!$libGenericStorage->attributeExistsInCurrentModule('fb_likebutton_url')){
	$libGenericStorage->saveValueInCurrentModule('fb_likebutton_url', '');
}


echo '<h1>Willkommen</h1>';

echo '<div class="row">';

include("elements/announcements.php");
include("elements/nextevents.php");
include("elements/thumbnails.php");

echo '</div>';