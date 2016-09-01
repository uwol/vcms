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


$fb_url = $libGenericStorage->loadValueInCurrentModule('fb_url');
$showFbPagePlugin = $libGenericStorage->loadValueInCurrentModule('showFbPagePlugin');
$fbPagePluginEnabled = $showFbPagePlugin && $fb_url != '';

if($fbPagePluginEnabled){
	echo '<section class="facebook-box">';
	echo '<div class="container">';
	echo '<div class="row">';

	echo '<div style="max-width:500px" class="center-block">';
	echo '<iframe src="https://www.facebook.com/plugins/page.php?href=' .urlencode($fb_url). '&tabs&width=340&height=154&small_header=true&adapt_container_width=true&hide_cover=true&show_facepile=true&appId" width="100%" height="154" class="facebookPagePlugin" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>';
	echo '</div>';

	echo '</div>';
	echo '</div>';
	echo '</section>';
}
