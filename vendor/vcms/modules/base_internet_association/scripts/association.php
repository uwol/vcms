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


if (isset($_GET['id'])) {
    $stmt = $libDb->prepare('SELECT * FROM base_verein WHERE id=:id');
    $stmt->bindValue(':id', $_GET['id'], PDO::PARAM_INT);
    $stmt->execute();
    $associationRow = $stmt->fetch(PDO::FETCH_ASSOC);

    echo '<h1>' .$libString->protectXSS($libAssociation->getAssociationNameString($associationRow['id'])). '</h1>';

    echo $libString->getErrorBoxText();
    echo $libString->getNotificationBoxText();

    echo '<div class="row">';
    echo '<div class="col-sm-9">';

    echo '<div class="card">';
    echo '<div class="card-body">';
    echo '<address class="mb-0">';

    if ($associationRow['zusatz1']) {
        echo $libString->protectXSS($associationRow['zusatz1']). '<br />';
    }

    if ($associationRow['strasse1']) {
        echo $libString->protectXSS($associationRow['strasse1']). '<br />';
    }

    if ($associationRow['ort1']) {
        echo $libString->protectXSS($associationRow['plz1']). ' ' .$libString->protectXSS($associationRow['ort1']). '<br />';
    }

    if ($associationRow['land1']) {
        echo $libString->protectXSS($associationRow['land1']). '<br />';
    }

    if ($associationRow['telefon1']) {
        echo $libString->protectXSS($associationRow['telefon1']). '<br />';
    }

    if ($associationRow['webseite']) {
        $website = $libString->assureHttpScheme($associationRow['webseite']);

        echo '<a href="' .$libString->protectXSS($website). '">' .$libString->protectXSS($website). '</a><br />';
    }

    echo '</address>';
    echo '</div>';
    echo '</div>';


    echo '<div class="card">';
    echo '<div class="card-body">';

    if ($associationRow['farbe1']) {
        echo '<div style="width:50px">';

        if ($associationRow['farbe1']) {
            echo '<div style="height:10px;background-color:' .$libAssociation->getColor($associationRow['farbe1']). '"></div>';
        }

        if ($associationRow['farbe2']) {
            echo '<div style="height:10px;background-color:' .$libAssociation->getColor($associationRow['farbe2']). '"></div>';
        }

        if ($associationRow['farbe3']) {
            echo '<div style="height:10px;background-color:' .$libAssociation->getColor($associationRow['farbe3']). '"></div>';
        }

        if ($associationRow['farbe4']) {
            echo '<div style="height:10px;background-color:' .$libAssociation->getColor($associationRow['farbe4']). '"></div>';
        }

        echo '</div>';

        echo '<p class="mb-4">';
        echo $libString->protectXSS($associationRow['farbe1']). ' ' .$libString->protectXSS($associationRow['farbe2']). ' ' .$libString->protectXSS($associationRow['farbe3']). '<br />';
        echo '</p>';
    }

    echo '<p class="mb-0">';

    if ($associationRow['datum_gruendung']) {
        echo 'Gründung ';
        echo $libAssociation->getFoundationString($associationRow['datum_gruendung']);
        echo '<br />';
    }

    if ($associationRow['dachverband']) {
        echo 'Dachverband: ' .$libString->protectXSS($associationRow['dachverband']). '<br />';
    }

    if ($associationRow['dachverbandnr']) {
        echo 'Nr.: ' .$libString->protectXSS($associationRow['dachverbandnr']). '<br />';
    }

    $activeString = '';

    if ($associationRow['aktivitas'] == 1) {
        $activeString = ' !';
    }

    if ($associationRow['kuerzel']) {
        echo 'Kürzel: ' .$libString->protectXSS($associationRow['kuerzel']) . $activeString. '<br />';
    }

    if ($associationRow['aktivitas'] == 1) {
        echo 'Aktivitas: Ja<br />';
    } else {
        echo 'Aktivitas: Nein<br />';
    }

    if ($associationRow['ahahschaft'] == 1) {
        echo 'Altherrenschaft: Ja<br />';
    } else {
        echo 'Altherrenschaft: Nein<br />';
    }

    if ($associationRow['mutterverein']) {
        echo 'Mutter: ';
        echo '<a href="index.php?pid=verein&amp;id=' .$associationRow['mutterverein']. '">';
        echo $libString->protectXSS($libAssociation->getAssociationNameString($associationRow['mutterverein'])). '</a>';
        echo '<br />';
    }

    if ($associationRow['fusioniertin']) {
        echo 'Fusioniert in: ';
        echo '<a href="index.php?pid=verein&amp;id=' .$associationRow['fusioniertin']. '">';
        echo $libString->protectXSS($libAssociation->getAssociationNameString($associationRow['fusioniertin'])). '</a>';
        echo '<br />';
    }

    $daughtersString = $libAssociation->getDaughtersString($associationRow['id'], 'verein');

    if ($daughtersString) {
        echo 'Töchter: ' .$daughtersString. '<br />';
    }

    $mergedString = $libAssociation->getMergedString($associationRow['id'], 'verein');

    if ($mergedString) {
        echo 'Fusioniert aus: ' .$mergedString. '<br />';
    }

    if ($associationRow['wahlspruch']) {
        echo 'Wahlspruch: ' .$libString->protectXSS($associationRow['wahlspruch']). '<br />';
    }

    echo '</p>';
    echo '</div>';
    echo '</div>';


    if ($associationRow['farbenstrophe']) {
        echo '<h3>Farbenstrophe</h3>';

        echo '<div class="card">';
        echo '<div class="card-body">';
        echo '<p class="mb-0">';
        echo nl2br($libString->protectXSS((string) $associationRow['farbenstrophe']));
        echo '</p>';
        echo '</div>';
        echo '</div>';
    }

    if ($associationRow['farbenstrophe_inoffiziell']) {
        echo '<h3>Inoffizielle Farbenstrophe</h3>';

        echo '<div class="card">';
        echo '<div class="card-body">';
        echo '<p class="mb-0">';
        echo nl2br($libString->protectXSS((string) $associationRow['farbenstrophe_inoffiziell']));
        echo '</p>';
        echo '</div>';
        echo '</div>';
    }

    if ($associationRow['fuchsenstrophe']) {
        echo '<h3>Fuchsenstrophe</h3>';

        echo '<div class="card">';
        echo '<div class="card-body">';
        echo '<p class="mb-0">';
        echo nl2br($libString->protectXSS((string) $associationRow['fuchsenstrophe']));
        echo '</p>';
        echo '</div>';
        echo '</div>';
    }

    if ($associationRow['bundeslied']) {
        echo '<h3>Bundeslied</h3>';

        echo '<div class="card">';
        echo '<div class="card-body">';
        echo '<p class="mb-0">';
        echo nl2br($libString->protectXSS((string) $associationRow['bundeslied']));
        echo '</p>';
        echo '</div>';
        echo '</div>';
    }

    if ($associationRow['beschreibung']) {
        echo '<div class="card">';
        echo '<div class="card-body">';
        echo '<p class="mb-0">';
        echo nl2br($libString->protectXSS((string) $associationRow['beschreibung']));
        echo '</p>';
        echo '</div>';
        echo '</div>';
    }

    echo '</div>';

    echo '<div class="col-sm-3">';
    echo '<div class="card">';
    echo '<div class="card-body">';

    /*
    * All three images are optional, so which one ends up last in the card body is only
    * known at runtime. They are collected first and the last one that is actually
    * rendered drops its bottom margin, because a margin on the last element of the card
    * body would stretch the card below the image.
    */
    $imagePaths = [];

    $filePathZirkelSvg = 'custom/vereine/zirkel/' .$associationRow['id']. '.svg';
    $filePathZirkelGif = 'custom/vereine/zirkel/' .$associationRow['id']. '.gif';

    if (is_file($filePathZirkelSvg)) {
        $imagePaths['Zirkel'] = $filePathZirkelSvg;
    } elseif (is_file($filePathZirkelGif)) {
        $imagePaths['Zirkel'] = $filePathZirkelGif;
    }

    $filePathWappenSvg = 'custom/vereine/wappen/' .$associationRow['id']. '.svg';
    $filePathWappenJpg = 'custom/vereine/wappen/' .$associationRow['id']. '.jpg';

    if (is_file($filePathWappenSvg)) {
        $imagePaths['Wappen'] = $filePathWappenSvg;
    } elseif (is_file($filePathWappenJpg)) {
        $imagePaths['Wappen'] = $filePathWappenJpg;
    }

    $filePathHausJpg = 'custom/vereine/haus/' .$associationRow['id']. '.jpg';

    if (is_file($filePathHausJpg)) {
        $imagePaths['Haus'] = $filePathHausJpg;
    }

    $lastImageAlt = array_key_last($imagePaths);

    foreach ($imagePaths as $imageAlt => $imagePath) {
        $marginClass = ($imageAlt === $lastImageAlt) ? 'mb-0' : 'mb-4';

        echo '<p class="' .$marginClass. '"><img src="' .$imagePath. '" alt="' .$imageAlt. '" class="img-fluid d-block mx-auto" /></p>';
    }

    echo '</div>';
    echo '</div>';
    echo '</div>';

    echo '</div>';


    $stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM base_verein_mitgliedschaft, base_person WHERE base_verein_mitgliedschaft.verein = :verein AND base_verein_mitgliedschaft.mitglied = base_person.id');
    $stmt->bindValue(':verein', $associationRow['id'], PDO::PARAM_INT);
    $stmt->execute();
    $stmt->bindColumn('number', $count);
    $stmt->fetch();

    if ($count > 0) {
        echo '<h2>Mitglieder</h2>';

        echo '<div class="card">';
        echo '<div class="card-body">';
        echo '<div class="persons-grid">';

        $stmt = $libDb->prepare('SELECT base_verein_mitgliedschaft.mitglied, base_verein_mitgliedschaft.ehrenmitglied, base_person.gruppe FROM base_verein_mitgliedschaft, base_person WHERE base_verein_mitgliedschaft.verein = :verein AND base_verein_mitgliedschaft.mitglied = base_person.id ORDER BY base_verein_mitgliedschaft.ehrenmitglied DESC, base_person.name ASC');
        $stmt->bindValue(':verein', $associationRow['id'], PDO::PARAM_INT);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<div class="persons-grid-element">';

            echo '<div>';
            echo $libPerson->getSignature($row['mitglied'], '');
            echo '</div>';

            echo '<div class="persons-grid-description">';
            echo $libString->protectXSS($libPerson->getNameString($row['mitglied'], 0));

            if ($row['ehrenmitglied'] == 1) {
                echo '<p class="mb-4">Ehrenmitglied</p>';
            }

            echo '</div>';
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}
