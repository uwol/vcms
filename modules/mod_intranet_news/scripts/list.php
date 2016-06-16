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


$lastInsertId = '';

if(isset($_POST['kategorie']) && isset($_POST['betroffenesmitglied']) && isset($_POST['text']) && trim($_POST['text']) != ''){
	$betroffenesmitglied = null;

	if(is_numeric($_POST['betroffenesmitglied']) && $_POST['betroffenesmitglied'] > 0){
		$betroffenesmitglied = $_POST['betroffenesmitglied'];
	}

	$stmt = $libDb->prepare('INSERT INTO mod_news_news (kategorieid, eingabedatum, text, betroffenesmitglied, autor) VALUES (:kategorieid, NOW(), :text, :betroffenesmitglied, :autor)');
	$stmt->bindValue(':kategorieid', $_POST['kategorie'], PDO::PARAM_INT);
	$stmt->bindValue(':text', $libString->protectXss(trim($_POST['text'])));
	$stmt->bindValue(':betroffenesmitglied', $betroffenesmitglied, PDO::PARAM_INT);
	$stmt->bindValue(':autor', $libAuth->getId(), PDO::PARAM_INT);
	$stmt->execute();

	$lastInsertId = $libDb->lastInsertId();

    $libMitglied->setMitgliedIntranetActivity($libAuth->getId(), 2, 0);

	$libGlobal->notificationTexts[] = 'Der Beitrag wurde gespeichert.';
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete' && isset($_REQUEST['id']) && $_REQUEST['id'] != ''){
	$stmt = $libDb->prepare('SELECT *, DATEDIFF(NOW(), eingabedatum) AS datediff FROM mod_news_news WHERE id=:id');
	$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
	$stmt->execute();
	$news_array = $stmt->fetch(PDO::FETCH_ASSOC);

	//can the news be deleted?
	if((in_array('internetwart', $libAuth->getAemter()))
		|| ($news_array['autor'] == $libAuth->getId() && $news_array['datediff'] < 7)){

		$stmt = $libDb->prepare('DELETE FROM mod_news_news WHERE id = :id');
		$stmt->bindValue(':id', $_REQUEST['id'], PDO::PARAM_INT);
		$stmt->execute();

		$libGlobal->notificationTexts[] = 'Der Beitrag wurde gelöscht.';
	}
}


/*
* output
*/

echo '<h1>Neuigkeiten im ' .$libTime->getSemesterString($libGlobal->semester). '</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<p><a href="index.php?pid=intranet_news_schreiben">Einen Beitrag hinzufügen</a></p>';

$stmt = $libDb->prepare("SELECT DATE_FORMAT(eingabedatum,'%Y-%m-01') AS eingabedatum FROM mod_news_news GROUP BY eingabedatum ORDER BY eingabedatum DESC");
$stmt->execute();

$daten = array();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$daten[] = $row['eingabedatum'];
}

echo $libTime->getSemesterMenu($libTime->getSemestersFromDates($daten), $libGlobal->semester);

$zeitraum = $libTime->getZeitraum($libGlobal->semester);

$stmt = $libDb->prepare('SELECT mod_news_news.eingabedatum, mod_news_news.id, mod_news_kategorie.bezeichnung, mod_news_news.text, mod_news_news.betroffenesmitglied, mod_news_news.autor, DATEDIFF(NOW(), mod_news_news.eingabedatum) AS datediff FROM mod_news_news LEFT JOIN mod_news_kategorie ON mod_news_news.kategorieid = mod_news_kategorie.id WHERE DATEDIFF(mod_news_news.eingabedatum, :semesterstart) >= 0 AND DATEDIFF(mod_news_news.eingabedatum, :semesterende) <= 0 ORDER BY eingabedatum DESC');
$stmt->bindValue(':semesterstart', $zeitraum[0]);
$stmt->bindValue(':semesterende', $zeitraum[1]);
$stmt->execute();

$lastsetmonth = '';

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	//month name
	if($lastsetmonth != substr($row['eingabedatum'], 0, 7)){
		echo '<h2>' .$libTime->getMonth((int) substr($row['eingabedatum'], 5, 2)). ' ' .substr($row['eingabedatum'], 0, 4). '</h2>';
		$lastsetmonth = substr($row['eingabedatum'], 0, 7);
	}

	echo '<div id="' .$row['id']. '" class="panel panel-default' .$libString->getLastInsertId($lastInsertId, $row['id']). '">';
	echo '<div class="panel-heading">';
	echo '<h3 class="panel-title">';
	echo $libTime->formatDateTimeString($row['eingabedatum'], 2);
	echo ' ';
	echo $row['bezeichnung'];

	if((in_array('internetwart', $libAuth->getAemter()))
			|| ($row['autor'] == $libAuth->getId() && $row['datediff'] < 7)){
		echo ' <a href="index.php?pid=intranet_news_news&amp;semester=' .$libGlobal->semester. '&amp;action=delete&amp;id=' .$row['id']. '" onclick="return confirm(\'Willst Du den Beitrag wirklich löschen?\')">';
		echo '<img src="styles/icons/basic/delete.svg" alt="delete" class="icon_small" />';
		echo '</a>';
	}

	echo '</h3>';
	echo '</div>';
	
	echo '<div class="panel-body">';
	echo '<div class="media">';

	if($row['betroffenesmitglied'] != '' && $row['betroffenesmitglied'] > 0){
		echo '<div class="media-left">';
		echo $libMitglied->getMitgliedSignature($row['betroffenesmitglied']);
		echo '</div>';
	}

	echo '<div class="media-body">';
	echo nl2br($row['text']);
	echo '</div>';

	if($row['autor'] != '' && $row['autor'] > 0){
		echo '<div class="media-right">';
		echo $libMitglied->getMitgliedSignature($row['autor']);
		echo '</div>';
	}

	echo '</div>';
	echo '</div>';
	echo '</div>';
}
?>