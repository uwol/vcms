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

echo PHP_EOL;
echo '          </main>' . PHP_EOL;
echo '        </div>' . PHP_EOL;
echo '      </div>' . PHP_EOL;
echo '      <div class="row">' . PHP_EOL;
echo '        <div class="col-md-12">' . PHP_EOL;
echo '          <footer>' . PHP_EOL;
echo '            <div class="social-buttons text-right">' . PHP_EOL;


$title = $libConfig->verbindungName;
$description = $libConfig->seiteBeschreibung;


//facebook
$fb_url = $libGenericStorage->loadValue('mod_internet_home', 'fb_likebutton_url');

if($fb_url != ''){
	echo '              <a href="' .$fb_url. '" rel="nofollow">' . PHP_EOL;
	echo '                <img src="styles/icons/social/facebook.svg" alt="FB" class="icon" />' . PHP_EOL;
	echo '              </a>' . PHP_EOL;
}

//twitter
$url = 'http://' .$libConfig->sitePath;

echo '              <a href="http://twitter.com/share?url=' .urlencode($url). '&amp;text=' .urlencode($title). '" rel="nofollow">' . PHP_EOL;
echo '                <img src="styles/icons/social/twitter.svg" alt="T" class="icon" />' . PHP_EOL;
echo '              </a>' . PHP_EOL;

//rss
echo '              <a href="http://' .$libConfig->sitePath. '/inc.php?iid=internet_home_rssfeed" rel="nofollow">' . PHP_EOL;
echo '                <img src="styles/icons/social/rss.svg" alt="RSS" class="icon" />' . PHP_EOL;
echo '              </a>' . PHP_EOL;

echo '            </div>' . PHP_EOL;
echo '          </footer>' . PHP_EOL;
echo '        </div>' . PHP_EOL;
echo '      </div>' . PHP_EOL;
echo '    </div>' . PHP_EOL;
echo '  </body>' . PHP_EOL;
echo '</html>' . PHP_EOL;
?>