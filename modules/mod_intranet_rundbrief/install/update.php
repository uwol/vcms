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

if (!is_object($libGlobal)) {
    exit();
}



/**
* Update table mod_rundbrief_brief
*/
$tableExists = false;

$stmt = $libDb->prepare('SHOW TABLES');
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    if ($row[0] == 'mod_rundbrief_brief') {
        $tableExists = true;
    }
}

if ($tableExists) {
    $libGlobal->notificationTexts[] = 'Aktualisiere Tabelle: mod_rundbrief_brief';

    $sql = "DROP TABLE mod_rundbrief_brief";
    $libDb->query($sql);
}


/**
* Update table mod_rundbrief_empfaenger
*/
$fieldExistsRecipient = false;
$fieldExistsInterested = false;
$fieldExistsShouldReceive = false;
$fieldExistsShouldReceiveInterestedAhAh = false;

$stmt = $libDb->prepare('SHOW COLUMNS FROM mod_rundbrief_empfaenger');
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($row['Field'] == 'sollempfangen') {
        $fieldExistsShouldReceive = true;
    } elseif ($row['Field'] == 'empfaenger') {
        $fieldExistsRecipient = true;
    } elseif ($row['Field'] == 'sollempfangen_interessierteahah') {
        $fieldExistsShouldReceiveInterestedAhAh = true;
    } elseif ($row['Field'] == 'interessiert') {
        $fieldExistsInterested = true;
    }
}

if ($fieldExistsShouldReceive) {
    $libGlobal->notificationTexts[] = 'Aktualisiere Tabelle: mod_rundbrief_empfaenger, benenne Spalte um';

    $sql = "ALTER TABLE mod_rundbrief_empfaenger CHANGE sollempfangen empfaenger tinyint(1) NOT NULL default '1'";
    $libDb->query($sql);
} elseif (!$fieldExistsRecipient) {
    $libGlobal->notificationTexts[] = 'Aktualisiere Tabelle: mod_rundbrief_empfaenger, füge Spalte hinzu';

    $sql = "ALTER TABLE mod_rundbrief_empfaenger ADD empfaenger tinyint(1) NOT NULL default '1'";
    $libDb->query($sql);
}

if ($fieldExistsShouldReceiveInterestedAhAh) {
    $libGlobal->notificationTexts[] = 'Aktualisiere Tabelle: mod_rundbrief_empfaenger, benenne Spalte um';

    $sql = "ALTER TABLE mod_rundbrief_empfaenger CHANGE sollempfangen_interessierteahah interessiert tinyint(1) NOT NULL default '0'";
    $libDb->query($sql);
} elseif (!$fieldExistsInterested) {
    $libGlobal->notificationTexts[] = 'Aktualisiere Tabelle: mod_rundbrief_empfaenger, füge Spalte hinzu';

    $sql = "ALTER TABLE mod_rundbrief_empfaenger ADD interessiert tinyint(1) NOT NULL default '0'";
    $libDb->query($sql);
}
