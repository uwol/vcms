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

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();


$lastInsertId = '';

if(isset($_POST["datum"]) && $_POST["datum"] < @date("Y-m-d")){
	$libGlobal->errorTexts[] = "Das Datum liegt in der Vergangenheit.";
} elseif(isset($_POST["datum"]) && isset($_POST["beschreibung"])){
	$stmt = $libDb->prepare("INSERT INTO mod_reservierung_reservierung (datum, beschreibung, person) VALUES (:datum, :beschreibung, :person)");
	$stmt->bindValue(':datum', $_POST["datum"]);
	$stmt->bindValue(':beschreibung', $libString->protectXss($_POST["beschreibung"]));
	$stmt->bindValue(':person', $libAuth->getId(), PDO::PARAM_INT);
	$stmt->execute();

	$lastInsertId = $libDb->lastInsertId();

	$libGlobal->notificationTexts[] = 'Die Reservierung wurde gespeichert.';
}

if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET["id"]) && $_GET["id"] != ''){
	$stmt = $libDb->prepare("DELETE FROM mod_reservierung_reservierung WHERE id=:id AND person=:person");
	$stmt->bindValue(':id', $_GET["id"], PDO::PARAM_INT);
	$stmt->bindValue(':person', $libAuth->getId(), PDO::PARAM_INT);
	$stmt->execute();

	$libGlobal->notificationTexts[] = "Die Reservierung wurde gelöscht.";
}


/*
* output
*/
echo '<h1>Reservierungen</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<p><a href="index.php?pid=intranet_reservierung_buchen">Eine Reservierung hinzufügen</a></p>';
echo '<hr />';

$stmt = $libDb->prepare("SELECT * FROM mod_reservierung_reservierung WHERE datum >= :datum ORDER BY datum");
$stmt->bindValue(':datum', date('Y-m-d'));
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<div id="' .$row['id']. '" class="panel panel-default' .$libString->getLastInsertId($lastInsertId, $row['id']). '">';
	echo '<div class="panel-heading">';
	echo '<h3 class="panel-title">';
	echo $libTime->formatDateString($row['datum']);
	echo ' ';
	echo '<a href="index.php?pid=intranet_person&amp;personid=' .$row['person']. '">';
	echo $libPerson->getMitgliedNameString($row['person'], 0);
	echo '</a>';

	if($libAuth->getId() == $row['person']){
		echo ' ';
		echo '<a href="index.php?pid=intranet_reservierung_liste&amp;action=delete&amp;id=' .$row['id']. '" onclick="return confirm(\'Willst Du die Reservierung wirklich löschen?\')">';
		echo '<i class="fa fa-trash" aria-hidden="true"></i>';
		echo '</a>';
	}

	echo '</h3>';
	echo '</div>';

	echo '<div class="panel-body">';
	echo '<div class="media">';
	echo '<div class="media-body">';
	echo nl2br($row['beschreibung']);
	echo '</div>';

	echo '<div class="media-right hidden-xs">';
	echo $libPerson->getMitgliedSignature($row['person']);
	echo '</div>';

	echo '</div>';
	echo '</div>';
	echo '</div>';
}
