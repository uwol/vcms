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

$stmtCount = $libDb->prepare("SELECT COUNT(*) AS number FROM mod_news_news");
$stmtCount->execute();
$stmtCount->bindColumn('number', $count);
$stmtCount->fetch();

// if there are entries
if($count > 0){
	echo '<tr><th>Neues</th></tr>'."\n";
	echo '<tr><td class="ankuendigungsBox">'."\n";
	echo '<hr />'."\n";

	$stmt = $libDb->prepare("SELECT mod_news_kategorie.bezeichnung, mod_news_news.eingabedatum, mod_news_news.id, mod_news_news.text, mod_news_news.betroffenesmitglied, mod_news_news.autor FROM mod_news_news LEFT JOIN mod_news_kategorie ON mod_news_news.kategorieid=mod_news_kategorie.id ORDER BY mod_news_news.eingabedatum DESC LIMIT 0,3");
	$stmt->execute();

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		$date = $libTime->formatDateTimeString($row['eingabedatum'], 2);

		echo '<div class="textankuendigung" style="clear:both">';

		if($row['betroffenesmitglied'] != ''){
			echo $libMitglied->getMitgliedSignature($row['betroffenesmitglied'], "left");
		}

		echo $libMitglied->getMitgliedSignature($row['autor'], "right");
		echo "<b>".$date." - " .$row['bezeichnung']. "</b><br />\n";

		if(($row["text"]) != ''){
			echo '<a href="index.php?pid=intranet_news_news&amp;semester=' .$libTime->getSemesterEinesDatums($row['eingabedatum']). '#' .$row['id']. '">';
			echo $libString->truncate(trim($row['text']), 200);
			echo "</a>\n";
		}

		echo '</div>'."\n";
		echo '<div style="clear:both"><hr /></div>'."\n";
	}

	echo '</td></tr>'."\n";
}
?>