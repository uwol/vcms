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


if(!$libGenericStorage->attributeExistsInCurrentModule('fb_likebutton_url')){
	$libGenericStorage->saveValueInCurrentModule('fb_likebutton_url', '');
}

$title = $libConfig->verbindungName;
$description = $libConfig->seiteBeschreibung;


//facebook
$fb_url = 'http://'.$libConfig->sitePath;
$fb_likebutton_url = $libGenericStorage->loadValueInCurrentModule('fb_likebutton_url');

if($fb_likebutton_url != ''){
	$fb_url = $fb_likebutton_url;
}

echo '<script type="text/javascript">';
echo 'function insertFbLikeButton() {';
echo '	var container = document.getElementById("fblikebuttoncontainer");';
echo '	fbFrame = document.createElement("iframe");';
echo '	fbFrame.setAttribute("src", "http://www.facebook.com/plugins/like.php?layout=button_count&show_faces=false&width=150&action=like&colorscheme=light&height=22&href=' .urlencode($fb_url). '");';
echo '	fbFrame.setAttribute("scrolling", "no");';
echo '	fbFrame.setAttribute("frameborder", 0);';
echo '	fbFrame.style.border = "none";';
echo '	fbFrame.style.overflow = "hidden";';
echo '	fbFrame.style.width = "150px";';
echo '	fbFrame.style.height = "22px";';
echo '	fbFrame.setAttribute("allowTransparency", true);';
echo '  container.style.display = "block";';
echo '	container.replaceChild(fbFrame, document.getElementById("fblikebuttonlink"));';
echo '}';
echo '</script>';

echo '<span id="fblikebuttoncontainer">';
echo '<a id="fblikebuttonlink" onclick="insertFbLikeButton();" style="cursor:pointer;text-decoration:none">';
echo '<img src="' .$libModuleHandler->getModuleDirectory(). '/img/buttons/facebook.png" alt="Fb" />';
echo '</a>';
echo '</span> ';

// google+
$googleplus_url = 'http://'.$libConfig->sitePath;

echo '<script type="text/javascript">';
echo 'function insertGooglePlusButton() {';
echo '	var container = document.getElementById("googleplusbuttoncontainer");';
echo '	googleplusScript = document.createElement("script");';
echo '	googleplusScript.setAttribute("src", "https://apis.google.com/js/plusone.js");';
echo '	googleplusScript.setAttribute("type", "text/javascript");';
echo '	plusoneButton = document.createElement("div");';
echo '  plusoneButton.setAttribute("data-size", "medium");';
echo '	plusoneButton.setAttribute("class", "g-plusone");';
echo '  container.style.display = "block";';
echo '	container.appendChild(googleplusScript);';
echo '	container.replaceChild(plusoneButton, document.getElementById("googleplusbuttonlink"));';
echo '}';
echo '</script>';

echo '<span id="googleplusbuttoncontainer">';
echo '<a id="googleplusbuttonlink" onclick="insertGooglePlusButton();" style="cursor:pointer;text-decoration:none">';
echo '<img src="' .$libModuleHandler->getModuleDirectory(). '/img/buttons/googleplus.png" alt="G+" style="height:16px" />';
echo '</a>';
echo '</span> ';

//twitter
$url = 'http://'.$libConfig->sitePath;
echo '<a href="http://twitter.com/share?url=' .urlencode($url). '&amp;text=' .urlencode($title). '" rel="nofollow">';
echo '<img src="' .$libModuleHandler->getModuleDirectory(). '/img/buttons/twitter.png" alt="T" />';
echo '</a> ';

//rss
echo '<a href="http://' .$libConfig->sitePath. '/inc.php?iid=internet_home_rssfeed">';
echo '<img src="' .$libModuleHandler->getModuleDirectory(). '/img/buttons/rss.png" alt="R" />';
echo '</a> ';

echo '<hr />';