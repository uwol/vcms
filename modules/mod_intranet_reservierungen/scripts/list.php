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
* action
*/
if(isset($_POST["datum"]) && $_POST["datum"] < @date("Y-m-d")){
	$libGlobal->errorTexts[] = "Das Datum liegt in der Vergangenheit.";
} elseif(isset($_POST["datum"]) && isset($_POST["beschreibung"])){
	$stmt = $libDb->prepare("INSERT INTO mod_reservierung_reservierung (datum, beschreibung, person) VALUES (:datum, :beschreibung, :person)");
	$stmt->bindValue(':datum', $_POST["datum"]);
	$stmt->bindValue(':beschreibung', $libString->protectXss($_POST["beschreibung"]));
	$stmt->bindValue(':person', $libAuth->getId(), PDO::PARAM_INT);
	$stmt->execute();

	$libGlobal->notificationTexts[] = 'Die Reservierung wurde durchgeführt.';
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

echo '<p>Um eine Reservierung anzulegen, bitte <a href="index.php?pid=intranet_reservierung_buchen">diese Seite</a> öffnen.</p>';

$stmt = $libDb->prepare("SELECT * FROM mod_reservierung_reservierung WHERE datum >= :datum ORDER BY datum");
$stmt->bindValue(':datum', date('Y-m-d'));
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	echo '<hr />';
	echo '<div class="media">';
	echo '<div class="media-body">';
	echo '<h4>' . $libTime->wochentag($row['datum']).', '.$libTime->formatDateTimeString($row['datum'], 2). '</h4>';

	if($libAuth->getId() == $row['person']){
		echo ' - <a href="index.php?pid=intranet_reservierung_liste&amp;action=delete&amp;id=' .$row['id']. '" onclick="return confirm(\'Willst Du die Reservierung wirklich löschen?\')">Reservierung löschen</a>';
	}

	echo '<p>' .$row['beschreibung']. '</p>';
	echo '</div>';

	echo '<div class="media-right">';

	echo $libMitglied->getMitgliedSignature($row['person']);

	echo '</div>';
	echo '</div>';
}
?>