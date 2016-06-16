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

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();


/*
* output
*/
$stmt = $libDb->prepare('SELECT mod_news_kategorie.bezeichnung, mod_news_news.eingabedatum, mod_news_news.id, mod_news_news.text, mod_news_news.betroffenesmitglied, mod_news_news.autor FROM mod_news_news LEFT JOIN mod_news_kategorie ON mod_news_news.kategorieid=mod_news_kategorie.id ORDER BY mod_news_news.eingabedatum DESC LIMIT 0,5');
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$link = 'index.php?pid=intranet_news_news&amp;semester=' .$libTime->getSemesterNameAtDate($row['eingabedatum']). '#' .$row['id'];

	echo '<div class="panel panel-default">';
	echo '<div class="panel-heading">';
	echo '<h3 class="panel-title">' .$libTime->formatDateTimeString($row['eingabedatum'], 2). ' <a href="' .$link. '">' .$row['bezeichnung']. '</a></h3>';
	echo '</div>';
	
	echo '<div class="panel-body">';
	echo '<div class="media">';

	if($row['betroffenesmitglied'] != ''){
		echo '<div class="media-left">';
		echo $libMitglied->getMitgliedSignature($row['betroffenesmitglied']);
		echo '</div>';
	}

	echo '<div class="media-body">';

	if(($row['text']) != ''){
		$truncateReplacement = ' <a href="' .$link. '">...</a>';
		echo $libString->truncate(trim($row['text']), 500, $truncateReplacement);
	}

	echo '</div>';

	echo '<div class="media-right">';
	echo $libMitglied->getMitgliedSignature($row['autor']);
	echo '</div>';

	echo '</div>';
	echo '</div>';
	echo '</div>';
}
?>