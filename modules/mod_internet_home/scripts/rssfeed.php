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

include("lib/thirdparty/feedcreator_modified.class.php");

header("Content-type: text/xml");

$rss = new UniversalFeedCreator();
$rss->title = $libConfig->verbindungName . " - Ankündigungen";
$rss->description = $libConfig->verbindungName. " - Die Ankündigungen auf der Startseite";
$rss->descriptionHtmlSyndicated = true;
$rss->link = 'http://' .$libConfig->sitePath. '/';
$rss->syndicationURL = 'http://' .$libConfig->sitePath. '/'.$_SERVER['PHP_SELF'];

$stmt = $libDb->prepare("SELECT id, text, startdatum, DATE_FORMAT(startdatum,'%a, %d %b %Y %T') AS datum FROM mod_internethome_nachricht ORDER BY startdatum DESC LIMIT 0,200");
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
   	$item = new FeedItem();
   	$item->title = $libString->truncate($libString->deleteBBCode($row['text']));
	$item->link = 'http://' .$libConfig->sitePath. '/index.php?pid=home_ankuendigungen&semester=' .$libTime->getSemesterEinesDatums($row['startdatum']). '#'.$row['id'];
   	$item->description = $libString->parseBBCode($row['text']);
   	$item->descriptionTruncSize = 1000;
   	$item->descriptionHtmlSyndicated = true;
   	$item->date = $row['datum']." ".@date('T');
   	$item->guid = $row['id'];
   	$rss->addItem($item);
}

echo $rss->createFeed('RSS2.0');
?>