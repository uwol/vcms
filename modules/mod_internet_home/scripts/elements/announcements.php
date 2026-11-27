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


$stmt = $libDb->prepare("SELECT * FROM mod_internethome_nachricht WHERE startdatum < NOW() AND (verfallsdatum > NOW() || verfallsdatum IS NULL || verfallsdatum = '0000-00-00 00:00:00') ORDER BY startdatum DESC LIMIT 0,2");
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo '<section class="announcement-box">';
    echo '<div class="ui-container">';
    echo '<div class="grid grid-cols-12 gap-x-6">';
    echo '<div class="col-span-12 lg:col-span-8 lg:col-start-3">';
    echo '<div class="ui-card ui-card-img">';

    $image = $libModuleHandler->getModuleDirectory(). '/custom/img/' .$row['id']. '.jpg';
    $imageExists = is_file($image);

    if ($imageExists) {
        echo '<img src="' .$image. '" class="max-w-full h-auto block mx-auto reveal" alt="" />';
    }

    echo '<p class="ui-card-body mb-0">';
    echo nl2br($libString->protectXSS(trim((string) $row['text'])));
    echo '</p>';

    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</section>';
}
