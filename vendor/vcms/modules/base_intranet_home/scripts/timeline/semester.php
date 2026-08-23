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


class LibSemesterTimelineEvent extends \vcms\timeline\LibTimelineEvent
{
    public function getBadgeClass()
    {
        return '';
    }

    public function isFullWidth()
    {
        return true;
    }
}


$stmt = $libDb->prepare('SELECT * FROM base_semester WHERE semester=:semester');
$stmt->bindValue(':semester', $libGlobal->semester);
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$title = $libTime->getSemesterString($row['semester']);
$url = 'index.php?pid=semesterprogramm&amp;semester=' .$row['semester'];
$description = '';


/**
* semester cover
*/
$description .= '<div class="row mb-4">';
$description .= '<div class="offset-sm-2 offset-md-3 col-sm-8 col-md-6">';
$description .= $libTime->getSemesterCoverString($row['semester']);
$description .= '</div>';
$description .= '</div>';

/**
* Board
*/
$description .= '<div class="row mb-4">';
$description .= '<div class="col-sm-1"></div>';
$description .= '<div class="col-sm-2">';

if ($row['senior']) {
    $description .= '<div class="row">';
    $description .= '<div class="col-6 col-sm-12">';

    $description .= '<a href="index.php?pid=intranet_person&amp;id=' .$row['senior']. '">';
    $description .= $libPerson->getSignature($row['senior'], '');
    $description .= '</a>';

    $description .= '</div>';
    $description .= '<div class="col-6 col-sm-12">';

    $description .= '<p class="mb-4">';
    $description .= 'Senior<br/>';
    $description .= '<a href="index.php?pid=intranet_person&amp;id=' .$row['senior']. '">';
    $description .= $libString->protectXSS($libPerson->getNameString($row['senior'], 0));
    $description .= '</a>';
    $description .= '</p>';

    $description .= '</div>';
    $description .= '</div>';
}

$description .= '</div>';
$description .= '<div class="col-sm-2">';

if ($row['consenior']) {
    $description .= '<div class="row">';
    $description .= '<div class="col-6 col-sm-12">';

    $description .= '<a href="index.php?pid=intranet_person&amp;id=' .$row['consenior']. '">';
    $description .= $libPerson->getSignature($row['consenior'], '');
    $description .= '</a>';

    $description .= '</div>';
    $description .= '<div class="col-6 col-sm-12">';

    $description .= '<p class="mb-4">';
    $description .= 'Consenior<br/>';
    $description .= '<a href="index.php?pid=intranet_person&amp;id=' .$row['consenior']. '">';
    $description .= $libString->protectXSS($libPerson->getNameString($row['consenior'], 0));
    $description .= '</a>';
    $description .= '</p>';

    $description .= '</div>';
    $description .= '</div>';
}

$description .= '</div>';
$description .= '<div class="col-sm-2">';

if ($row['fuchsmajor']) {
    $description .= '<div class="row">';
    $description .= '<div class="col-6 col-sm-12">';

    $description .= '<a href="index.php?pid=intranet_person&amp;id=' .$row['fuchsmajor']. '">';
    $description .= $libPerson->getSignature($row['fuchsmajor'], '');
    $description .= '</a>';

    $description .= '</div>';
    $description .= '<div class="col-6 col-sm-12">';

    $description .= '<p class="mb-4">';
    $description .= 'Fuchsmajor<br/>';
    $description .= '<a href="index.php?pid=intranet_person&amp;id=' .$row['fuchsmajor']. '">';
    $description .= $libString->protectXSS($libPerson->getNameString($row['fuchsmajor'], 0));
    $description .= '</a>';
    $description .= '</p>';

    $description .= '</div>';
    $description .= '</div>';
}

$description .= '</div>';
$description .= '<div class="col-sm-2">';

if ($row['scriptor']) {
    $description .= '<div class="row">';
    $description .= '<div class="col-6 col-sm-12">';

    $description .= '<a href="index.php?pid=intranet_person&amp;id=' .$row['scriptor']. '">';
    $description .= $libPerson->getSignature($row['scriptor'], '');
    $description .= '</a>';

    $description .= '</div>';
    $description .= '<div class="col-6 col-sm-12">';

    $description .= '<p class="mb-4">';
    $description .= 'Scriptor<br/>';
    $description .= '<a href="index.php?pid=intranet_person&amp;id=' .$row['scriptor']. '">';
    $description .= $libString->protectXSS($libPerson->getNameString($row['scriptor'], 0));
    $description .= '</a>';
    $description .= '</p>';

    $description .= '</div>';
    $description .= '</div>';
}

$description .= '</div>';
$description .= '<div class="col-sm-2">';

if ($row['quaestor']) {
    $description .= '<div class="row">';
    $description .= '<div class="col-6 col-sm-12">';

    $description .= '<a href="index.php?pid=intranet_person&amp;id=' .$row['quaestor']. '">';
    $description .= $libPerson->getSignature($row['quaestor'], '');
    $description .= '</a>';

    $description .= '</div>';
    $description .= '<div class="col-6 col-sm-12">';

    $description .= '<p class="mb-4">';
    $description .= 'Quaestor<br/>';
    $description .= '<a href="index.php?pid=intranet_person&amp;id=' .$row['quaestor']. '">';
    $description .= $libString->protectXSS($libPerson->getNameString($row['quaestor'], 0));
    $description .= '</a>';
    $description .= '</p>';

    $description .= '</div>';
    $description .= '</div>';
}

$description .= '</div>';
$description .= '<div class="col-sm-1"></div>';
$description .= '</div>';

/*
* Offices
*/

$description .= '<div>';

$description .= '<div class="row">';
$description .= '<div class="col-md-6">';

$description .= '<p class="mb-4">';
$description .= getOffice('Jubelsenior', $row['jubelsenior']);
$description .= getOffice('Fuchsmajor 2', $row['fuchsmajor2']);
$description .= '</p>';

$description .= '<p class="mb-4">';

/**
* Receptions
*/
$stmt2 = $libDb->prepare("SELECT id FROM base_person WHERE semester_reception=:semester");
$stmt2->bindValue(':semester', $row['semester']);

$description .= getAssociationGroup($stmt2, 'Receptionen');


/**
* Promotions
*/
$stmt2 = $libDb->prepare("SELECT id FROM base_person WHERE semester_promotion = :semester");
$stmt2->bindValue(':semester', $row['semester']);

$description .= getAssociationGroup($stmt2, 'Promotionen');


/**
* Philistriations
*/
$stmt2 = $libDb->prepare("SELECT id FROM base_person WHERE semester_philistrierung = :semester");
$stmt2->bindValue(':semester', $row['semester']);

$description .= getAssociationGroup($stmt2, 'Philistrierungen');


/**
* Admissions
*/
$stmt2 = $libDb->prepare("SELECT id FROM base_person WHERE semester_aufnahme = :semester");
$stmt2->bindValue(':semester', $row['semester']);

$description .= getAssociationGroup($stmt2, 'Aufnahmen');


/**
* Mergers
*/
$stmt2 = $libDb->prepare("SELECT id FROM base_person WHERE semester_fusion = :semester");
$stmt2->bindValue(':semester', $row['semester']);

$description .= getAssociationGroup($stmt2, 'Fusionierte');

$description .= '</p>';


/**
* other functions
*/
$description .= '<p class="mb-4">';
$description .= getOffice('VOP', $row['vop']);
$description .= getOffice('VVOP', $row['vvop']);
$description .= getOffice('VOPxx', $row['vopxx']);
$description .= getOffice('VOPxxx', $row['vopxxx']);
$description .= getOffice('VOPxxxx', $row['vopxxxx']);
$description .= '</p>';

$description .= '<p class="mb-4">';
$description .= getOffice('Senior Altherrenvorstand', $row['ahv_senior']);
$description .= getOffice('Consenior Altherrenvorstand', $row['ahv_consenior']);
$description .= getOffice('Keilbeauftragter', $row['ahv_keilbeauftragter']);
$description .= getOffice('Scriptor Altherrenvorstand', $row['ahv_scriptor']);
$description .= getOffice('Quaestor Altherrenvorstand', $row['ahv_quaestor']);
$description .= getOffice('Beisitzer 1 Altherrenvorstand', $row['ahv_beisitzer1']);
$description .= getOffice('Beisitzer 2 Altherrenvorstand', $row['ahv_beisitzer2']);
$description .= '</p>';

$description .= '<p class="mb-4">';
$description .= getOffice('Vorsitzender Hausverein', $row['hv_vorsitzender']);
$description .= getOffice('Kassierer Hausverein', $row['hv_kassierer']);
$description .= getOffice('Beisitzender 1 Hausverein', $row['hv_beisitzer1']);
$description .= getOffice('Beisitzender 2 Hausverein', $row['hv_beisitzer2']);
$description .= '</p>';

$description .= '</div>';
$description .= '<div class="col-md-6">';

$description .= '<p class="mb-4">';
$description .= getOffice('Ausflugswart', $row['ausflugswart']);
$description .= getOffice('Bierwart', $row['bierwart']);
$description .= getOffice('Bootshauswart', $row['bootshauswart']);
$description .= getOffice('Couleurartikelwart', $row['couleurartikelwart']);
$description .= getOffice('Datenpflegewart', $row['datenpflegewart']);
$description .= getOffice('Fechtwart', $row['fechtwart']);
$description .= getOffice('Fotowart', $row['fotowart']);
$description .= getOffice('Hauswart', $row['hauswart']);
$description .= getOffice('Hüttenwart', $row['huettenwart']);
$description .= getOffice('Internetwart', $row['internetwart']);
$description .= getOffice('Kühlschrankwart', $row['kuehlschrankwart']);
$description .= getOffice('Musikwart', $row['musikwart']);
$description .= getOffice('Redaktionswart', $row['redaktionswart']);
$description .= getOffice('Sportwart', $row['sportwart']);
$description .= getOffice('Stammtischwart', $row['stammtischwart']);
$description .= getOffice('Technikwart', $row['technikwart']);
$description .= getOffice('Thekenwart', $row['thekenwart']);
$description .= getOffice('Wichswart', $row['wichswart']);
$description .= getOffice('Wirtschaftskassenwart', $row['wirtschaftskassenwart']);

$description .= getOffice('Archivar', $row['archivar']);
$description .= getOffice('Dachverbandsberichterstatter', $row['dachverbandsberichterstatter']);
$description .= getOffice('Ferienordner', $row['ferienordner']);
$description .= '</p>';

$description .= '</div>';
$description .= '</div>';
$description .= '</div>';


$timelineEvent = new LibSemesterTimelineEvent();

$timelineEvent->setTitle($title);
$timelineEvent->setDatetime($period[0]);
$timelineEvent->setDescription($description);
$timelineEvent->setUrl($url);

$timelineEventSet->addEvent($timelineEvent);


function getAssociationGroup($stmt, $title)
{
    global $libPerson, $libString;

    $nameStrings = [];

    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $nameStrings[] = '<a href="index.php?pid=intranet_person&amp;id=' .$row['id']. '">' .$libString->protectXSS($libPerson->getNameString($row['id'], 0)). '</a>';
    }

    $retstr = '';

    if (count($nameStrings) > 0) {
        $retstr .= '<p class="mb-4">';
        $retstr .= $title. '<br/>';
        $retstr .= implode(', ', $nameStrings);
        $retstr .= '</p>';
    }

    return $retstr;
}

function getOffice($officeName, $id)
{
    global $libPerson, $libString;

    $retstr = '';

    if ($id != '') {
        $retstr .= '<p class="mb-4">';
        $retstr .= $officeName. '<br/>';
        $retstr .= '<a href="index.php?pid=intranet_person&amp;id=' .(int) $id. '">' .$libString->protectXSS($libPerson->getNameString($id, 0)). '</a>';
        $retstr .= '</p>';
    }

    return $retstr;
}
