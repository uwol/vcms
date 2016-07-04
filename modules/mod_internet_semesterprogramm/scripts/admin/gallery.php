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


$libImage = new LibImage($libTime, $libGenericStorage);


if($libGallery->hasFotowartPrivilege($libAuth->getAemter())){
	//delete image
	if(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == 'deleteFoto'){
		if(is_numeric($id) && isset($_REQUEST['bildnr']) && is_numeric($_REQUEST['bildnr'])){
			$pictures = $libGallery->getPictures($id, 2);

			if(isset($pictures[$_REQUEST['bildnr']])){
				$libImage->deleteVeranstaltungsFoto($id, $pictures[$_REQUEST['bildnr']]);
			}
		}
	}
	//rotate image
	elseif(isset($_REQUEST['aktion']) && ($_REQUEST['aktion'] == 'rotateFotoRechts' || $_REQUEST['aktion'] == 'rotateFotoLinks')){
		if(is_numeric($id) && isset($_REQUEST['bildnr']) && is_numeric($_REQUEST['bildnr'])){
			//rotation direction
			if($_REQUEST['aktion'] == 'rotateFotoLinks'){
				$degree = 270;
			} else {
				$degree = 90;
			}

			$pictures = $libGallery->getPictures($id, 2);

			if(isset($pictures[$_REQUEST['bildnr']])){
				//rotate
				$libImage->rotateImage('custom/veranstaltungsfotos/'.$id.'/'.$pictures[$_REQUEST['bildnr']], $degree);
				$libImage->rotateImage('custom/veranstaltungsfotos/'.$id.'/thumbs/thumb_'.$pictures[$_REQUEST['bildnr']], $degree);
			}
		}
	}
	//publish image in internet
	elseif(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == 'oeffentlich'){
		if(is_numeric($id) && isset($_REQUEST['bildnr']) && is_numeric($_REQUEST['bildnr'])){
			$pictures = $libGallery->getPictures($id, 2);
			$filename = $pictures[$_REQUEST['bildnr']];
			$thumbfilename = 'thumb_'.$pictures[$_REQUEST['bildnr']];

			rename('custom/veranstaltungsfotos/'.$id.'/'.$filename, 'custom/veranstaltungsfotos/'.$id.'/'.$libGallery->changeVisibility($filename, 'E'));
			rename('custom/veranstaltungsfotos/'.$id.'/thumbs/'.$thumbfilename, 'custom/veranstaltungsfotos/'.$id.'/thumbs/'.$libGallery->changeVisibility($thumbfilename, 'E'));
		}
	}
	//publish image in intranet
	elseif(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == 'intranet'){
		if(is_numeric($id) && isset($_REQUEST['bildnr']) && is_numeric($_REQUEST['bildnr'])){
			$pictures = $libGallery->getPictures($id, 2);
			$filename = $pictures[$_REQUEST['bildnr']];
			$thumbfilename = 'thumb_'.$pictures[$_REQUEST['bildnr']];

			rename('custom/veranstaltungsfotos/'.$id.'/'.$filename, 'custom/veranstaltungsfotos/'.$id.'/'.$libGallery->changeVisibility($filename, 'I'));
			rename('custom/veranstaltungsfotos/'.$id.'/thumbs/'.$thumbfilename, 'custom/veranstaltungsfotos/'.$id.'/thumbs/'.$libGallery->changeVisibility($thumbfilename, 'I'));
		}
	}
	//put image back in pool
	elseif(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == 'pool'){
		if(is_numeric($id) && isset($_REQUEST['bildnr']) && is_numeric($_REQUEST['bildnr'])){
			$pictures = $libGallery->getPictures($id, 2);
			$filename = $pictures[$_REQUEST['bildnr']];
			$thumbfilename = 'thumb_'.$pictures[$_REQUEST['bildnr']];

			rename('custom/veranstaltungsfotos/'.$id.'/'.$filename, 'custom/veranstaltungsfotos/'.$id.'/'.$libGallery->changeVisibility($filename, 'P'));
			rename('custom/veranstaltungsfotos/'.$id.'/thumbs/'.$thumbfilename, 'custom/veranstaltungsfotos/'.$id.'/thumbs/'.$libGallery->changeVisibility($thumbfilename, 'P'));
		}
	}
	//publish all images in internet
	elseif(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == 'oeffentlichalle'){
		if(is_numeric($id)){
			$pictures = $libGallery->getPictures($id, 2);

			foreach($pictures as $key => $value){
				$filename = $pictures[$key];
				$thumbfilename = 'thumb_'.$pictures[$key];

				rename('custom/veranstaltungsfotos/'.$id.'/'.$filename, 'custom/veranstaltungsfotos/'.$id.'/'.$libGallery->changeVisibility($filename, 'E'));
				rename('custom/veranstaltungsfotos/'.$id.'/thumbs/'.$thumbfilename, 'custom/veranstaltungsfotos/'.$id.'/thumbs/'.$libGallery->changeVisibility($thumbfilename, 'E'));
			}
		}
	}
	// publish all images in intranet
	elseif(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == 'intranetalle'){
		if(is_numeric($id)){
			$pictures = $libGallery->getPictures($id, 2);

			foreach($pictures as $key => $value){
				$filename = $pictures[$key];
				$thumbfilename = 'thumb_'.$pictures[$key];

				rename('custom/veranstaltungsfotos/'.$id.'/'.$filename, 'custom/veranstaltungsfotos/'.$id.'/'.$libGallery->changeVisibility($filename, 'I'));
				rename('custom/veranstaltungsfotos/'.$id.'/thumbs/'.$thumbfilename, 'custom/veranstaltungsfotos/'.$id.'/thumbs/'.$libGallery->changeVisibility($thumbfilename, 'I'));
			}
		}
	}
	//put all images back in pool
	elseif(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == 'poolalle'){
		if(is_numeric($id)){
			$pictures = $libGallery->getPictures($id, 2);

			foreach($pictures as $key => $value){
				$filename = $pictures[$key];
				$thumbfilename = 'thumb_'.$pictures[$key];

				rename('custom/veranstaltungsfotos/'.$id.'/'.$filename, 'custom/veranstaltungsfotos/'.$id.'/'.$libGallery->changeVisibility($filename, 'P'));
				rename('custom/veranstaltungsfotos/'.$id.'/thumbs/'.$thumbfilename, 'custom/veranstaltungsfotos/'.$id.'/thumbs/'.$libGallery->changeVisibility($thumbfilename, 'P'));
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

		echo '<a href="index.php?pid=semesterprogramm_admin_galerienliste&amp;aktion=delete&amp;id=' .$id. '"  onclick="return confirm(\'Willst Du die Galerie wirklich löschen?\')"><i class="fa fa-trash" aria-hidden="true"></i> Komplette Galerie löschen</a>';

		echo '<p>';
		echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=oeffentlichalle&amp;id=' .$id. '" onclick="return confirm(\'Willst Du die Galerie wirklich komplett veröffentlichen?\')"><i class="fa fa-users public" aria-hidden="true"></i> Sämtliche Bilder veröffentlichen</a><br />';
		echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=intranetalle&amp;id=' .$id. '" onclick="return confirm(\'Willst Du die Galerie wirklich komplett nur intern zugänglich machen?\')"><i class="fa fa-users internal" aria-hidden="true"></i> Bei sämtlichen Bildern Zugriff auf das Intranet beschränken</a><br />';
		echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=poolalle&amp;id=' .$id. '" onclick="return confirm(\'Willst Du die Galerie wirklich komplett in die Ablage zurücklegen?\')"><i class="fa fa-users private" aria-hidden="true"></i> Sämtliche Bilder in Ablage zurücklegen</a>';
		echo '</p>';

		echo '<p>Nach dem Rotieren eines Fotos wird dieses evtl. erst nach einer Aktualisierung der Seite rotiert darstellt.</p>';
	}

	echo '<hr />';

	$pictures = $libGallery->getPictures($id,2);

	echo '<div class="row gallery">';

	foreach($pictures as $key => $picture){
		echo '<div class="col-sm-6 col-md-4 col-lg-3">';

		if($libGallery->hasFotowartPrivilege($libAuth->getAemter())){
			echo '<div class="thumbnailControls">';

			echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=oeffentlich&amp;id=' .$id. '&amp;bildnr=' .$key. '"><i class="fa fa-users public" aria-hidden="true"></i></a> ';
			echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=intranet&amp;id=' .$id. '&amp;bildnr=' .$key. '"><i class="fa fa-users internal" aria-hidden="true"></i></a> ';
			echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=pool&amp;id=' .$id. '&amp;bildnr=' .$key. '"><i class="fa fa-users private" aria-hidden="true"></i></a>';

			echo '<br />';

			echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=rotateFotoLinks&amp;id=' .$id. '&amp;bildnr=' .$key. '" onclick="return confirm(\'Willst Du das Bild wirklich drehen?\')"><i class="fa fa-undo" aria-hidden="true"></i></a> ';
			echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=rotateFotoRechts&amp;id=' .$id. '&amp;bildnr=' .$key. '" onclick="return confirm(\'Willst Du das Bild wirklich drehen?\')"><i class="fa fa-repeat" aria-hidden="true"></i></a> ';
			echo '<a href="index.php?pid=semesterprogramm_admin_galerie&amp;aktion=deleteFoto&amp;id=' .$id. '&amp;bildnr=' .$key. '" onclick="return confirm(\'Willst Du das Bild wirklich löschen?\')"><i class="fa fa-trash" aria-hidden="true"></i></a><br />';

			echo '</div>';
		}

		echo '<div class="thumbnail">';

		$visibility = $libGallery->getPublicityLevel($picture);
		$visibilityClass = '';

		if($visibility == 0){
			$visibilityClass = 'public';
		} elseif($visibility == 1){
			$visibilityClass = 'internal';
		} else {
			$visibilityClass = 'private';
		}

		echo '<div class="thumbnailOverflow ' .$visibilityClass. '">';
		echo '<a href="inc.php?iid=semesterprogramm_picture&amp;eventid=' .$id. '&amp;pictureid=' .$key. '">';
		echo '<img src="inc.php?iid=semesterprogramm_picture&amp;eventid=' .$id. '&amp;pictureid=' .$key. '" class="img-responsive center-block">';
		echo '</a>';
		echo '</div>';

		echo '</div>';
		echo '</div>';
	}

	echo '</div>';
}
?>