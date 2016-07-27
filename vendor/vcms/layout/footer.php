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

echo PHP_EOL;

if($libGlobal->page->isContainerEnabled()){
	echo '      </div>' . PHP_EOL;
	echo '    </main>' . PHP_EOL;
}

echo '    <footer>' . PHP_EOL;
echo '      <div class="social-buttons text-right container">' . PHP_EOL;

//wikipedia
$wp_url = $libGenericStorage->loadValue('mod_internet_home', 'wp_url');

if($wp_url != ''){
	echo '        <a href="' .$wp_url. '" rel="nofollow"><i class="fa fa-wikipedia-w fa-lg" aria-hidden="true"></i></a>' . PHP_EOL;
}

//facebook
$fb_url = $libGenericStorage->loadValue('mod_internet_home', 'fb_url');

if($fb_url != ''){
	echo '        <a href="' .$fb_url. '" rel="nofollow"><i class="fa fa-facebook-official fa-lg" aria-hidden="true"></i></a>' . PHP_EOL;
}

//twitter
$twitter_url = 'http://' .$libConfig->sitePath;
echo '        <a href="http://twitter.com/share?url=' .urlencode($twitter_url). '&amp;text=' .urlencode($libConfig->verbindungName). '" rel="nofollow"><i class="fa fa-twitter-square fa-lg" aria-hidden="true"></i></a>' . PHP_EOL;

echo '      </div>' . PHP_EOL;
echo '    </footer>' . PHP_EOL;
echo '  </body>' . PHP_EOL;
echo '</html>' . PHP_EOL;
?>