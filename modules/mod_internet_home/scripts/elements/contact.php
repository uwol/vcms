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


echo '<section class="contact-box">';
echo '<div class="container">';
echo '<div class="row">';
echo '<div class="col-lg-8 col-lg-offset-2 text-center">';
echo '<h1 class="section-heading">Kontakt</h1>';
echo '<hr>';
echo '<p>Interesse geweckt? Großartig! Melde Dich bei uns und wir antworten Dir schnellstmöglich.</p>';
echo '</div>';
echo '<div class="col-lg-4 col-lg-offset-2 text-center">';
echo '<i class="fa fa-phone fa-3x sr-contact"></i>';
echo '<p>' .$libConfig->verbindungTelefon. '</p>';
echo '</div>';
echo '<div class="col-lg-4 text-center">';
echo '<i class="fa fa-envelope-o fa-3x sr-contact"></i>';
echo '<p>' .$libConfig->emailInfo. '</p>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</section>';
?>