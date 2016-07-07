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


class LibNewsTimelineEvent extends LibTimelineEvent{
	function getBadgeClass(){
		return 'news';
	}

	function getBadgeIcon(){
		return '<i class="fa fa-newspaper-o" aria-hidden="true"></i>';
	}
}


$stmt = $libDb->prepare('SELECT mod_news_kategorie.bezeichnung, mod_news_news.eingabedatum, mod_news_news.id, mod_news_news.text, mod_news_news.betroffenesmitglied, mod_news_news.autor FROM mod_news_news LEFT JOIN mod_news_kategorie ON mod_news_news.kategorieid=mod_news_kategorie.id WHERE DATEDIFF(mod_news_news.eingabedatum, :semesterstart) >= 0 AND DATEDIFF(mod_news_news.eingabedatum, :semesterende) <= 0 ORDER BY mod_news_news.eingabedatum DESC');
$stmt->bindValue(':semesterstart', $zeitraum[0]);
$stmt->bindValue(':semesterende', $zeitraum[1]);
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$url = 'index.php?pid=intranet_news_news&amp;semester=' .$libTime->getSemesterNameAtDate($row['eingabedatum']). '#' .$row['id'];

	$truncateReplacement = ' <a href="' .$url. '">...</a>';
	$description = $libString->truncate(trim($row['text']), 500, $truncateReplacement);

	$timelineEvent = new LibNewsTimelineEvent();

	$timelineEvent->setTitle($row['bezeichnung']);
	$timelineEvent->setDatetime($row['eingabedatum']);
	$timelineEvent->setDescription($description);
	$timelineEvent->setAuthorId($row['autor']);
	$timelineEvent->setReferencedPersonId($row['betroffenesmitglied']);
	$timelineEvent->setUrl($url);

	$timelineEventSet->addEvent($timelineEvent);
}
?>