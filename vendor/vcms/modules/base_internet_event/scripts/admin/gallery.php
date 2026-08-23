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


if (!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id']) || !preg_match("/^[0-9]+$/", $_REQUEST['id'])) {
    die('Id ist keine Zahl');
}


$id = '';

if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
}


if ($libGallery->hasFotowartPrivilege($libAuth->getOffices())) {
    //delete image
    if (isset($_POST['action']) && $_POST['action'] == 'deletePhoto') {
        if (is_numeric($id) && isset($_POST['bildnr']) && is_numeric($_POST['bildnr'])) {
            $pictures = $libGallery->getPictures($id, 2);

            if (isset($pictures[$_POST['bildnr']])) {
                $libImage->deleteEventPhoto($id, $pictures[$_POST['bildnr']]);
            }
        }
    }
    //rotate image
    elseif (isset($_POST['action']) && ($_POST['action'] == 'rotatePhotoRight' || $_POST['action'] == 'rotatePhotoLeft')) {
        if (is_numeric($id) && isset($_POST['bildnr']) && is_numeric($_POST['bildnr'])) {
            //rotation direction
            if ($_POST['action'] == 'rotatePhotoLeft') {
                $degree = 270;
            } else {
                $degree = 90;
            }

            $pictures = $libGallery->getPictures($id, 2);

            if (isset($pictures[$_POST['bildnr']])) {
                //rotate
                $libImage->rotateImage('custom/veranstaltungsfotos/' .$id. '/' .$pictures[$_POST['bildnr']], $degree);
            }
        }
    }
    //set as main image
    elseif (isset($_POST['action']) && $_POST['action'] == 'main') {
        if (is_numeric($id) && isset($_POST['bildnr']) && is_numeric($_POST['bildnr'])) {
            $libGallery->setPublicityLevel($id, $_POST['bildnr'], 'M');
        }
    }
    //publish image in internet
    elseif (isset($_POST['action']) && $_POST['action'] == 'public') {
        if (is_numeric($id) && isset($_POST['bildnr']) && is_numeric($_POST['bildnr'])) {
            $libGallery->setPublicityLevel($id, $_POST['bildnr'], 'E');
        }
    }
    //publish image in intranet
    elseif (isset($_POST['action']) && $_POST['action'] == 'intranet') {
        if (is_numeric($id) && isset($_POST['bildnr']) && is_numeric($_POST['bildnr'])) {
            $libGallery->setPublicityLevel($id, $_POST['bildnr'], 'I');
        }
    }
    //put image back in pool
    elseif (isset($_POST['action']) && $_POST['action'] == 'pool') {
        if (is_numeric($id) && isset($_POST['bildnr']) && is_numeric($_POST['bildnr'])) {
            $libGallery->setPublicityLevel($id, $_POST['bildnr'], 'P');
        }
    }
    //publish all images in internet
    elseif (isset($_POST['action']) && $_POST['action'] == 'publicAll') {
        if (is_numeric($id)) {
            $libGallery->setPublicityLevels($id, 'E');
        }
    }
    // publish all images in intranet
    elseif (isset($_POST['action']) && $_POST['action'] == 'intranetAll') {
        if (is_numeric($id)) {
            $libGallery->setPublicityLevels($id, 'I');
        }
    }
    //put all images back into pool
    elseif (isset($_POST['action']) && $_POST['action'] == 'poolAll') {
        if (is_numeric($id)) {
            $libGallery->setPublicityLevels($id, 'P');
        }
    }
}


//-------------------------------------------------------------------------------------------------

echo '<h1>Galerie - ' .$libString->protectXSS((string) $libEvent->getTitle($id)). '</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<div id="progress" class="progress">';
echo '<div class="progress-bar bg-success"></div>';
echo '</div>';

echo '<div id="files-success" role="alert" class="alert alert-success" style="display:none"></div>';
echo '<div id="files-danger" role="alert" class="alert alert-danger" style="display:none"></div>';

echo '<div class="mb-3">';
echo '<label class="btn btn-outline-secondary btn-file">';
echo '<i aria-hidden="true" class="fa fa-upload"></i> Fotos hochladen';
echo '<input id="fileupload" type="file" style="display:none" name="files[]" accept="image/jpeg,.jpg,.jpeg" multiple>';
echo '</label>';
echo '</div>';

echo '<script>
	document.addEventListener("DOMContentLoaded", function() {
		"use strict";

		var url = "api.php?iid=event_admin_galerie_upload&veranstaltungId=' .$id. '";
		var input = document.getElementById("fileupload");
		var progressBar = document.querySelector("#progress .progress-bar");

		if (!input) {
			return;
		}

		input.addEventListener("change", function() {
			var files = Array.prototype.slice.call(input.files || []);

			if (files.length === 0) {
				return;
			}

			var totalBytes = 0;
			var completedBytes = 0;

			files.forEach(function(file) {
				totalBytes += file.size;
			});

			// One request per file keeps a large selection below post_max_size, while the
			// progress bar still reports the progress of the whole selection.
			function showProgress(currentBytes) {
				if (progressBar && totalBytes > 0) {
					progressBar.style.width = Math.round((completedBytes + currentBytes) / totalBytes * 100) + "%";
				}
			}

			function reportFile(file) {
				var failed = typeof file.error !== "undefined";
				var box = document.getElementById(failed ? "files-danger" : "files-success");

				if (!box) {
					return;
				}

				var paragraph = document.createElement("p");
				paragraph.textContent = failed ? file.name + ": " + file.error : file.name;

				box.appendChild(paragraph);
				box.removeAttribute("style");
			}

			function uploadFile(index) {
				if (index >= files.length) {
					input.value = "";
					return;
				}

				var formData = new FormData();
				formData.append("files[]", files[index]);

				var xhr = new XMLHttpRequest();
				xhr.open("POST", url);
				xhr.responseType = "json";

				xhr.upload.addEventListener("progress", function(event) {
					if (event.lengthComputable) {
						showProgress(event.loaded);
					}
				});

				xhr.addEventListener("loadend", function() {
					var response = xhr.response;

					if (response && Array.isArray(response.files) && response.files.length > 0) {
						response.files.forEach(reportFile);
					} else {
						reportFile({name: files[index].name, error: "Der Upload ist fehlgeschlagen."});
					}

					completedBytes += files[index].size;
					showProgress(0);
					uploadFile(index + 1);
				});

				xhr.send(formData);
			}

			uploadFile(0);
		});
	});
	</script>';


echo '<p class="mb-4">Hochgeladene Fotos sind nach einer <a href="index.php?pid=event_admin_galerie&amp;id=' .$id. '">Aktualisierung</a> dieser Seite sichtbar.</p>';


if (is_dir('custom/veranstaltungsfotos/' .$id)) {
    if ($libGallery->hasFotowartPrivilege($libAuth->getOffices())) {
        echo '<hr />';

        echo '<form method="post" action="index.php?pid=event_admin_galerien" onsubmit="return confirm(\'Willst Du die Galerie wirklich löschen?\')">';
        echo '<input type="hidden" name="action" value="delete" />';
        echo '<input type="hidden" name="id" value="' .$id. '" />';
        echo '<button type="submit" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer"><i class="fa fa-trash" aria-hidden="true"></i> Komplette Galerie löschen</button>';
        echo '</form>';

        echo '<form method="post" action="index.php?pid=event_admin_galerie" class="mb-4">';
        echo '<input type="hidden" name="id" value="' .$id. '" />';
        echo '<button type="submit" name="action" value="publicAll" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer" onclick="return confirm(\'Willst Du die Galerie wirklich komplett veröffentlichen?\')"><i class="fa fa-users public" aria-hidden="true"></i> Sämtliche Bilder veröffentlichen</button><br />';
        echo '<button type="submit" name="action" value="intranetAll" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer" onclick="return confirm(\'Willst Du die Galerie wirklich komplett nur intern zugänglich machen?\')"><i class="fa fa-users internal" aria-hidden="true"></i> Bei sämtlichen Bildern Zugriff auf das Intranet beschränken</button><br />';
        echo '<button type="submit" name="action" value="poolAll" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer" onclick="return confirm(\'Willst Du die Galerie wirklich komplett in die Ablage zurücklegen?\')"><i class="fa fa-users private" aria-hidden="true"></i> Sämtliche Bilder in Ablage zurücklegen</button>';
        echo '</form>';
    }

    echo '<hr />';

    $pictures = $libGallery->getPictures($id, 2);
    $mainPictureId = $libGallery->getMainPictureId($id);

    if ($mainPictureId != -1) {
        echo '<div class="row gallery">';
        echo '<div class="col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-4 offset-lg-4">';
        echo '<div class="card card-img reveal">';
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

    foreach ($pictures as $key => $picture) {
        echo '<div class="col-sm-6 col-md-4 col-lg-3">';
        echo '<div class="card card-img reveal mb-2">';

        $visibility = $libGallery->getPublicityLevel($picture);
        $visibilityClass = '';

        if ($visibility == 0) {
            $visibilityClass = 'public';
        } elseif ($visibility == 1) {
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

        if ($libGallery->hasFotowartPrivilege($libAuth->getOffices())) {
            echo '<div class="controls mb-3">';

            echo '<form method="post" action="index.php?pid=event_admin_galerie">';
            echo '<input type="hidden" name="id" value="' .$id. '" />';
            echo '<input type="hidden" name="bildnr" value="' .$key. '" />';
            echo '<button type="submit" name="action" value="main" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer" title="Als Hauptbild verwenden"><i class="fa fa-home public" aria-hidden="true"></i></button> ';
            echo '| ';
            echo '<button type="submit" name="action" value="public" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer" title="Veröffentlichen"><i class="fa fa-users public" aria-hidden="true"></i></button> ';
            echo '<button type="submit" name="action" value="intranet" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer" title="Zugriff auf das Intranet beschränken"><i class="fa fa-users internal" aria-hidden="true"></i></button> ';
            echo '<button type="submit" name="action" value="pool" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer" title="In die Ablage zurücklegen"><i class="fa fa-users private" aria-hidden="true"></i></button> ';
            echo '| ';
            echo '<button type="submit" name="action" value="rotatePhotoLeft" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer" title="Nach links drehen"><i class="fa fa-undo" aria-hidden="true"></i></button> ';
            echo '<button type="submit" name="action" value="rotatePhotoRight" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer" title="Nach rechts drehen"><i class="fa fa-repeat" aria-hidden="true"></i></button> ';
            echo '<button type="submit" name="action" value="deletePhoto" class="p-0 border-0 bg-transparent align-baseline text-dark cursor-pointer" title="Löschen" onclick="return confirm(\'Willst Du das Bild wirklich löschen?\')"><i class="fa fa-trash" aria-hidden="true"></i></button>';
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
