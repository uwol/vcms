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

if (!is_object($libGlobal) || !$libAuth->isLoggedin()) {
    exit();
}


require('lib/persons.php');

/*
* determine person id
*/
if (isset($_GET['id']) && $_GET['id'] != '' &&	is_numeric($_GET['id'])
        && preg_match("/^[0-9]+$/", $_GET['id'])) {
    $id = $_GET['id'];
} else {
    $id = $libAuth->getId();
}


/*
* own profile?
*/
$ownprofile = false;

if ($libAuth->getId() == $id) {
    $ownprofile = true;
}


$stmt = $libDb->prepare('SELECT * FROM base_person WHERE id=:id');
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);


/*
* actions
*/
if ($ownprofile) {
    if (isset($_POST['formType']) && $_POST['formType'] == 'personData') {
        $leibMember = '';

        if (isset($_POST['leibmitglied'])) {
            $leibMember = $_POST['leibmitglied'];
        }

        if ($leibMember == $id) {
            $libGlobal->errorTexts[] = 'Das Mitglied darf nicht von sich selbst der Leibbursch sein.';
        } else {
            $stmt = $libDb->prepare('UPDATE base_person SET anrede=:anrede, titel=:titel, rang=:rang, zusatz1=:zusatz1, strasse1=:strasse1, ort1=:ort1, plz1=:plz1, land1=:land1,
				telefon1=:telefon1, zusatz2=:zusatz2, strasse2=:strasse2, ort2=:ort2, plz2=:plz2, land2=:land2,telefon2=:telefon2, mobiltelefon=:mobiltelefon,
				email=:email, skype=:skype, webseite=:webseite, spitzname=:spitzname, beruf=:beruf, leibmitglied=:leibmitglied, region1=:region1, region2=:region2, vita=:vita WHERE id=:id');
            $stmt->bindValue(':anrede', trim($_POST['anrede'] ?? ''));
            $stmt->bindValue(':titel', trim($_POST['titel'] ?? ''));
            $stmt->bindValue(':rang', trim($_POST['rang'] ?? ''));
            $stmt->bindValue(':zusatz1', trim($_POST['zusatz1'] ?? ''));
            $stmt->bindValue(':strasse1', trim($_POST['strasse1'] ?? ''));
            $stmt->bindValue(':ort1', trim($_POST['ort1'] ?? ''));
            $stmt->bindValue(':plz1', trim($_POST['plz1'] ?? ''));
            $stmt->bindValue(':land1', trim($_POST['land1'] ?? ''));
            $stmt->bindValue(':telefon1', trim($_POST['telefon1'] ?? ''));
            $stmt->bindValue(':zusatz2', trim($_POST['zusatz2'] ?? ''));
            $stmt->bindValue(':strasse2', trim($_POST['strasse2'] ?? ''));
            $stmt->bindValue(':ort2', trim($_POST['ort2'] ?? ''));
            $stmt->bindValue(':plz2', trim($_POST['plz2'] ?? ''));
            $stmt->bindValue(':land2', trim($_POST['land2'] ?? ''));
            $stmt->bindValue(':telefon2', trim($_POST['telefon2'] ?? ''));
            $stmt->bindValue(':mobiltelefon', trim($_POST['mobiltelefon'] ?? ''));
            $stmt->bindValue(':email', strtolower(trim($_POST['email'] ?? '')));
            $stmt->bindValue(':skype', trim($_POST['skype'] ?? ''));
            $stmt->bindValue(':webseite', trim($_POST['webseite'] ?? ''));
            $stmt->bindValue(':spitzname', trim($_POST['spitzname'] ?? ''));
            $stmt->bindValue(':beruf', trim($_POST['beruf'] ?? ''));
            $stmt->bindValue(':leibmitglied', $leibMember, PDO::PARAM_INT);
            $stmt->bindValue(':region1', $_POST['region1'] ?? '', PDO::PARAM_INT);
            $stmt->bindValue(':region2', $_POST['region2'] ?? '', PDO::PARAM_INT);
            $stmt->bindValue(':vita', trim($_POST['vita'] ?? ''));
            $stmt->bindValue(':id', $libAuth->getId(), PDO::PARAM_INT);
            $stmt->execute();
        }

        //if the mailing module is installed
        if ($libModuleHandler->moduleIsAvailable('mod_intranet_rundbrief')) {
            //synchronize tables
            $stmt = $libDb->prepare('INSERT INTO mod_rundbrief_empfaenger (id, empfaenger) SELECT id, 1 FROM base_person WHERE (SELECT COUNT(*) FROM mod_rundbrief_empfaenger WHERE id = base_person.id) = 0');
            $stmt->execute();

            if (isset($_POST['empfaenger'])) {
                $stmt = $libDb->prepare('UPDATE mod_rundbrief_empfaenger SET empfaenger=:empfaenger WHERE id = :id');
                $stmt->bindValue(':empfaenger', $_POST['empfaenger'], PDO::PARAM_BOOL);
                $stmt->bindValue(':id', $libAuth->getId(), PDO::PARAM_INT);
                $stmt->execute();
            }

            if (isset($_POST['interessiert'])) {
                $stmt = $libDb->prepare('UPDATE mod_rundbrief_empfaenger SET interessiert=:interessiert WHERE id = :id');
                $stmt->bindValue(':interessiert', $_POST['interessiert'], PDO::PARAM_BOOL);
                $stmt->bindValue(':id', $libAuth->getId(), PDO::PARAM_INT);
                $stmt->execute();
            }
        }

        if ($libModuleHandler->moduleIsAvailable('mod_intranet_zipfelranking')) {
            //synchronize tables
            $stmt = $libDb->prepare('INSERT INTO mod_zipfelranking_anzahl (id, anzahlzipfel) SELECT id, 0 FROM base_person WHERE (SELECT COUNT(*) FROM mod_zipfelranking_anzahl WHERE id = base_person.id) = 0');
            $stmt->execute();

            if (isset($_POST['anzahlzipfel'])) {
                $stmt = $libDb->prepare('UPDATE mod_zipfelranking_anzahl SET anzahlzipfel=:anzahlzipfel WHERE id = :id');
                $stmt->bindValue(':anzahlzipfel', $_POST['anzahlzipfel'], PDO::PARAM_INT);
                $stmt->bindValue(':id', $libAuth->getId(), PDO::PARAM_INT);
                $stmt->execute();
            }
        }

        if (($_POST['strasse1'] ?? '') != $row['strasse1'] || ($_POST['ort1'] ?? '') != $row['ort1'] || ($_POST['plz1'] ?? '') != $row['plz1'] || ($_POST['land1'] ?? '') != $row['land1'] || ($_POST['telefon1'] ?? '') != $row['telefon1']) {
            $stmt = $libDb->prepare('UPDATE base_person SET datum_adresse1_stand=NOW() WHERE id = :id');
            $stmt->bindValue(':id', $libAuth->getId(), PDO::PARAM_INT);
            $stmt->execute();
        }

        if (($_POST['strasse2'] ?? '') != $row['strasse2'] || ($_POST['ort2'] ?? '') != $row['ort2'] || ($_POST['plz2'] ?? '') != $row['plz2'] || ($_POST['land2'] ?? '') != $row['land2'] || ($_POST['telefon2'] ?? '') != $row['telefon2']) {
            $stmt = $libDb->prepare('UPDATE base_person SET datum_adresse2_stand=NOW() WHERE id = :id');
            $stmt->bindValue(':id', $libAuth->getId(), PDO::PARAM_INT);
            $stmt->execute();
        }
    } elseif (isset($_POST['formType']) && $_POST['formType'] == 'photoDataUpload') {
        if (isset($_FILES['bilddatei']['tmp_name']) && $_FILES['bilddatei']['tmp_name'] != '') {
            $libImage->savePersonPhotoByFilesArray($libAuth->getId(), 'bilddatei');
        }
    } elseif (isset($_POST['formType']) && $_POST['formType'] == 'personPassword') {
        $oldPassword = $_POST['oldpwd'] ?? '';
        $newPassword1 = $_POST['newpwd1'] ?? '';
        $newPassword2 = $_POST['newpwd2'] ?? '';

        if (!$libAuth->checkPasswordForPerson($libAuth->getId(), $oldPassword)) {
            $libGlobal->errorTexts[] = 'Das alte Passwort ist nicht korrekt.';
        } elseif (trim($newPassword1) == '') {
            $libGlobal->errorTexts[] = 'Es wurde kein neues Passwort angegeben.';
        } elseif ($newPassword2 != $newPassword1) {
            $libGlobal->errorTexts[] = 'Das neue Passwort und die Passwortwiederholung stimmen nicht überein.';
        } else {
            $libAuth->savePassword($libAuth->getId(), $newPassword1);
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'photoDelete') {
        $libImage->deletePersonPhoto($libAuth->getId());
    }
}

//------------------------------------------------------------------------------------------------

/*
* output
*/

$stmt = $libDb->prepare('SELECT * FROM base_person WHERE id=:id');
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!is_array($row)) {
    echo '<h1>Mitglied</h1>';
    echo $libString->getErrorBoxText();
    echo $libString->getNotificationBoxText();
    echo '<p class="mb-4">Das Mitglied existiert nicht.</p>';
    return;
}

/*
* header
*/

$personSchema = $libPerson->getPersonSchema($row);

echo '<script type="application/ld+json">';
echo str_replace(['<', '>', '&'], ['\u003c', '\u003e', '\u0026'], json_encode($personSchema));
echo '</script>';


echo '<h1>';
echo $libString->protectXSS($libPerson->formatNameString($row['anrede'], $row['titel'], $row['rang'], $row['vorname'], $row['praefix'], $row['name'], $row['suffix'], 0));
echo ' ';
echo $libString->protectXSS((string) $libPerson->getChargenString($id));
echo '</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<div class="row">';

echo '<div class="col-sm-3">';
printPersonSignature($row, $ownprofile);
echo '</div>';

echo '<div class="col-sm-9">';
echo '<div class="card">';
echo '<div class="card-body">';
printPersonData($row);

echo '<div class="row">';
printPrimaryAddress($row);
printSecondaryAddress($row);
echo '</div>';

printCommunication($row);
printAssociationDetails($row);
echo '</div>';
echo '</div>';

echo '<div class="card">';
echo '<div class="card-body">';
printVita($row);
echo '</div>';

echo '</div>';
echo '</div>';
echo '</div>';


/*
* Password change form
*/
if ($ownprofile) {
    echo '<h2>Passwort ändern</h2>';

    echo '<div class="card">';
    echo '<div class="card-body">';
    echo '<form action="index.php?pid=intranet_person&amp;id=' .$id. '" method="post">';
    echo '<fieldset>';
    echo '<input type="hidden" name="formType" value="personPassword" />';

    $libForm->printTextInput('oldpwd', 'Altes Passwort', '', 'password', false, true);
    $libForm->printTextInput('newpwd1', 'Neues Passwort', '', 'password', false, true);
    $libForm->printTextInput('newpwd2', 'Neues Passwort (Wiederholung)', '', 'password', false, true);

    echo '<div class="row mb-3">';
    echo '<div class="col-sm-3"></div>';
    echo '<div class="col-sm-9">' .$libAuth->getPasswordRequirements(). '</div>';
    echo '</div>';

    $libForm->printSubmitButton('<i class="fa fa-pencil-square-o" aria-hidden="true"></i> Passwort speichern');

    echo '</fieldset>';
    echo '</form>';
    echo '</div>';
    echo '</div>';

    echo '<h2>Stammdaten ändern</h2>';

    echo '<div class="card">';
    echo '<div class="card-body">';
    echo '<form action="index.php?pid=intranet_person" method="post">';
    echo '<fieldset>';
    echo '<input type="hidden" name="formType" value="personData" />';

    $stmt = $libDb->prepare('SELECT * FROM base_person WHERE id=:id');
    $stmt->bindValue(':id', $libAuth->getId(), PDO::PARAM_INT);
    $stmt->execute();
    $row2 = $stmt->fetch(PDO::FETCH_ASSOC);

    $libForm->printTextInput('anrede', 'Anrede', $row2['anrede']);
    $libForm->printTextInput('titel', 'Titel', $row2['titel']);
    $libForm->printTextInput('rang', 'Rang', $row2['rang']);
    $libForm->printTextInput('vorname', 'Vorname', $row2['vorname'], 'text', true);
    $libForm->printTextInput('praefix', 'Präfix', $row2['praefix'], 'text', true);
    $libForm->printTextInput('name', 'Nachname', $row2['name'], 'text', true);
    $libForm->printTextInput('suffix', 'Suffix', $row2['suffix'], 'text', true);
    $libForm->printTextInput('spitzname', 'Spitzname', $row2['spitzname']);
    $libForm->printTextInput('beruf', 'Beruf', $row2['beruf']);

    if ($row['gruppe'] != 'C' && $row['gruppe'] != 'G' && $row['gruppe'] != 'W' && $row['gruppe'] != 'Y') {
        $libForm->printMembersDropDownBox('leibmitglied', 'Leibbursch', $row2['leibmitglied'], true);
    }

    echo '<hr />';

    $libForm->printTextInput('zusatz1', 'Zusatz', $row2['zusatz1']);
    $libForm->printTextInput('strasse1', 'Straße', $row2['strasse1']);
    $libForm->printTextInput('ort1', 'Ort', $row2['ort1']);
    $libForm->printTextInput('plz1', 'PLZ', $row2['plz1']);
    $libForm->printTextInput('land1', 'Land', $row2['land1']);
    $libForm->printTextInput('telefon1', 'Telefon', $row2['telefon1']);

    echo '<hr />';

    $libForm->printTextInput('zusatz2', 'Zusatz', $row2['zusatz2']);
    $libForm->printTextInput('strasse2', 'Straße', $row2['strasse2']);
    $libForm->printTextInput('ort2', 'Ort', $row2['ort2']);
    $libForm->printTextInput('plz2', 'PLZ', $row2['plz2']);
    $libForm->printTextInput('land2', 'Land', $row2['land2']);
    $libForm->printTextInput('telefon2', 'Telefon', $row2['telefon2']);

    echo '<hr />';

    $libForm->printTextInput('mobiltelefon', 'Mobiltelefon', $row2['mobiltelefon']);
    $libForm->printTextInput('email', 'E-Mail-Adresse', $row2['email'], 'email', false, true);
    $libForm->printTextInput('skype', 'Skype', $row2['skype']);
    $libForm->printTextInput('webseite', 'Webseite', $row2['webseite']);

    echo '<hr />';

    $libForm->printRegionDropDownBox('region1', 'Region 1', $row['region1']);
    $libForm->printRegionDropDownBox('region2', 'Region 2', $row['region2']);

    if ($libModuleHandler->moduleIsAvailable("mod_intranet_rundbrief")) {
        $stmt = $libDb->prepare("SELECT empfaenger FROM mod_rundbrief_empfaenger WHERE id=:id");
        $stmt->bindValue(':id', $libAuth->getId(), PDO::PARAM_INT);
        $stmt->execute();
        $stmt->bindColumn('empfaenger', $recipient);
        $stmt->fetch();

        $libForm->printBoolSelectBox('empfaenger', 'Rundbriefe erhalten', $recipient);

        if ($row['gruppe'] == 'P' || $row['gruppe'] == 'G' || $row['gruppe'] == 'W') {
            $stmt = $libDb->prepare("SELECT interessiert FROM mod_rundbrief_empfaenger WHERE id=:id");
            $stmt->bindValue(':id', $libAuth->getId(), PDO::PARAM_INT);
            $stmt->execute();
            $stmt->bindColumn('interessiert', $interested);
            $stmt->fetch();

            $libForm->printBoolSelectBox('interessiert', 'Rundbriefe aus Aktivenleben erhalten', $interested);
        }
    }

    if ($libModuleHandler->moduleIsAvailable("mod_intranet_zipfelranking")) {
        $stmt = $libDb->prepare("SELECT anzahlzipfel FROM mod_zipfelranking_anzahl WHERE id=:id");
        $stmt->bindValue(':id', $libAuth->getId(), PDO::PARAM_INT);
        $stmt->execute();
        $stmt->bindColumn('anzahlzipfel', $zipfelCount);
        $stmt->fetch();

        $libForm->printTextInput('anzahlzipfel', 'Zipfelanzahl', $zipfelCount);
    }

    $libForm->printTextarea('vita', 'Vita', $row['vita']);
    $libForm->printSubmitButton('<i class="fa fa-pencil-square-o" aria-hidden="true"></i> Speichern');

    echo '</fieldset>';
    echo '</form>';
    echo '</div>';
    echo '</div>';
}



/*
* Leib brother
*/
$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person AS bs, base_person AS bv WHERE bs.id=:id AND bs.leibmitglied = bv.id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$stmt->bindColumn('number', $count);
$stmt->fetch();

if ($count > 0) {
    echo '<h2>Leibbursche</h2>';

    $stmt = $libDb->prepare("SELECT bv.id, bv.anrede, bv.titel, bv.rang, bv.vorname, bv.praefix, bv.name, bv.suffix, bv.status, bv.beruf, bv.ort1, bv.tod_datum, bv.datum_geburtstag, bv.gruppe, bv.leibmitglied FROM base_person AS bs, base_person AS bv WHERE bs.id=:id AND bs.leibmitglied=bv.id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    printPersons($stmt, 0);
}


/*
* Beer sons
*/
$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person AS bs WHERE bs.leibmitglied = :leibmitglied");
$stmt->bindValue(':leibmitglied', $id, PDO::PARAM_INT);
$stmt->execute();
$stmt->bindColumn('number', $count);
$stmt->fetch();

if ($count > 0) {
    echo '<h2>Leibverhältnisse</h2>';

    $stmt = $libDb->prepare("SELECT bs.id, bs.anrede, bs.titel, bs.rang, bs.vorname, bs.praefix, bs.name, bs.suffix, bs.status, bs.beruf, bs.ort1, bs.tod_datum, bs.datum_geburtstag, bs.gruppe, bs.leibmitglied FROM base_person AS bs WHERE bs.leibmitglied=:leibmitglied");
    $stmt->bindValue(':leibmitglied', $id, PDO::PARAM_INT);
    printPersons($stmt, 0);
}


/*
* Co-fuchsia
*/
$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person AS confuchs, base_person AS ich WHERE confuchs.semester_reception = ich.semester_reception AND ich.id=:id AND confuchs.id!=:id2");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->bindValue(':id2', $id, PDO::PARAM_INT);
$stmt->execute();
$stmt->bindColumn('number', $count);
$stmt->fetch();

if ($count > 0) {
    echo '<h2>Confuchsen</h2>';

    $stmt = $libDb->prepare("SELECT confuchs.id, confuchs.anrede, confuchs.titel, confuchs.rang, confuchs.vorname, confuchs.praefix, confuchs.name, confuchs.suffix, confuchs.status, confuchs.beruf, confuchs.ort1, confuchs.tod_datum, confuchs.datum_geburtstag, confuchs.gruppe, confuchs.leibmitglied FROM base_person AS confuchs, base_person AS ich WHERE confuchs.semester_reception = ich.semester_reception AND ich.id=:id1 AND confuchs.id!=:id2");
    $stmt->bindValue(':id1', $id, PDO::PARAM_INT);
    $stmt->bindValue(':id2', $id, PDO::PARAM_INT);
    printPersons($stmt, 0);
}


/*
* Co-officers
*/
$stmt = $libDb->prepare("SELECT COUNT(*) AS number FROM base_semester WHERE
	base_semester.senior=:senior OR base_semester.consenior=:consenior OR base_semester.fuchsmajor=:fuchsmajor OR
	base_semester.fuchsmajor2=:fuchsmajor2 OR base_semester.scriptor=:scriptor OR base_semester.quaestor=:quaestor OR
	base_semester.jubelsenior=:jubelsenior OR base_semester.vop=:vop OR base_semester.vvop=:vvop OR
	base_semester.vopxx=:vopxx OR base_semester.vopxxx=:vopxxx OR base_semester.vopxxxx=:vopxxxx");
$stmt->bindValue(':senior', $id, PDO::PARAM_INT);
$stmt->bindValue(':consenior', $id, PDO::PARAM_INT);
$stmt->bindValue(':fuchsmajor', $id, PDO::PARAM_INT);
$stmt->bindValue(':fuchsmajor2', $id, PDO::PARAM_INT);
$stmt->bindValue(':scriptor', $id, PDO::PARAM_INT);
$stmt->bindValue(':quaestor', $id, PDO::PARAM_INT);
$stmt->bindValue(':jubelsenior', $id, PDO::PARAM_INT);
$stmt->bindValue(':vop', $id, PDO::PARAM_INT);
$stmt->bindValue(':vvop', $id, PDO::PARAM_INT);
$stmt->bindValue(':vopxx', $id, PDO::PARAM_INT);
$stmt->bindValue(':vopxxx', $id, PDO::PARAM_INT);
$stmt->bindValue(':vopxxxx', $id, PDO::PARAM_INT);
$stmt->execute();
$stmt->bindColumn('number', $count);
$stmt->fetch();


if ($count > 0) {
    echo '<h2>Conchargen</h2>';

    $stmt = $libDb->prepare("
SELECT senior.id, senior.anrede, senior.titel, senior.rang, senior.vorname, senior.praefix, senior.name, senior.suffix, senior.status, senior.beruf, senior.ort1, senior.tod_datum, senior.datum_geburtstag, senior.gruppe, senior.leibmitglied FROM base_person AS senior, base_semester WHERE senior.id = base_semester.senior AND base_semester.senior != :id AND (base_semester.senior = :id OR base_semester.consenior = :id OR base_semester.fuchsmajor = :id OR base_semester.fuchsmajor2 = :id OR base_semester.scriptor = :id OR base_semester.quaestor = :id OR base_semester.jubelsenior = :id)
UNION DISTINCT
SELECT consenior.id, consenior.anrede, consenior.titel, consenior.rang, consenior.vorname, consenior.praefix, consenior.name, consenior.suffix, consenior.status, consenior.beruf, consenior.ort1, consenior.tod_datum, consenior.datum_geburtstag, consenior.gruppe, consenior.leibmitglied FROM base_person AS consenior, base_semester WHERE consenior.id = base_semester.consenior AND base_semester.consenior != :id AND (base_semester.senior = :id OR base_semester.consenior = :id OR base_semester.fuchsmajor = :id OR base_semester.fuchsmajor2 = :id OR base_semester.scriptor = :id OR base_semester.quaestor = :id OR base_semester.jubelsenior = :id)
UNION DISTINCT
SELECT fuchsmajor.id, fuchsmajor.anrede, fuchsmajor.titel, fuchsmajor.rang, fuchsmajor.vorname, fuchsmajor.praefix, fuchsmajor.name, fuchsmajor.suffix, fuchsmajor.status, fuchsmajor.beruf, fuchsmajor.ort1, fuchsmajor.tod_datum, fuchsmajor.datum_geburtstag, fuchsmajor.gruppe, fuchsmajor.leibmitglied FROM base_person AS fuchsmajor, base_semester WHERE fuchsmajor.id = base_semester.fuchsmajor AND base_semester.fuchsmajor != :id AND (base_semester.senior = :id OR base_semester.consenior = :id OR base_semester.fuchsmajor = :id OR base_semester.fuchsmajor2 = :id OR base_semester.scriptor = :id OR base_semester.quaestor = :id OR base_semester.jubelsenior = :id)
UNION DISTINCT
SELECT fuchsmajor2.id, fuchsmajor2.anrede, fuchsmajor2.titel, fuchsmajor2.rang, fuchsmajor2.vorname, fuchsmajor2.praefix, fuchsmajor2.name, fuchsmajor2.suffix, fuchsmajor2.status, fuchsmajor2.beruf, fuchsmajor2.ort1, fuchsmajor2.tod_datum, fuchsmajor2.datum_geburtstag, fuchsmajor2.gruppe, fuchsmajor2.leibmitglied FROM base_person AS fuchsmajor2, base_semester WHERE fuchsmajor2.id = base_semester.fuchsmajor2 AND base_semester.fuchsmajor2 != :id AND (base_semester.senior = :id OR base_semester.consenior = :id OR base_semester.fuchsmajor = :id OR base_semester.fuchsmajor2 = :id OR base_semester.scriptor = :id OR base_semester.quaestor = :id OR base_semester.jubelsenior = :id)
UNION DISTINCT
SELECT scriptor.id, scriptor.anrede, scriptor.titel, scriptor.rang, scriptor.vorname, scriptor.praefix, scriptor.name, scriptor.suffix, scriptor.status, scriptor.beruf, scriptor.ort1, scriptor.tod_datum, scriptor.datum_geburtstag, scriptor.gruppe, scriptor.leibmitglied FROM base_person AS scriptor, base_semester WHERE scriptor.id = base_semester.scriptor AND base_semester.scriptor != :id AND (base_semester.senior = :id OR base_semester.consenior = :id OR base_semester.fuchsmajor = :id OR base_semester.fuchsmajor2 = :id OR base_semester.scriptor = :id OR base_semester.quaestor = :id OR base_semester.jubelsenior = :id)
UNION DISTINCT
SELECT quaestor.id, quaestor.anrede, quaestor.titel, quaestor.rang, quaestor.vorname, quaestor.praefix, quaestor.name, quaestor.suffix, quaestor.status, quaestor.beruf, quaestor.ort1, quaestor.tod_datum, quaestor.datum_geburtstag, quaestor.gruppe, quaestor.leibmitglied FROM base_person AS quaestor, base_semester WHERE quaestor.id = base_semester.quaestor AND base_semester.quaestor != :id AND (base_semester.senior = :id OR base_semester.consenior = :id OR base_semester.fuchsmajor = :id OR base_semester.fuchsmajor2 = :id OR base_semester.scriptor = :id OR base_semester.quaestor = :id OR base_semester.jubelsenior = :id)
UNION DISTINCT
SELECT jubelsenior.id, jubelsenior.anrede, jubelsenior.titel, jubelsenior.rang, jubelsenior.vorname, jubelsenior.praefix, jubelsenior.name, jubelsenior.suffix, jubelsenior.status, jubelsenior.beruf, jubelsenior.ort1, jubelsenior.tod_datum, jubelsenior.datum_geburtstag, jubelsenior.gruppe, jubelsenior.leibmitglied FROM base_person AS jubelsenior, base_semester WHERE jubelsenior.id = base_semester.jubelsenior AND base_semester.jubelsenior != :id AND (base_semester.senior = :id OR base_semester.consenior = :id OR base_semester.fuchsmajor = :id OR base_semester.fuchsmajor2 = :id OR base_semester.scriptor = :id OR base_semester.quaestor = :id OR base_semester.jubelsenior = :id)
UNION DISTINCT
SELECT vop.id, vop.anrede, vop.titel, vop.rang, vop.vorname, vop.praefix, vop.name, vop.suffix, vop.status, vop.beruf, vop.ort1, vop.tod_datum, vop.datum_geburtstag, vop.gruppe, vop.leibmitglied FROM base_person AS vop, base_semester WHERE vop.id = base_semester.vop AND base_semester.vop != :id AND (base_semester.vvop = :id OR base_semester.vopxx = :id OR base_semester.vopxxx = :id OR base_semester.vopxxxx = :id)
UNION DISTINCT
SELECT vvop.id, vvop.anrede, vvop.titel, vvop.rang, vvop.vorname, vvop.praefix, vvop.name, vvop.suffix, vvop.status, vvop.beruf, vvop.ort1, vvop.tod_datum, vvop.datum_geburtstag, vvop.gruppe, vvop.leibmitglied FROM base_person AS vvop, base_semester WHERE vvop.id = base_semester.vvop AND base_semester.vvop != :id AND (base_semester.vop = :id OR base_semester.vopxx = :id OR base_semester.vopxxx = :id OR base_semester.vopxxxx = :id)
UNION DISTINCT
SELECT vopxx.id, vopxx.anrede, vopxx.titel, vopxx.rang, vopxx.vorname, vopxx.praefix, vopxx.name, vopxx.suffix, vopxx.status, vopxx.beruf, vopxx.ort1, vopxx.tod_datum, vopxx.datum_geburtstag, vopxx.gruppe, vopxx.leibmitglied FROM base_person AS vopxx, base_semester WHERE vopxx.id = base_semester.vopxx AND base_semester.vopxx != :id AND (base_semester.vop = :id OR base_semester.vvop = :id OR base_semester.vopxxx = :id OR base_semester.vopxxxx = :id)
UNION DISTINCT
SELECT vopxxx.id, vopxxx.anrede, vopxxx.titel, vopxxx.rang, vopxxx.vorname, vopxxx.praefix, vopxxx.name, vopxxx.suffix, vopxxx.status, vopxxx.beruf, vopxxx.ort1, vopxxx.tod_datum, vopxxx.datum_geburtstag, vopxxx.gruppe, vopxxx.leibmitglied FROM base_person AS vopxxx, base_semester WHERE vopxxx.id = base_semester.vopxxx AND base_semester.vopxxx != :id AND (base_semester.vop = :id OR base_semester.vvop = :id OR base_semester.vopxx = :id OR base_semester.vopxxxx = :id)
UNION DISTINCT
SELECT vopxxxx.id, vopxxxx.anrede, vopxxxx.titel, vopxxxx.rang, vopxxxx.vorname, vopxxxx.praefix, vopxxxx.name, vopxxxx.suffix, vopxxxx.status, vopxxxx.beruf, vopxxxx.ort1, vopxxxx.tod_datum, vopxxxx.datum_geburtstag, vopxxxx.gruppe, vopxxxx.leibmitglied FROM base_person AS vopxxxx, base_semester WHERE vopxxxx.id = base_semester.vopxxxx AND base_semester.vopxxxx != :id AND (base_semester.vop = :id OR base_semester.vvop = :id OR base_semester.vopxx = :id OR base_semester.vopxxx = :id)
");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    printPersons($stmt, 0);
}


function printPersonSignature($row, $ownprofile)
{
    global $libPerson, $libForm;

    echo '<div class="d-block mx-auto person-signature-box mb-3">';
    echo '<div class="img-box">';

    if ($ownprofile) {
        echo '<span class="delete-icon-box">';
        echo '<form method="post" action="index.php?pid=intranet_person" class="d-inline" onsubmit="return confirm(\'Willst Du Dein Foto wirklich löschen?\')">';
        echo '<input type="hidden" name="action" value="photoDelete" />';
        echo '<button type="submit" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer"><i class="fa fa-trash" aria-hidden="true"></i></button>';
        echo '</form>';
        echo '</span>';
    }

    echo $libPerson->getImage($row['id'], 'lg');
    echo '</div>';

    echo $libPerson->getIntranetActivityBox($row['id']);
    echo '</div>';

    if ($ownprofile) {
        //image upload form
        echo '<form action="index.php?pid=intranet_person" method="post" enctype="multipart/form-data" class="text-center">';
        echo '<input type="hidden" name="formType" value="photoDataUpload" />';
        $libForm->printFileUpload('bilddatei', 'Foto (4x3) hochladen', false, false, [], ['image/jpeg']);
        echo '</form>';
    }
}

function printPersonData($row)
{
    global $libDb, $libPerson, $libTime, $libString;

    echo '<div>';
    echo '<div>';

    if ($row['anrede'] != '') {
        echo $libString->protectXSS($row['anrede']). ' ';
    }

    if ($row['titel'] != '') {
        echo $libString->protectXSS($row['titel']). ' ';
    }

    echo $libString->protectXSS($row['vorname']). ' ';

    if ($row['praefix'] != '') {
        echo $libString->protectXSS($row['praefix']). ' ';
    }

    echo $libString->protectXSS($row['name']);

    if ($row['suffix'] != '') {
        echo ' ' .$libString->protectXSS($row['suffix']);
    }

    if ($row['geburtsname'] != '') {
        echo ', geb. ';
        echo $libString->protectXSS($row['geburtsname']);
        echo ' ';
    }

    echo '</div>';

    if ($row['rang'] != '') {
        echo '<div>' .$libString->protectXSS($row['rang']). '</div>';
    }

    if ($row['spitzname'] != '') {
        echo '<div>Spitzname ' .$libString->protectXSS($row['spitzname']). '</div>';
    }

    if ($row['beruf'] != '') {
        echo '<div>' .$libString->protectXSS($row['beruf']). '</div>';
    }

    if ($row['gruppe'] != '') {
        echo '<div>';

        $stmt = $libDb->prepare('SELECT beschreibung FROM base_gruppe WHERE bezeichnung=:bezeichnung');
        $stmt->bindValue(':bezeichnung', $row['gruppe']);
        $stmt->execute();
        $stmt->bindColumn('beschreibung', $description);
        $stmt->fetch();

        echo $libString->protectXSS((string) $description);

        if ($row['status'] != '') {
            echo ', ' .$libString->protectXSS($row['status']);
        }

        echo '</div>';
    }

    if ($row['heirat_partner'] != '' && $row['heirat_partner'] != 0) {
        echo '<div>';
        echo 'Ehepartner <a href="index.php?pid=intranet_person&amp;id=' .(int) $row['heirat_partner']. '">' .$libString->protectXSS($libPerson->getNameString($row['heirat_partner'], 5)). '</a>';
        echo '</div>';
    }

    if ($row['datum_geburtstag'] != '') {
        echo '<div><i class="fa fa-birthday-cake fa-fw" aria-hidden="true"></i> ';
        echo $libTime->formatDateString($row['datum_geburtstag']);
        echo '</div>';
    }

    if ($row['tod_datum'] != '') {
        echo '<div><i class="fa fa-fw">&dagger;</i> ';
        echo $libTime->formatDateString($row['tod_datum']);
        echo '</div>';
    }

    echo '</div>';
}

function printPrimaryAddress($row)
{
    global $libTime, $libString;

    /*
    * primary address
    */
    if ($row['zusatz1'] != '' || $row['strasse1'] != '' || $row['ort1'] != '' || $row['plz1'] != '' || $row['land1'] != '' || $row['telefon1'] != '') {
        echo '<div class="col-sm-6">';
        echo '<hr />';
        echo '<address>';

        if ($row['zusatz1'] != '') {
            echo '<div>' .$libString->protectXSS($row['zusatz1']). '</div>';
        }

        if ($row['strasse1'] != '') {
            echo '<div>' .$libString->protectXSS($row['strasse1']). '</div>';
        }

        if ($row['plz1'] != '' || $row['ort1'] != '') {
            echo '<div>' .$libString->protectXSS($row['plz1']). ' ' .$libString->protectXSS($row['ort1']). '</div>';
        }

        if ($row['land1'] != '') {
            echo '<div>' .$libString->protectXSS($row['land1']). '</div>';
        }

        if ($row['telefon1'] != '') {
            echo '<div><i class="fa fa-phone fa-fw" aria-hidden="true"></i> ' .$libString->protectXSS($row['telefon1']). '</div>';
        }

        if ($row['datum_adresse1_stand'] != '') {
            echo '<div>Stand ' .$libTime->formatDateString($row['datum_adresse1_stand']). '</div>';
        }

        echo '</address>';
        echo '</div>';
    }
}

function printSecondaryAddress($row)
{
    global $libTime, $libString;

    /*
    * secondary address
    */
    if ($row['zusatz2'] != '' || $row['strasse2'] != '' || $row['ort2'] != '' || $row['plz2'] != '' || $row['land2'] != '' || $row['telefon2'] != '') {
        echo '<div class="col-sm-6">';
        echo '<hr />';
        echo '<address>';

        if ($row['zusatz2'] != '') {
            echo '<div>' .$libString->protectXSS($row['zusatz2']). '</div>';
        }

        if ($row['strasse2'] != '') {
            echo '<div>' .$libString->protectXSS($row['strasse2']). '</div>';
        }

        if ($row['plz2'] != '' || $row['ort2'] != '') {
            echo '<div>' .$libString->protectXSS($row['plz2']). ' ' .$libString->protectXSS($row['ort2']). '</div>';
        }

        if ($row['land2'] != '') {
            echo '<div>' .$libString->protectXSS($row['land2']). '</div>';
        }

        if ($row['telefon2'] != '') {
            echo '<div><i class="fa fa-phone fa-fw" aria-hidden="true"></i> ' .$libString->protectXSS($row['telefon2']). '</div>';
        }

        if ($row['datum_adresse2_stand'] != '') {
            echo '<div>Stand ' .$libTime->formatDateString($row['datum_adresse2_stand']). '</div>';
        }

        echo '</address>';
        echo '</div>';
    }
}

function printCommunication($row)
{
    global $libString;

    /*
    * communication
    */
    if ($row['email'] != '' || $row['mobiltelefon'] != '' || $row['webseite'] != '' ||  $row['skype'] != '') {
        echo '<hr />';
        echo '<div>';

        if ($row['email'] != '') {
            echo '<div><i class="fa fa-envelope-o fa-fw" aria-hidden="true"></i> <a href="mailto:' .$libString->protectXSS($row['email']). '">' .$libString->protectXSS($row['email']). '</a></div>';
        }

        if ($row['mobiltelefon'] != '') {
            echo '<div><i class="fa fa-mobile fa-fw" aria-hidden="true"></i> ' .$libString->protectXSS($row['mobiltelefon']). '</div>';
        }

        if ($row['webseite'] != '') {
            $website = $libString->assureHttpScheme($row['webseite']);

            $icon = '';

            if (strstr($website, 'linkedin')) {
                $icon = '<i class="fa fa-linkedin-square fa-fw" aria-hidden="true"></i>';
            } elseif (strstr($website, 'xing')) {
                $icon = '<i class="fa fa-xing-square fa-fw" aria-hidden="true"></i>';
            } elseif (strstr($website, 'twitter')) {
                $icon = '<i class="fa fa-twitter-square fa-fw" aria-hidden="true"></i>';
            } elseif (strstr($website, 'facebook')) {
                $icon = '<i class="fa fa-facebook-official fa-fw" aria-hidden="true"></i>';
            } elseif (strstr($website, 'wikipedia')) {
                $icon = '<i class="fa fa-wikipedia-w fa-fw" aria-hidden="true"></i>';
            } elseif (strstr($website, 'instagram')) {
                $icon = '<i class="fa fa-instagram fa-fw" aria-hidden="true"></i>';
            } elseif (strstr($website, 'github')) {
                $icon = '<i class="fa fa-github fa-fw" aria-hidden="true"></i>';
            } else {
                $icon = '<i class="fa fa-globe fa-fw" aria-hidden="true"></i>';
            }

            echo '<div>';
            echo $icon. ' <a href="' .$libString->protectXSS($website). '">' .$libString->protectXSS($website). '</a>';
            echo '</div>';
        }

        if ($row['skype'] != '') {
            echo '<div>';
            echo '<i class="fa fa-skype fa-fw" aria-hidden="true"></i> <a href="skype:' .$libString->protectXSS($row['skype']). '">' .$libString->protectXSS($row['skype']). '</a>';
            echo '</div>';
        }

        echo '</div>';
    }
}

function printAssociationDetails($row)
{
    global $libAssociation, $libDb, $libTime, $libModuleHandler, $libString;

    /*
    * others
    */
    if ($row['semester_reception'] != '' || $row['semester_promotion'] != '' || $row['semester_philistrierung'] != '' || $row['semester_aufnahme'] != '' || $row['semester_fusion'] != '') {
        echo '<hr />';
        echo '<div>';

        if ($row['semester_reception'] != '') {
            echo '<div>Reception ' .$libTime->getSemesterString($row['semester_reception']). '</div>';
        }

        if ($row['semester_promotion'] != '') {
            echo '<div>Promotion ' .$libTime->getSemesterString($row['semester_promotion']). '</div>';
        }

        if ($row['semester_philistrierung'] != '') {
            echo '<div>Philistrierung ' .$libTime->getSemesterString($row['semester_philistrierung']). '</div>';
        }

        if ($row['semester_aufnahme'] != '') {
            echo '<div>Aufnahme ' .$libTime->getSemesterString($row['semester_aufnahme']). '</div>';
        }

        if ($row['semester_fusion'] != '') {
            echo '<div>Fusion ' .$libTime->getSemesterString($row['semester_fusion']). '</div>';
        }

        echo '</div>';
    }

    if ($row['gruppe'] == 'F' || $row['gruppe'] == 'B' || $row['gruppe'] == 'P' || $row['gruppe'] == 'T') {
        if ($row['leibmitglied'] > 0) {
            echo '<div>Stammbaum <a href="index.php?pid=intranet_person_stammbaum&mitgliedid=' .$row['id']. '">öffnen</a></div>';
        }
    }

    /*
    * assocations
    */
    $stmt = $libDb->prepare('SELECT base_verein.id, base_verein.titel, base_verein.name, base_verein.dachverband, base_verein.ort1 FROM base_verein_mitgliedschaft, base_verein WHERE base_verein_mitgliedschaft.mitglied = :mitglied AND base_verein_mitgliedschaft.verein = base_verein.id');
    $stmt->bindValue(':mitglied', $row['id'], PDO::PARAM_INT);
    $stmt->execute();

    $associations = [];

    while ($associationRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $associationString = '<a href="index.php?pid=verein&amp;id=' .$associationRow['id']. '">';
        $associationString .= $libString->protectXSS($associationRow['titel']). ' ' .$libString->protectXSS($associationRow['name']);
        $associationString .= '</a>';

        $associations[] = $associationString;
    }

    $associationsCount = count($associations);

    if ($associationsCount > 0) {
        echo '<p class="mb-4">';
        echo '<span class="badge">' .$associationsCount. '</span>';
        echo ' ';
        echo 'Mitgliedschaften in weiteren Verbindungen: ' .implode(', ', $associations);
        echo '</p>';
    }

    /*
    * chargiert
    */
    if ($libModuleHandler->moduleIsAvailable('mod_intranet_chargierkalender')) {
        $stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM mod_chargierkalender_teilnahme WHERE mitglied = :mitglied');
        $stmt->bindValue(':mitglied', $row['id'], PDO::PARAM_INT);
        $stmt->execute();
        $stmt->bindColumn('number', $chargierCount);
        $stmt->fetch();

        if ($chargierCount > 0) {
            echo '<p class="mb-4">';
            echo '<span class="badge text-bg-secondary">' .$chargierCount. '</span>';
            echo ' ';
            echo 'Chargierter bei ';

            $stmt = $libDb->prepare('SELECT datum, beschreibung, verein FROM mod_chargierkalender_veranstaltung, mod_chargierkalender_teilnahme WHERE mod_chargierkalender_veranstaltung.id = mod_chargierkalender_teilnahme.chargierveranstaltung AND mod_chargierkalender_teilnahme.mitglied = :mitglied ORDER BY mod_chargierkalender_veranstaltung.datum DESC');
            $stmt->bindValue(':mitglied', $row['id'], PDO::PARAM_INT);
            $stmt->execute();

            $chargierEvents = [];

            while ($rowEvent = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $chargierEventStr = '';

                if (isset($rowEvent['verein']) && is_numeric($rowEvent['verein'])) {
                    $chargierEventStr .= '<a href="index.php?pid=verein&amp;id=' .$rowEvent['verein']. '">';
                    $chargierEventStr .= $libString->protectXSS($libAssociation->getAssociationNameString($rowEvent['verein']));
                    $chargierEventStr .= '</a>';
                } else {
                    $chargierEventStr .= $libString->protectXSS($rowEvent['beschreibung']);
                }

                $chargierEventStr .= ' (<time datetime="' .$libTime->formatUtcString($rowEvent['datum']). '">' .$libTime->formatYearString($rowEvent['datum']). '</time>)';

                $chargierEvents[] = $chargierEventStr;
            }

            echo implode(', ', $chargierEvents);
            echo '</p>';
        }
    }
}

function printVita($row)
{
    global $libString;

    echo '<article>';

    $vita = trim((string) $row['vita']);

    if ($vita != '') {
        echo nl2br($libString->protectXSS($vita));
    } else {
        echo 'Keine Vita erfasst.';
    }

    echo '</article>';
}
