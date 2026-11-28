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


$associationSchema = $libAssociation->getAssociationSchema();

echo '<script type="application/ld+json">';
echo str_replace(['<', '>', '&'], ['\u003c', '\u003e', '\u0026'], json_encode($associationSchema));
echo '</script>';

echo '<section class="contact-box">';
echo '<div class="ui-container">';
echo '<div class="grid grid-cols-12 gap-6">';
echo '<div class="text-center col-span-12 lg:col-span-8 lg:col-start-3 flex flex-col gap-4">';

echo '<h1 class="section-heading">Kontakt</h1>';
echo '<hr>';
echo '<p>Interesse geweckt? Großartig! Melde Dich bei uns und wir antworten Dir schnellstmöglich.</p>';
echo '</div>';
echo '<div class="text-center col-span-12 lg:col-span-4 lg:col-start-3">';
echo '<i class="fa fa-phone fa-3x sr-contact reveal"></i>';
echo '<p>' .$libConfig->verbindungTelefon. '</p>';
echo '</div>';
echo '<div class="text-center col-span-12 lg:col-span-4">';
echo '<i class="fa fa-envelope-o fa-3x sr-contact reveal"></i>';
echo '<p>' .$libConfig->emailInfo. '</p>';

echo '</div>';
echo '</div>';
echo '</div>';
echo '</section>';
