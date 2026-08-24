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

class LibForm
{
    public $colLabel = 3;

    public $colInput = 9;

    public function printDisabledString($disabled)
    {
        if ($disabled) {
            echo ' disabled';
        }
    }

    public function printRequiredString($required)
    {
        if ($required) {
            echo ' required';
        }
    }

    public function printClassesString($classes)
    {
        if (!empty($classes)) {
            echo ' ' .implode(' ', $classes);
        }
    }

    public function printAcceptString($accepts)
    {
        if (!empty($accepts)) {
            echo ' accept="' .implode(', ', $accepts). '"';
        }
    }

    public function printTextInput($name, $label, $value, $type = 'text', $disabled = false, $required = false, $classes = [])
    {
        global $libString;

        $value = $libString->protectXSS((string) $value);

        echo '<div class="row mb-3">';
        echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' col-form-label">' .$label. '</label>';
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

    public function printMinMaxString($min, $max)
    {
        if ($min != '') {
            echo ' min="' .$min. '"';
        }

        if ($max != '') {
            echo ' max="' .$max. '"';
        }
    }

    /**
    * HTML5 date field. The value is normalized to YYYY-MM-DD. Values that are not a
    * complete date (e.g. legacy data like 1985-00-00) are shown in a text field, so
    * that they remain visible and are not silently lost.
    */
    public function printDateInput($name, $label, $value, $disabled = false, $required = false, $classes = [], $min = '', $max = '')
    {
        global $libTime;

        $type = 'date';
        $htmlValue = $libTime->formatHtmlDateString($value);

        if ($htmlValue == '' && trim((string) $value) != '') {
            $type = 'text';
            $htmlValue = (string) $value;
            $min = '';
            $max = '';
        }

        $this->printDateTimeInputInternal($name, $label, $htmlValue, $type, $disabled, $required, $classes, $min, $max);
    }

    /**
    * HTML5 field for date and time. The value is normalized to YYYY-MM-DDTHH:MM,
    * fallback as in printDateInput.
    */
    public function printDateTimeInput($name, $label, $value, $disabled = false, $required = false, $classes = [], $min = '', $max = '')
    {
        global $libTime;

        $type = 'datetime-local';
        $htmlValue = $libTime->formatHtmlDateTimeString($value);

        if ($htmlValue == '' && trim((string) $value) != '') {
            $type = 'text';
            $htmlValue = (string) $value;
            $min = '';
            $max = '';
        }

        $this->printDateTimeInputInternal($name, $label, $htmlValue, $type, $disabled, $required, $classes, $min, $max);
    }

    public function printDateTimeInputInternal($name, $label, $value, $type, $disabled, $required, $classes, $min, $max)
    {
        global $libString;

        $value = $libString->protectXSS((string) $value);

        echo '<div class="row mb-3">';
        echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' col-form-label">' .$label. '</label>';
        echo '<div class="col-sm-' .$this->colInput. '">';
        echo '<input type="' .$type. '" id="' .$name. '" name="' .$name. '" value="' .$value. '"';

        $this->printMinMaxString($min, $max);
        $this->printDisabledString($disabled);
        $this->printRequiredString($required);

        echo ' class="form-control';

        $this->printClassesString($classes);

        echo '" />';
        echo '</div>';
        echo '</div>';
    }

    public function printTextarea($name, $label, $value, $disabled = false, $required = false, $classes = [])
    {
        global $libString;

        $value = $libString->protectXSS((string) $value);

        echo '<div class="row mb-3">';
        echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' col-form-label">' .$label. '</label>';
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

    public function printFileInput($name, $label, $disabled = false, $required = false, $classes = [], $accepts = [])
    {
        echo '<div class="row mb-3">';
        echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' col-form-label">' .$label. '</label>';
        echo '<div class="col-sm-' .$this->colInput. '">';
        echo '<label class="btn btn-outline-secondary btn-file';

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

    public function printFileUpload($name, $label, $disabled = false, $required = false, $classes = [], $accepts = [])
    {
        echo '<div class="row mb-3">';
        echo '<label class="btn btn-outline-secondary btn-file';

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

    public function printStaticText($label, $value, $disabled = false, $required = false, $classes = [])
    {
        global $libString;

        echo '<div class="row mb-3">';
        echo '<label class="col-sm-' .$this->colLabel. ' col-form-label">' .$label. '</label>';
        echo '<div class="col-sm-' .$this->colInput. '">';
        echo '<p class="form-control-plaintext mb-3';

        $this->printClassesString($classes);

        echo '">';
        echo $libString->protectXSS((string) $value);
        echo '</p>';
        echo '</div>';
        echo '</div>';
    }

    public function printSubmitButton($label, $classes = [])
    {
        echo '<div class="row mb-3">';
        echo '<div class="offset-sm-' .$this->colLabel. ' col-sm-' .$this->colInput. '">';
        echo '<button type="submit" class="btn btn-primary';

        $this->printClassesString($classes);

        echo '">' .$label. '</button>';
        echo '</div>';
        echo '</div>';
    }

    public function printSubmitButtonInline($label, $classes = [])
    {
        echo '<button type="submit" class="btn btn-outline-secondary';

        $this->printClassesString($classes);

        echo '">' .$label. '</button>';
    }

    public function printMembersDropDownBox($name, $label, $activeElementId = '', $allowNull = true, $disabled = false)
    {
        global $libDb, $libPerson, $libString;

        echo '<div class="row mb-3">';
        echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' col-form-label">' .$label. '</label>';
        echo '<div class="col-sm-' .$this->colInput. '"><select id="' .$name. '" name="' .$name. '"';

        $this->printDisabledString($disabled);

        echo ' class="form-select">';

        if ($allowNull) {
            echo '<option value=""></option>';
        }

        $stmt = $libDb->prepare("SELECT id, anrede, name, vorname, titel, rang, praefix, suffix, gruppe FROM base_person ORDER BY name, vorname");
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<option value="' .$row['id']. '"';

            if ($activeElementId == $row['id']) {
                echo ' selected="selected"';
            }

            echo '>' .$libString->protectXSS($libPerson->formatNameString($row['anrede'], $row['titel'], $row['rang'], $row['vorname'], $row['praefix'], $row['name'], $row['suffix'], 7)). ' [' .$libString->protectXSS((string) $row['gruppe']). ']</option>';
        }

        echo '</select></div>';
        echo '</div>';
    }

    public function printAssociationsDropDownBox($name, $label, $activeElementId = '', $allowNull = true, $disabled = false)
    {
        global $libDb, $libString;

        echo '<div class="row mb-3">';
        echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' col-form-label">' .$label. '</label>';
        echo '<div class="col-sm-' .$this->colInput. '"><select id="' .$name. '" name="' .$name. '"';

        $this->printDisabledString($disabled);

        echo ' class="form-select">';

        if ($allowNull) {
            echo '<option value=""></option>';
        }

        $stmt = $libDb->prepare("SELECT id, titel, name FROM base_verein ORDER BY name");
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<option value="' .$row['id']. '"';

            if ($activeElementId == $row['id']) {
                echo ' selected="selected"';
            }

            echo '>' .$libString->protectXSS($row['name']). ', ' .$libString->protectXSS((string) $row['titel']). '</option>';
        }

        echo '</select></div>';
        echo '</div>';
    }

    public function printSemesterDropDownBox($name, $label, $selectedSemester = '', $allowNull = true, $disabled = false)
    {
        global $libDb, $libString;

        echo '<div class="row mb-3">';
        echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' col-form-label">' .$label. '</label>';
        echo '<div class="col-sm-' .$this->colInput. '"><select id="' .$name. '" name="' .$name. '"';

        $this->printDisabledString($disabled);

        echo ' class="form-select">';

        if ($allowNull) {
            echo '<option value=""></option>';
        }

        $stmt = $libDb->prepare("SELECT semester FROM base_semester ORDER BY SUBSTRING(semester, 3) DESC");
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<option value="' .$libString->protectXSS($row['semester']). '"';

            if ($selectedSemester == $row['semester']) {
                echo ' selected="selected"';
            }

            echo '>' .$libString->protectXSS($row['semester']). '</option>';
        }

        echo '</select></div>';
        echo '</div>';
    }

    public function printStatusDropDownBox($name, $label, $selectedStatus = '', $allowNull = true, $disabled = false)
    {
        global $libDb, $libString;

        echo '<div class="row mb-3">';
        echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' col-form-label">' .$label. '</label>';
        echo '<div class="col-sm-' .$this->colInput. '"><select id="' .$name. '" name="' .$name. '"';

        $this->printDisabledString($disabled);

        echo ' class="form-select">';

        if ($allowNull) {
            echo '<option value=""></option>';
        }

        $stmt = $libDb->prepare("SELECT bezeichnung, beschreibung FROM base_status ORDER BY bezeichnung");
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<option value="' .$libString->protectXSS($row['bezeichnung']). '"';

            if ($selectedStatus == $row['bezeichnung']) {
                echo ' selected="selected"';
            }

            echo '>' .$libString->protectXSS($row['bezeichnung']). ' - ' .$libString->protectXSS((string) $row['beschreibung']). '</option>';
        }

        echo '</select></div>';
        echo '</div>';
    }

    public function printGroupDropDownBox($name, $label, $selectedGroup = '', $allowNull = true, $disabled = false)
    {
        global $libDb, $libString;

        echo '<div class="row mb-3">';
        echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' col-form-label">' .$label. '</label>';
        echo '<div class="col-sm-' .$this->colInput. '"><select id="' .$name. '" name="' .$name. '"';

        $this->printDisabledString($disabled);

        echo ' class="form-select">';

        if ($allowNull) {
            echo '<option value=""></option>';
        }

        $stmt = $libDb->prepare("SELECT bezeichnung, beschreibung FROM base_gruppe ORDER BY bezeichnung");
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<option value="' .$libString->protectXSS($row['bezeichnung']). '"';

            if ($selectedGroup == $row['bezeichnung']) {
                echo ' selected="selected"';
            }

            echo '>' .$libString->protectXSS($row['bezeichnung']). ' - ' .$libString->protectXSS((string) $row['beschreibung']). '</option>';
        }

        echo '</select></div>';
        echo '</div>';
    }

    public function printRegionDropDownBox($name, $label, $selectedRegion = '', $allowNull = true, $disabled = false)
    {
        global $libDb, $libString;

        echo '<div class="row mb-3">';
        echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' col-form-label">' .$label. '</label>';
        echo '<div class="col-sm-' .$this->colInput. '"><select id="' .$name. '" name="' .$name. '"';

        $this->printDisabledString($disabled);

        echo ' class="form-select">';

        if ($allowNull) {
            echo '<option value=""></option>';
        }

        $stmt = $libDb->prepare("SELECT id, bezeichnung FROM base_region ORDER BY bezeichnung");
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stmt2 = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person WHERE region1 = :region1 OR region2 = :region2");
            $stmt2->bindValue(':region1', $row['id'], PDO::PARAM_INT);
            $stmt2->bindValue(':region2', $row['id'], PDO::PARAM_INT);
            $stmt2->execute();
            $stmt2->bindColumn('number', $count);
            $stmt2->fetch();

            echo '<option value="' .$row['id']. '"';

            if ($selectedRegion == $row['id']) {
                echo ' selected="selected"';
            }

            echo '>' .$libString->protectXSS($row['bezeichnung']). ' [' .(int) $count. ' Personen]</option>';
        }

        echo '</select></div>';
        echo '</div>';
    }

    public function printEventDropDownBox($name, $label, $selectedEvent = '', $allowNull = true, $disabled = false)
    {
        global $libDb, $libString;

        echo '<div class="row mb-3">';
        echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' col-form-label">' .$label. '</label>';
        echo '<div class="col-sm-' .$this->colInput. '"><select id="' .$name. '" name="' .$name. '"';

        $this->printDisabledString($disabled);

        echo ' class="form-select">';

        if ($allowNull) {
            echo '<option value=""></option>';
        }

        $stmt = $libDb->prepare("SELECT id, titel, datum FROM base_veranstaltung ORDER BY datum DESC");
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<option value="' .$row['id']. '"';

            if ($selectedEvent == $row['id']) {
                echo ' selected="selected"';
            }

            echo '>' .$libString->protectXSS(substr((string) $row['titel'], 0, 25)). ' [' .$libString->protectXSS($row['datum']). ']</option>';
        }

        echo '</select></div>';
        echo '</div>';
    }

    public function printBoolSelectBox($name, $label, $selectedValue = 0)
    {
        echo '<div class="row mb-3">';
        echo '<label for="' .$name. '" class="col-sm-' .$this->colLabel. ' col-form-label">' .$label. '</label>';
        echo '<div class="col-sm-' .$this->colInput. '"><select name="' .$name. '" class="form-select">';
        echo '<option value="1"';

        if ($selectedValue > 0) {
            echo ' selected="selected"';
        }

        echo '>Ja</option>';
        echo '<option value="0"';

        if ($selectedValue == 0) {
            echo ' selected="selected"';
        }

        echo '>Nein</option>';
        echo '</select></div>';
        echo '</div>';
    }
}
