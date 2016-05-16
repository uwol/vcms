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

if(!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id']) || !preg_match("/^[0-9]+$/", $_REQUEST['id']))
	die('Fehler: Veranstaltungsid ist keine Zahl');

$id = '';
if(isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])){
	$id = $_REQUEST['id'];
}

require_once($libModuleHandler->getModuleDirectory() . "/scripts/lib/gallery.class.php");

$libImage = new LibImage($libTime, $libGenericStorage);
$libGallery = new LibGallery();


if($libGallery->hasFotowartPrivilege($libAuth->getAemter())){
	//delete image
	if(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == "deleteFoto"){
		if(is_numeric($id) && isset($_REQUEST['bildnr']) && is_numeric($_REQUEST['bildnr'])){
			$pictures = $libGallery->getPictures($id, 2);

			if(isset($pictures[$_REQUEST['bildnr']])){
				$libImage->deleteVeranstaltungsFoto($id, $pictures[$_REQUEST['bildnr']]);
			}
		}
	}
	//rotate image
	elseif(isset($_REQUEST['aktion']) && ($_REQUEST['aktion'] == "rotateFotoRechts" || $_REQUEST['aktion'] == "rotateFotoLinks")){
		if(is_numeric($id) && isset($_REQUEST['bildnr']) && is_numeric($_REQUEST['bildnr'])){
			//rotation direction
			if($_REQUEST['aktion'] == "rotateFotoLinks"){
				$degree = 270;
			} else {
				$degree = 90;
			}

			$pictures = $libGallery->getPictures($id, 2);

			if(isset($pictures[$_REQUEST['bildnr']])){
				//rotate
				$libImage->rotateImage("custom/veranstaltungsfotos/".$id."/".$pictures[$_REQUEST['bildnr']], $degree);
				$libImage->rotateImage("custom/veranstaltungsfotos/".$id."/thumbs/thumb_".$pictures[$_REQUEST['bildnr']], $degree);
			}
		}
	}
	//publish image in internet
	elseif(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == "oeffentlich"){
		if(is_numeric($id) && isset($_REQUEST['bildnr']) && is_numeric($_REQUEST['bildnr'])){
			$pictures = $libGallery->getPictures($id, 2);
			$filename = $pictures[$_REQUEST['bildnr']];
			$thumbfilename = "thumb_".$pictures[$_REQUEST['bildnr']];

			rename("custom/veranstaltungsfotos/".$id."/".$filename, "custom/veranstaltungsfotos/".$id."/".$libGallery->changeVisibility($filename, "E"));
			rename("custom/veranstaltungsfotos/".$id."/thumbs/".$thumbfilename, "custom/veranstaltungsfotos/".$id."/thumbs/".$libGallery->changeVisibility($thumbfilename, "E"));
		}
	}
	//publish image in intranet
	elseif(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == "intranet"){
		if(is_numeric($id) && isset($_REQUEST['bildnr']) && is_numeric($_REQUEST['bildnr'])){
			$pictures = $libGallery->getPictures($id, 2);
			$filename = $pictures[$_REQUEST['bildnr']];
			$thumbfilename = "thumb_".$pictures[$_REQUEST['bildnr']];

			rename("custom/veranstaltungsfotos/".$id."/".$filename, "custom/veranstaltungsfotos/".$id."/".$libGallery->changeVisibility($filename, "I"));
			rename("custom/veranstaltungsfotos/".$id."/thumbs/".$thumbfilename, "custom/veranstaltungsfotos/".$id."/thumbs/".$libGallery->changeVisibility($thumbfilename, "I"));
		}
	}
	//put image back in pool
	elseif(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == "pool"){
		if(is_numeric($id) && isset($_REQUEST['bildnr']) && is_numeric($_REQUEST['bildnr'])){
			$pictures = $libGallery->getPictures($id, 2);
			$filename = $pictures[$_REQUEST['bildnr']];
			$thumbfilename = "thumb_".$pictures[$_REQUEST['bildnr']];

			rename("custom/veranstaltungsfotos/".$id."/".$filename, "custom/veranstaltungsfotos/".$id."/".$libGallery->changeVisibility($filename, "P"));
			rename("custom/veranstaltungsfotos/".$id."/thumbs/".$thumbfilename, "custom/veranstaltungsfotos/".$id."/thumbs/".$libGallery->changeVisibility($thumbfilename, "P"));
		}
	}
	//publish all images in internet
	elseif(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == "oeffentlichalle"){
		if(is_numeric($id)){
			$pictures = $libGallery->getPictures($id, 2);

			foreach($pictures as $key => $value){
				$filename = $pictures[$key];
				$thumbfilename = "thumb_".$pictures[$key];

				rename("custom/veranstaltungsfotos/".$id."/".$filename, "custom/veranstaltungsfotos/".$id."/".$libGallery->changeVisibility($filename, "E"));
				rename("custom/veranstaltungsfotos/".$id."/thumbs/".$thumbfilename, "custom/veranstaltungsfotos/".$id."/thumbs/".$libGallery->changeVisibility($thumbfilename, "E"));
			}
		}
	}
	// publish all images in intranet
	elseif(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == "intranetalle"){
		if(is_numeric($id)){
			$pictures = $libGallery->getPictures($id, 2);

			foreach($pictures as $key => $value){
				$filename = $pictures[$key];
				$thumbfilename = "thumb_".$pictures[$key];

				rename("custom/veranstaltungsfotos/".$id."/".$filename, "custom/veranstaltungsfotos/".$id."/".$libGallery->changeVisibility($filename, "I"));
				rename("custom/veranstaltungsfotos/".$id."/thumbs/".$thumbfilename, "custom/veranstaltungsfotos/".$id."/thumbs/".$libGallery->changeVisibility($thumbfilename, "I"));
			}
		}
	}
	//put all images back in pool
	elseif(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == "poolalle"){
		if(is_numeric($id)){
			$pictures = $libGallery->getPictures($id, 2);

			foreach($pictures as $key => $value){
				$filename = $pictures[$key];
				$thumbfilename = "thumb_".$pictures[$key];

				rename("custom/veranstaltungsfotos/".$id."/".$filename, "custom/veranstaltungsfotos/".$id."/".$libGallery->changeVisibility($filename, "P"));
				rename("custom/veranstaltungsfotos/".$id."/thumbs/".$thumbfilename, "custom/veranstaltungsfotos/".$id."/thumbs/".$libGallery->changeVisibility($thumbfilename, "P"));
			}
		}
	}
}


//-------------------------------------------------------------------------------------------------

echo '<h1>Galerie ' .$id. '</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();


//upload form based on http://valums.com/files/2010/file-uploader
echo '<div id="file-uploader">';
echo '<noscript>';
echo '<p>Für das Hochladen von Bildern aktiviere bitte JavaScript in Deinem Browser.</p>';
echo '</noscript>';
echo '</div>';

echo '<script src="styles/fileuploader/fileuploader.js"></script>';
echo '<script>
		function createUploader(){
			var uploader = new qq.FileUploader({
				element: document.getElementById(\'file-uploader\'),
				action: \'inc.php?iid=semesterprogramm_admin_galerie_upload\',
				allowedExtensions: [\'jpg\', \'jpeg\'],
				params: {
					veranstaltungId: ' .$id. '
				}
			});
		}
		window.onload = createUploader;
    </script>';

echo '<p>Nach dem Hochladen von Bildern sind diese durch eine <a href="index.php?pid=semesterprogramm_admin_galerie&amp;id=' .$id. '">Aktualisierung</a> dieser Seite sichtbar.</p>';


if(is_dir("custom/veranstaltungsfotos/" .$id)){
	if($libGallery->hasFotowartPrivilege($libAuth->getAemter())){
		echo '<hr />';

		echo '<a href="index.php?pid=semesterprogramm_admin_galerienliste&amp;aktion=delete&amp;id=' .$id. '"  onclick="return confirm(\'Willst Du die Galerie wirklich löschen?\')"><img src="styles/icons/basic/delete.svg" class="icon_small" /> Komplette Galerie löschen.</a>';

		echo '<p>';
		echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=oeffentlichalle&amp;id=' .$id. '" onclick="return confirm(\'Willst Du die Galerie wirklich komplett veröffentlichen?\')"><img src="styles/icons/image/public.svg" class="icon_small" /> Sämtliche Bilder veröffentlichen.</a><br />';
		echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=intranetalle&amp;id=' .$id. '" onclick="return confirm(\'Willst Du die Galerie wirklich komplett nur intern zugänglich machen?\')"><img src="styles/icons/image/internal.svg" class="icon_small" /> Bei sämtlichen Bildern Zugriff auf das Intranet beschränken.</a><br />';
		echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=poolalle&amp;id=' .$id. '" onclick="return confirm(\'Willst Du die Galerie wirklich komplett in die Ablage zurücklegen?\')"><img src="styles/icons/image/private.svg" class="icon_small" /> Sämtliche Bilder in Ablage zurücklegen.</a>';
		echo '</p>';

		echo '<p>Nach dem Rotieren eines Fotos wird dieses evtl. erst nach einer Aktualisierung der Seite rotiert darstellt.</p>';
	}

	echo '<hr />';

	$pictures = $libGallery->getPictures($id,2);

	echo '<div class="highslide-gallery galerie">';
	echo '<table><tr>';
	$i = 1;

	foreach($pictures as $key => $picture){
		echo '<td style="width:25%;text-align:center;padding:10px 10px 25px 10px">';

		if($libGallery->hasFotowartPrivilege($libAuth->getAemter())){
			echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=oeffentlich&amp;id=' .$id. '&amp;bildnr=' .$key. '"><img src="styles/icons/image/public.svg" class="icon_small" /></a>';
			echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=intranet&amp;id=' .$id. '&amp;bildnr=' .$key. '"><img src="styles/icons/image/internal.svg" class="icon_small" /></a>';
			echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=pool&amp;id=' .$id. '&amp;bildnr=' .$key. '"><img src="styles/icons/image/private.svg" class="icon_small" /></a><br />';

			echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=rotateFotoLinks&amp;id=' .$id. '&amp;bildnr=' .$key. '" onclick="return confirm(\'Willst Du das Bild wirklich drehen?\')"><img src="styles/icons/image/rotate-left.svg" class="icon_small" /></a>';
			echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=rotateFotoRechts&amp;id=' .$id. '&amp;bildnr=' .$key. '" onclick="return confirm(\'Willst Du das Bild wirklich drehen?\')"><img src="styles/icons/image/rotate-right.svg" class="icon_small" /></a>';
			echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=deleteFoto&amp;id=' .$id. '&amp;bildnr=' .$key. '" onclick="return confirm(\'Willst Du das Bild wirklich löschen?\')"><img src="styles/icons/basic/delete.svg" class="icon_small" /></a><br />';
		}

		echo '<a href="inc.php?iid=semesterprogramm_picture&amp;eventid=' .$id. '&amp;pictureid=' .$key. '" class="highslide" onclick="return hs.expand(this)">';
		echo '<img style="border-width: 3px; border-style: solid; border-color: ';

		$visibility = $libGallery->getPublicityLevel($picture);

		if($visibility == 0){
			echo 'green';
		} elseif($visibility == 1){
			echo 'yellow';
		} else {
			echo 'red';
		}

		echo ';" src="inc.php?iid=semesterprogramm_picture&amp;eventid=' .$id. '&amp;pictureid=' .$key. '&amp;thumb=1">';
		echo '</a>';
		echo '</td>';

		if($i % 4 == 0){
			echo '</tr><tr>';
		}

		$i++;
	}

	echo '</tr></table>';
	echo '</div>';
}
?>