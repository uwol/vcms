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

if(!is_object($libGlobal))
	exit();


$associationSchema = $libAssociation->getAssociationSchema();

echo '<script type="application/ld+json">';
echo json_encode($associationSchema);
echo '</script>';

echo '<section class="contact-box">';
echo '<div class="container">';
echo '<div class="row">';
echo '<div class="col-lg-8 offset-lg-2 text-center">';

echo '<h1 class="section-heading">Kontakt</h1>';
echo '<hr>';
echo '<p class="mb-4">Interesse geweckt? Großartig! Melde Dich bei uns und wir antworten Dir schnellstmöglich.</p>';
echo '</div>';
echo '<div class="col-lg-4 offset-lg-2 text-center">';
echo '<a href="tel:' .filter_var($libConfig->verbindungTelefon, FILTER_SANITIZE_NUMBER_INT). '"><i class="fa fa-phone fa-3x sr-contact reveal"></i>';
echo '<p class="mb-4">' .$libConfig->verbindungTelefon. '</p></a>';
echo '</div>';
echo '<div class="col-lg-4 text-center">';
echo '<a href="mailto:ENTFERNEDASVORDEMSENDEN+'.$libConfig->emailInfo.'"><i class="fa fa-envelope-o fa-3x sr-contact reveal"></i>';
echo '<p class="mb-4"><span style="display:none">ENTFERNEDASVORDEMSENDEN+</span>' .$libConfig->emailInfo. '</p></a>';

echo '</div>';
echo '</div>';
echo '</div>';
echo '</section>';
