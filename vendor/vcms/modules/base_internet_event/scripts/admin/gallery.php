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

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();


if(!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id']) || !preg_match("/^[0-9]+$/", $_REQUEST['id']))
	die('Id ist keine Zahl');


$id = '';

if(isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])){
	$id = $_REQUEST['id'];
}


if($libGallery->hasFotowartPrivilege($libAuth->getAemter())){
	//delete image
	if(isset($_POST['aktion']) && $_POST['aktion'] == 'deleteFoto'){
		if(is_numeric($id) && isset($_POST['bildnr']) && is_numeric($_POST['bildnr'])){
			$pictures = $libGallery->getPictures($id, 2);

			if(isset($pictures[$_POST['bildnr']])){
				$libImage->deleteVeranstaltungsFoto($id, $pictures[$_POST['bildnr']]);
			}
		}
	}
	//rotate image
	elseif(isset($_POST['aktion']) && ($_POST['aktion'] == 'rotateFotoRechts' || $_POST['aktion'] == 'rotateFotoLinks')){
		if(is_numeric($id) && isset($_POST['bildnr']) && is_numeric($_POST['bildnr'])){
			//rotation direction
			if($_POST['aktion'] == 'rotateFotoLinks'){
				$degree = 270;
			} else {
				$degree = 90;
			}

			$pictures = $libGallery->getPictures($id, 2);

			if(isset($pictures[$_POST['bildnr']])){
				//rotate
				$libImage->rotateImage('custom/veranstaltungsfotos/' .$id. '/' .$pictures[$_POST['bildnr']], $degree);
			}
		}
	}
	//set as main image
	elseif(isset($_POST['aktion']) && $_POST['aktion'] == 'main'){
		if(is_numeric($id) && isset($_POST['bildnr']) && is_numeric($_POST['bildnr'])){
			$libGallery->setPublicityLevel($id, $_POST['bildnr'], 'M');
		}
	}
	//publish image in internet
	elseif(isset($_POST['aktion']) && $_POST['aktion'] == 'oeffentlich'){
		if(is_numeric($id) && isset($_POST['bildnr']) && is_numeric($_POST['bildnr'])){
			$libGallery->setPublicityLevel($id, $_POST['bildnr'], 'E');
		}
	}
	//publish image in intranet
	elseif(isset($_POST['aktion']) && $_POST['aktion'] == 'intranet'){
		if(is_numeric($id) && isset($_POST['bildnr']) && is_numeric($_POST['bildnr'])){
			$libGallery->setPublicityLevel($id, $_POST['bildnr'], 'I');
		}
	}
	//put image back in pool
	elseif(isset($_POST['aktion']) && $_POST['aktion'] == 'pool'){
		if(is_numeric($id) && isset($_POST['bildnr']) && is_numeric($_POST['bildnr'])){
			$libGallery->setPublicityLevel($id, $_POST['bildnr'], 'P');
		}
	}
	//publish all images in internet
	elseif(isset($_POST['aktion']) && $_POST['aktion'] == 'oeffentlichalle'){
		if(is_numeric($id)){
			$libGallery->setPublicityLevels($id, 'E');
		}
	}
	// publish all images in intranet
	elseif(isset($_POST['aktion']) && $_POST['aktion'] == 'intranetalle'){
		if(is_numeric($id)){
			$libGallery->setPublicityLevels($id, 'I');
		}
	}
	//put all images back into pool
	elseif(isset($_POST['aktion']) && $_POST['aktion'] == 'poolalle'){
		if(is_numeric($id)){
			$libGallery->setPublicityLevels($id, 'P');
		}
	}
}


//-------------------------------------------------------------------------------------------------

echo '<h1>Galerie - ' .$libEvent->getTitle($id). '</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<div id="progress" class="progress">';
echo '<div class="progress-bar progress-bar-success"></div>';
echo '</div>';

echo '<div id="files-success" role="alert" class="alert alert-success" style="display:none"></div>';
echo '<div id="files-danger" role="alert" class="alert alert-danger" style="display:none"></div>';

echo '<div class="form-group">';
echo '<label class="btn btn-default btn-file">';
echo '<i aria-hidden="true" class="fa fa-upload"></i> Fotos hochladen';
echo '<input id="fileupload" type="file" style="display:none" name="files[]" multiple>';
echo '</label>';
echo '</div>';

echo '<script src="vendor/blueimp-file-upload/js/vendor/jquery.ui.widget.js"></script>';
echo '<script src="vendor/blueimp-file-upload/js/jquery.iframe-transport.js"></script>';
echo '<script src="vendor/blueimp-file-upload/js/jquery.fileupload.js"></script>';
echo '<script>
	$(document).ready(function() {
		\'use strict\';

		var url = \'api.php?iid=event_admin_galerie_upload&veranstaltungId=' .$id. '\';

		$(\'#fileupload\').fileupload({
			url: url,
			dataType: \'json\',
			progressall: function (e, data) {
				var progress = parseInt(data.loaded / data.total * 100, 10);

				$(\'#progress .progress-bar\').css(
					\'width\',
					progress + \'%\'
				);
			},
			done: function (e, data) {
				$.each(data.result.files, function (index, file) {
					var id = \'#files-success\';
					var responseText = file.name;

					if(typeof file.error !== "undefined"){
						id = \'#files-danger\';
						responseText += \': \' + file.error;
					}

					var response = $(\'<p/>\').text(responseText).appendTo(id);
					$(id).removeAttr(\'style\');
				});
			}
		});
	});
	</script>';


echo '<p class="mb-4">Hochgeladene Fotos sind nach einer <a href="index.php?pid=event_admin_galerie&amp;id=' .$id. '">Aktualisierung</a> dieser Seite sichtbar.</p>';


if(is_dir('custom/veranstaltungsfotos/' .$id)){
	if($libGallery->hasFotowartPrivilege($libAuth->getAemter())){
		echo '<hr />';

		echo '<form method="post" action="index.php?pid=event_admin_galerien" onsubmit="return confirm(\'Willst Du die Galerie wirklich löschen?\')">';
		echo '<input type="hidden" name="aktion" value="delete" />';
		echo '<input type="hidden" name="id" value="' .$id. '" />';
		echo '<button type="submit" class="btn btn-link btn-icon"><i class="fa fa-trash" aria-hidden="true"></i> Komplette Galerie löschen</button>';
		echo '</form>';

		echo '<form method="post" action="index.php?pid=event_admin_galerie" class="mb-4">';
		echo '<input type="hidden" name="id" value="' .$id. '" />';
		echo '<button type="submit" name="aktion" value="oeffentlichalle" class="btn btn-link btn-icon" onclick="return confirm(\'Willst Du die Galerie wirklich komplett veröffentlichen?\')"><i class="fa fa-users public" aria-hidden="true"></i> Sämtliche Bilder veröffentlichen</button><br />';
		echo '<button type="submit" name="aktion" value="intranetalle" class="btn btn-link btn-icon" onclick="return confirm(\'Willst Du die Galerie wirklich komplett nur intern zugänglich machen?\')"><i class="fa fa-users internal" aria-hidden="true"></i> Bei sämtlichen Bildern Zugriff auf das Intranet beschränken</button><br />';
		echo '<button type="submit" name="aktion" value="poolalle" class="btn btn-link btn-icon" onclick="return confirm(\'Willst Du die Galerie wirklich komplett in die Ablage zurücklegen?\')"><i class="fa fa-users private" aria-hidden="true"></i> Sämtliche Bilder in Ablage zurücklegen</button>';
		echo '</form>';
	}

	echo '<hr />';

	$pictures = $libGallery->getPictures($id, 2);
	$mainPictureId = $libGallery->getMainPictureId($id);

	if($mainPictureId != -1){
		echo '<div class="row gallery">';
		echo '<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4">';
		echo '<div class="thumbnail reveal">';
		echo '<div class="img-frame">';
		echo '<a href="api.php?iid=event_picture&amp;eventid=' .$id. '&amp;id=' .$mainPictureId. '">';
		echo '<img src="api.php?iid=event_picture&amp;eventid=' .$id. '&amp;id=' .$mainPictureId. '">';
		echo '</a>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';

		echo '<hr />';
	}

	echo '<div class="row gallery">';

	foreach($pictures as $key => $picture){
		echo '<div class="col-sm-6 col-md-4 col-lg-3">';
		echo '<div class="thumbnail reveal mb-2">';

		$visibility = $libGallery->getPublicityLevel($picture);
		$visibilityClass = '';

		if($visibility == 0){
			$visibilityClass = 'public';
		} elseif($visibility == 1){
			$visibilityClass = 'internal';
		} else {
			$visibilityClass = 'private';
		}

		echo '<div class="img-frame">';
		echo '<a href="api.php?iid=event_picture&amp;eventid=' .$id. '&amp;id=' .$key. '">';
		echo '<img src="api.php?iid=event_picture&amp;eventid=' .$id. '&amp;id=' .$key. '" class="' .$visibilityClass. '">';
		echo '</a>';
		echo '</div>';

		echo '</div>';

		if($libGallery->hasFotowartPrivilege($libAuth->getAemter())){
			echo '<div class="controls mb-3">';

			echo '<form method="post" action="index.php?pid=event_admin_galerie">';
			echo '<input type="hidden" name="id" value="' .$id. '" />';
			echo '<input type="hidden" name="bildnr" value="' .$key. '" />';
			echo '<button type="submit" name="aktion" value="main" class="btn btn-link btn-icon" title="Als Hauptbild verwenden"><i class="fa fa-home public" aria-hidden="true"></i></button> ';
			echo '| ';
			echo '<button type="submit" name="aktion" value="oeffentlich" class="btn btn-link btn-icon" title="Veröffentlichen"><i class="fa fa-users public" aria-hidden="true"></i></button> ';
			echo '<button type="submit" name="aktion" value="intranet" class="btn btn-link btn-icon" title="Zugriff auf das Intranet beschränken"><i class="fa fa-users internal" aria-hidden="true"></i></button> ';
			echo '<button type="submit" name="aktion" value="pool" class="btn btn-link btn-icon" title="In die Ablage zurücklegen"><i class="fa fa-users private" aria-hidden="true"></i></button> ';
			echo '| ';
			echo '<button type="submit" name="aktion" value="rotateFotoLinks" class="btn btn-link btn-icon" title="Nach links drehen"><i class="fa fa-undo" aria-hidden="true"></i></button> ';
			echo '<button type="submit" name="aktion" value="rotateFotoRechts" class="btn btn-link btn-icon" title="Nach rechts drehen"><i class="fa fa-repeat" aria-hidden="true"></i></button> ';
			echo '<button type="submit" name="aktion" value="deleteFoto" class="btn btn-link btn-icon" title="Löschen" onclick="return confirm(\'Willst Du das Bild wirklich löschen?\')"><i class="fa fa-trash" aria-hidden="true"></i></button>';
			echo '</form>';

			echo '</div>';
		}

		echo '</div>';
	}

	echo '</div>';
} else {
	echo '<hr />';
	echo '<p class="mb-4">Die Fotos sind auf eine qualitativ hochwertige Auswahl zu beschränken. Es geht nicht um Vollständigkeit. Hochwertige Fotos bilden Personengruppen in einer ansprechenden Umgebung ab und sind gut belichtet.</p>';
}
