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


$facebookUrl = $libGenericStorage->loadValue('mod_internet_home', 'facebook_url');
$instagramUrl = $libGenericStorage->loadValue('mod_internet_home', 'instagram_url');
$twitterUrl = $libGenericStorage->loadValue('mod_internet_home', 'twitter_url');
$wikipediaUrl = $libGenericStorage->loadValue('mod_internet_home', 'wikipedia_url');


echo '    <footer>' . PHP_EOL;
echo '      <div class="social-buttons text-right container">' . PHP_EOL;

if($facebookUrl != ''){
	echo '        <a href="' .$facebookUrl. '" rel="nofollow"><i class="fa fa-facebook-official fa-lg" aria-hidden="true"></i></a>' . PHP_EOL;
} else {
	echo '        <a href="http://www.facebook.com/sharer/sharer.php?u=' .urlencode($libGlobal->getSiteUrl()). '"><i class="fa fa-facebook-official fa-lg" aria-hidden="true"></i></a>' . PHP_EOL;
}

if($instagramUrl != ''){
	echo '        <a href="' .$instagramUrl. '" rel="nofollow"><i class="fa fa-instagram fa-lg" aria-hidden="true"></i></a>' . PHP_EOL;
}

if($twitterUrl != ''){
	echo '        <a href="' .$twitterUrl. '" rel="nofollow"><i class="fa fa-twitter-square fa-lg" aria-hidden="true"></i></a>' . PHP_EOL;
} else {
	echo '        <a href="http://twitter.com/share?url=' .urlencode($libGlobal->getSiteUrl()). '&amp;text=' .urlencode($libConfig->verbindungName). '" rel="nofollow"><i class="fa fa-twitter-square fa-lg" aria-hidden="true"></i></a>' . PHP_EOL;
}

if($wikipediaUrl != ''){
	echo '        <a href="' .$wikipediaUrl. '" rel="nofollow"><i class="fa fa-wikipedia-w fa-lg" aria-hidden="true"></i></a>' . PHP_EOL;
}

echo '      </div>' . PHP_EOL;
echo '    </footer>' . PHP_EOL;
echo '    <script src="vendor/object-fit-polyfill/object-fit-polyfill.js"></script>' . PHP_EOL;
echo '  </body>' . PHP_EOL;
echo '</html>' . PHP_EOL;
?>
