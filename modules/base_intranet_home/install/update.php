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

if(!is_object($libGlobal))
	exit();

/*
* secure rechtespalte.php
*/
$rechteSpaltePhpFilePath = 'modules/base_intranet_home/custom/rechtespalte.php';
$needle = '!is_object($libGlobal)';

if(is_file($rechteSpaltePhpFilePath)){
   	$handle = fopen($rechteSpaltePhpFilePath, 'r');
   	$content = fread($handle, filesize($rechteSpaltePhpFilePath));
   	fclose($handle);

   	$pos = strpos($content, $needle);
	if($pos === false) { // string needle NOT found in haystack -> update needed
		echo '<p>Die Datei ' . $rechteSpaltePhpFilePath . ' muss aus Sicherheitsgründen gepatcht werden.</p>';

		$securingString = '<?php if(!is_object($libGlobal)) exit(); ?>' . chr(13) . chr(10);

		$handle = @fopen($rechteSpaltePhpFilePath, 'w'); //write mode + set pointer to beginning of file
		fwrite($handle, $securingString);
		fwrite($handle, $content);
		fclose($handle);
		echo '<p>Patch durchgeführt.</p>';
	}
}
?>