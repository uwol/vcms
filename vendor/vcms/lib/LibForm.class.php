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

namespace vcms;

use PDO;

class LibForm{

	var $colLabel = 2;
	var $colInput = 10;

	function printDisabledString($disabled){
		if($disabled){
			echo ' disabled';
		}
	}

	function printRequiredString($required){
		if($required){
			echo ' required';
		}
	}

	function printClassesString($classes){
		if(!empty($classes)){
			echo ' ' .implode(' ', $classes);
		}
	}

	function printAcceptString($accepts){
		if(!empty($accepts)){
			echo ' accept="' .implode(', ', $accepts). '"';
		}
	}

	function printTextInput($name, $label, $value, $type = 'text', $disabled = false, $required = false, $classes = array()){
		echo '<div class="form-group">';
		echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' control-label">' .$label. '</label>';
		echo '<div class="col-sm-' .$this->colInput. '">';
		echo '<input type="' .$type. '" id="' .$name. '" name="' .$name. '" value="' .$value. '"';

		$this->printDisabledString($disabled);
		$this->printRequiredString($required);

		echo ' class="form-control';

		$this->printClassesString($classes);

		echo '" />';
		echo '</div>';
		echo '</div>';
	}

	function printTextarea($name, $label, $value, $disabled = false, $required = false, $classes = array()){
		echo '<div class="form-group">';
		echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' control-label">' .$label. '</label>';
		echo '<div class="col-sm-' .$this->colInput. '">';
		echo '<textarea id="' .$name. '" name="' .$name. '" rows="10"';

		$this->printDisabledString($disabled);
		$this->printRequiredString($required);

		echo ' class="form-control';

		$this->printClassesString($classes);

		echo '">' .$value. '</textarea>';
		echo '</div>';
		echo '</div>';
	}

	function printFileInput($name, $label, $disabled = false, $required = false, $classes = array(), $accepts = array()){
		echo '<div class="form-group">';
		echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' control-label">' .$label. '</label>';
		echo '<div class="col-sm-10">';
		echo '<label class="btn btn-default btn-file';

		$this->printClassesString($classes);

		echo '">Datei wählen';
		echo '<input type="file" name="' .$name. '"';

		$this->printDisabledString($disabled);
		$this->printRequiredString($required);
		$this->printAcceptString($accepts);

		echo ' style="display:none">';
		echo '</label>';
		echo '</div>';
		echo '</div>';
	}

	function printFileUpload($name, $label, $disabled = false, $required = false, $classes = array(), $accepts = array()){
		echo '<div class="form-group">';
		echo '<label class="btn btn-default btn-file';

		$this->printClassesString($classes);

		echo '">' .$label;
		echo '<input type="file" name="' .$name. '" onchange="this.form.submit()"';

		$this->printDisabledString($disabled);
		$this->printRequiredString($required);
		$this->printAcceptString($accepts);

		echo ' style="display:none">';
		echo '</label>';
		echo '</div>';
	}

	function printStaticText($label, $value, $disabled = false, $required = false, $classes = array()){
		echo '<div class="form-group">';
		echo '<label class="col-sm-' .$this->colLabel. ' control-label">' .$label. '</label>';
		echo '<div class="col-sm-' .$this->colInput. '">';
		echo '<p class="form-control-static';

		$this->printClassesString($classes);

		echo '">';
		echo $value;
		echo '</p>';
		echo '</div>';
		echo '</div>';
	}

	function printSubmitButton($label, $classes = array()){
		echo '<div class="form-group">';
		echo '<div class="col-sm-offset-' .$this->colLabel. ' col-sm-' .$this->colInput. '">';
		echo '<button type="submit" class="btn btn-default hvr-forward';

		$this->printClassesString($classes);

		echo '">' .$label. '</button>';
		echo '</div>';
		echo '</div>';
	}

	function printSubmitButtonInline($label, $classes = array()){
		echo '<button type="submit" class="btn btn-default hvr-forward';

		$this->printClassesString($classes);

		echo '">' .$label. '</button>';
	}

	function printMitgliederDropDownBox($name, $label, $activeElementId = '', $allowNull = true, $disabled = false){
		global $libDb, $libPerson;

		echo '<div class="form-group">';
		echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' control-label">' .$label. '</label>';
		echo '<div class="col-sm-' .$this->colInput. '"><select id="' .$name. '" name="' .$name. '"';

		$this->printDisabledString($disabled);

		echo ' class="form-control">';

		if($allowNull){
			echo '<option value="">------------</option>';
		}

		$stmt = $libDb->prepare("SELECT id, anrede, name, vorname, titel, rang, praefix, suffix, gruppe FROM base_person ORDER BY name, vorname");
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			echo '<option value="' .$row['id']. '"';

			if($activeElementId == $row['id']){
				echo ' selected="selected"';
			}

			echo '>' .$libPerson->formatNameString($row['anrede'], $row['titel'], $row['rang'], $row['vorname'], $row['praefix'], $row['name'], $row['suffix'], 7). ' [' .$row['gruppe']. ']</option>';
		}

		echo '</select></div>';
		echo '</div>';
	}

	function printVereineDropDownBox($name, $label, $activeElementId = '', $allowNull = true, $disabled = false){
		global $libDb;

		echo '<div class="form-group">';
		echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' control-label">' .$label. '</label>';
		echo '<div class="col-sm-' .$this->colInput. '"><select id="' .$name. '" name="' .$name. '"';

		$this->printDisabledString($disabled);

		echo ' class="form-control">';

		if($allowNull){
			echo '<option value="">------------</option>';
		}

		$stmt = $libDb->prepare("SELECT id, titel, name FROM base_verein ORDER BY name");
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			echo '<option value="' .$row['id']. '"';

			if($activeElementId == $row['id']){
				echo ' selected="selected"';
			}

			echo '>' .$row['name']. ', ' .$row['titel']. '</option>';
		}

		echo '</select></div>';
		echo '</div>';
	}

	function printSemesterDropDownBox($name, $label, $selectedSemester = '', $allowNull = true, $disabled = false){
		global $libDb;

		echo '<div class="form-group">';
		echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' control-label">' .$label. '</label>';
		echo '<div class="col-sm-' .$this->colInput. '"><select id="' .$name. '" name="' .$name. '"';

		$this->printDisabledString($disabled);

		echo ' class="form-control">';

		if($allowNull){
			echo '<option value="">------------</option>';
		}

		$stmt = $libDb->prepare("SELECT semester FROM base_semester ORDER BY SUBSTRING(semester, 3) DESC");
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			echo '<option value="' .$row['semester']. '"';

			if($selectedSemester == $row['semester']){
				echo ' selected="selected"';
			}

			echo '>' .$row['semester']. '</option>';
		}

		echo '</select></div>';
		echo '</div>';
	}

	function printStatusDropDownBox($name, $label, $selectedStatus = '', $allowNull = true, $disabled = false){
		global $libDb;

		echo '<div class="form-group">';
		echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' control-label">' .$label. '</label>';
		echo '<div class="col-sm-' .$this->colInput. '"><select id="' .$name. '" name="' .$name. '"';

		$this->printDisabledString($disabled);

		echo ' class="form-control">';

		if($allowNull){
			echo '<option value="">------------</option>';
		}

		$stmt = $libDb->prepare("SELECT bezeichnung, beschreibung FROM base_status ORDER BY bezeichnung");
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			echo '<option value="' .$row['bezeichnung']. '"';

			if($selectedStatus == $row['bezeichnung']){
				echo ' selected="selected"';
			}

			echo '>' .$row['bezeichnung']. ' - ' .$row['beschreibung']. '</option>';
		}

		echo '</select></div>';
		echo '</div>';
	}

	function printGruppeDropDownBox($name, $label, $selectedGruppe = '', $allowNull = true, $disabled = false){
		global $libDb;

		echo '<div class="form-group">';
		echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' control-label">' .$label. '</label>';
		echo '<div class="col-sm-' .$this->colInput. '"><select id="' .$name. '" name="' .$name. '"';

		$this->printDisabledString($disabled);

		echo ' class="form-control">';

		if($allowNull){
			echo '<option value="">------------</option>';
		}

		$stmt = $libDb->prepare("SELECT bezeichnung, beschreibung FROM base_gruppe ORDER BY bezeichnung");
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			echo '<option value="' .$row['bezeichnung']. '"';

			if($selectedGruppe == $row['bezeichnung']){
				echo ' selected="selected"';
			}

			echo '>' .$row['bezeichnung']. ' - ' .$row['beschreibung']. '</option>';
		}

		echo '</select></div>';
		echo '</div>';
	}

	function printRegionDropDownBox($name, $label, $selectedRegion = '', $allowNull = true, $disabled = false){
		global $libDb;

		echo '<div class="form-group">';
		echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' control-label">' .$label. '</label>';
		echo '<div class="col-sm-' .$this->colInput. '"><select id="' .$name. '" name="' .$name. '"';

		$this->printDisabledString($disabled);

		echo ' class="form-control">';

		if($allowNull){
			echo '<option value="">------------</option>';
		}

		$stmt = $libDb->prepare("SELECT id, bezeichnung FROM base_region ORDER BY bezeichnung");
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$stmt2 = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person WHERE region1 = :region1 OR region2 = :region2");
			$stmt2->bindValue(':region1', $row['id'], PDO::PARAM_INT);
			$stmt2->bindValue(':region2', $row['id'], PDO::PARAM_INT);
			$stmt2->execute();
			$stmt2->bindColumn('number', $anzahl);
			$stmt2->fetch();

			echo '<option value="' .$row['id']. '"';

			if($selectedRegion == $row['id']){
				echo ' selected="selected"';
			}

			echo '>' .$row['bezeichnung']. ' [' .$anzahl. ' Personen]</option>';
		}

		echo '</select></div>';
		echo '</div>';
	}

	function printVeranstaltungDropDownBox($name, $label, $selectedVeranstaltung = '', $allowNull = true, $disabled = false){
		global $libDb;

		echo '<div class="form-group">';
		echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' control-label">' .$label. '</label>';
		echo '<div class="col-sm-' .$this->colInput. '"><select id="' .$name. '" name="' .$name. '"';

		$this->printDisabledString($disabled);

		echo ' class="form-control">';

		if($allowNull){
			echo '<option value="">------------</option>';
		}

		$stmt = $libDb->prepare("SELECT id, titel, datum FROM base_veranstaltung ORDER BY datum DESC");
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			echo '<option value="' .$row['id']. '"';

			if($selectedVeranstaltung == $row['id']){
				echo ' selected="selected"';
			}

			echo '>' .substr($row['titel'], 0, 25). ' [' .$row['datum']. ']</option>';
		}

		echo '</select></div>';
		echo '</div>';
	}

	function printBoolSelectBox($name, $label, $selectedValue = 0){
		echo '<div class="form-group">';
		echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' control-label">' .$label. '</label>';
		echo '<div class="col-sm-' .$this->colInput. '"><select name="' .$name. '" class="form-control">';
		echo '<option value="1"';

		if($selectedValue > 0){
			echo ' selected="selected"';
		}

		echo '>Ja</option>';
		echo '<option value="0"';

		if($selectedValue == 0){
			echo ' selected="selected"';
		}

		echo '>Nein</option>';
		echo '</select></div>';
		echo '</div>';
	}
}
