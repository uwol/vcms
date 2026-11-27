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

    /*
    * Tailwind only emits classes that occur literally in the scanned files, so the column
    * classes of the form rows are looked up instead of being concatenated from the widths.
    */
    private const COLUMN_SPAN_CLASSES = [
        1 => 'sm:col-span-1', 2 => 'sm:col-span-2', 3 => 'sm:col-span-3', 4 => 'sm:col-span-4',
        5 => 'sm:col-span-5', 6 => 'sm:col-span-6', 7 => 'sm:col-span-7', 8 => 'sm:col-span-8',
        9 => 'sm:col-span-9', 10 => 'sm:col-span-10', 11 => 'sm:col-span-11', 12 => 'sm:col-span-12',
    ];

    private const COLUMN_START_CLASSES = [
        1 => 'sm:col-start-1', 2 => 'sm:col-start-2', 3 => 'sm:col-start-3', 4 => 'sm:col-start-4',
        5 => 'sm:col-start-5', 6 => 'sm:col-start-6', 7 => 'sm:col-start-7', 8 => 'sm:col-start-8',
        9 => 'sm:col-start-9', 10 => 'sm:col-start-10', 11 => 'sm:col-start-11', 12 => 'sm:col-start-12',
    ];

    public function getLabelColumnClasses()
    {
        return 'col-span-12 ' .self::COLUMN_SPAN_CLASSES[$this->colLabel];
    }

    /**
    * With $offset the input column starts behind the label column, as for a submit button
    * without label.
    */
    public function getInputColumnClasses($offset = false)
    {
        $classes = 'col-span-12 ' .self::COLUMN_SPAN_CLASSES[$this->colInput];

        if ($offset) {
            $classes .= ' ' .self::COLUMN_START_CLASSES[$this->colLabel + 1];
        }

        return $classes;
    }

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

        echo '<div class="grid grid-cols-12 gap-x-6 mb-4">';
        echo '<label for="' .$name. '" class="' .$this->getLabelColumnClasses(). ' ui-col-form-label">' .$label. '</label>';
        echo '<div class="' .$this->getInputColumnClasses(). '">';
        echo '<input type="' .$type. '" id="' .$name. '" name="' .$name. '" value="' .$value. '"';

        $this->printDisabledString($disabled);
        $this->printRequiredString($required);

        echo ' class="ui-form-control';

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

        echo '<div class="grid grid-cols-12 gap-x-6 mb-4">';
        echo '<label for="' .$name. '" class="' .$this->getLabelColumnClasses(). ' ui-col-form-label">' .$label. '</label>';
        echo '<div class="' .$this->getInputColumnClasses(). '">';
        echo '<input type="' .$type. '" id="' .$name. '" name="' .$name. '" value="' .$value. '"';

        $this->printMinMaxString($min, $max);
        $this->printDisabledString($disabled);
        $this->printRequiredString($required);

        echo ' class="ui-form-control';

        $this->printClassesString($classes);

        echo '" />';
        echo '</div>';
        echo '</div>';
    }

    public function printTextarea($name, $label, $value, $disabled = false, $required = false, $classes = [])
    {
        global $libString;

        $value = $libString->protectXSS((string) $value);

        echo '<div class="grid grid-cols-12 gap-x-6 mb-4">';
        echo '<label for="' .$name. '" class="' .$this->getLabelColumnClasses(). ' ui-col-form-label">' .$label. '</label>';
        echo '<div class="' .$this->getInputColumnClasses(). '">';
        echo '<textarea id="' .$name. '" name="' .$name. '" rows="10"';

        $this->printDisabledString($disabled);
        $this->printRequiredString($required);

        echo ' class="ui-form-control';

        $this->printClassesString($classes);

        echo '">' .$value. '</textarea>';
        echo '</div>';
        echo '</div>';
    }

    public function printFileInput($name, $label, $disabled = false, $required = false, $classes = [], $accepts = [])
    {
        echo '<div class="grid grid-cols-12 gap-x-6 mb-4">';
        echo '<label for="' .$name. '" class="' .$this->getLabelColumnClasses(). ' ui-col-form-label">' .$label. '</label>';
        echo '<div class="' .$this->getInputColumnClasses(). '">';
        echo '<label class="ui-btn ui-btn-outline-secondary';

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
        echo '<div class="grid grid-cols-12 gap-x-6 mb-4">';
        echo '<label class="col-span-12 ui-btn ui-btn-outline-secondary';

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

        echo '<div class="grid grid-cols-12 gap-x-6 mb-4">';
        echo '<label class="' .$this->getLabelColumnClasses(). ' ui-col-form-label">' .$label. '</label>';
        echo '<div class="' .$this->getInputColumnClasses(). '">';
        echo '<p class="ui-form-control-plaintext mb-4';

        $this->printClassesString($classes);

        echo '">';
        echo $libString->protectXSS((string) $value);
        echo '</p>';
        echo '</div>';
        echo '</div>';
    }

    public function printSubmitButton($label, $classes = [])
    {
        echo '<div class="grid grid-cols-12 gap-x-6 mb-4">';
        echo '<div class="' .$this->getInputColumnClasses(true). '">';
        echo '<button type="submit" class="ui-btn ui-btn-outline-secondary';

        $this->printClassesString($classes);

        echo '">' .$label. '</button>';
        echo '</div>';
        echo '</div>';
    }

    public function printSubmitButtonInline($label, $classes = [])
    {
        echo '<button type="submit" class="ui-btn ui-btn-outline-secondary';

        $this->printClassesString($classes);

        echo '">' .$label. '</button>';
    }

    public function printMembersDropDownBox($name, $label, $activeElementId = '', $allowNull = true, $disabled = false)
    {
        global $libDb, $libPerson, $libString;

        echo '<div class="grid grid-cols-12 gap-x-6 mb-4">';
        echo '<label for="' .$name. '" class="' .$this->getLabelColumnClasses(). ' ui-col-form-label">' .$label. '</label>';
        echo '<div class="' .$this->getInputColumnClasses(). '"><select id="' .$name. '" name="' .$name. '"';

        $this->printDisabledString($disabled);

        echo ' class="ui-form-select">';

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

        echo '<div class="grid grid-cols-12 gap-x-6 mb-4">';
        echo '<label for="' .$name. '" class="' .$this->getLabelColumnClasses(). ' ui-col-form-label">' .$label. '</label>';
        echo '<div class="' .$this->getInputColumnClasses(). '"><select id="' .$name. '" name="' .$name. '"';

        $this->printDisabledString($disabled);

        echo ' class="ui-form-select">';

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

        echo '<div class="grid grid-cols-12 gap-x-6 mb-4">';
        echo '<label for="' .$name. '" class="' .$this->getLabelColumnClasses(). ' ui-col-form-label">' .$label. '</label>';
        echo '<div class="' .$this->getInputColumnClasses(). '"><select id="' .$name. '" name="' .$name. '"';

        $this->printDisabledString($disabled);

        echo ' class="ui-form-select">';

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

        echo '<div class="grid grid-cols-12 gap-x-6 mb-4">';
        echo '<label for="' .$name. '" class="' .$this->getLabelColumnClasses(). ' ui-col-form-label">' .$label. '</label>';
        echo '<div class="' .$this->getInputColumnClasses(). '"><select id="' .$name. '" name="' .$name. '"';

        $this->printDisabledString($disabled);

        echo ' class="ui-form-select">';

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

        echo '<div class="grid grid-cols-12 gap-x-6 mb-4">';
        echo '<label for="' .$name. '" class="' .$this->getLabelColumnClasses(). ' ui-col-form-label">' .$label. '</label>';
        echo '<div class="' .$this->getInputColumnClasses(). '"><select id="' .$name. '" name="' .$name. '"';

        $this->printDisabledString($disabled);

        echo ' class="ui-form-select">';

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

        echo '<div class="grid grid-cols-12 gap-x-6 mb-4">';
        echo '<label for="' .$name. '" class="' .$this->getLabelColumnClasses(). ' ui-col-form-label">' .$label. '</label>';
        echo '<div class="' .$this->getInputColumnClasses(). '"><select id="' .$name. '" name="' .$name. '"';

        $this->printDisabledString($disabled);

        echo ' class="ui-form-select">';

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

        echo '<div class="grid grid-cols-12 gap-x-6 mb-4">';
        echo '<label for="' .$name. '" class="' .$this->getLabelColumnClasses(). ' ui-col-form-label">' .$label. '</label>';
        echo '<div class="' .$this->getInputColumnClasses(). '"><select id="' .$name. '" name="' .$name. '"';

        $this->printDisabledString($disabled);

        echo ' class="ui-form-select">';

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
        echo '<div class="grid grid-cols-12 gap-x-6 mb-4">';
        echo '<label for="' .$name. '" class="' .$this->getLabelColumnClasses(). ' ui-col-form-label">' .$label. '</label>';
        echo '<div class="' .$this->getInputColumnClasses(). '"><select name="' .$name. '" class="ui-form-select">';
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
