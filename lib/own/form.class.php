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

class LibForm{
	function getMitgliederDropDownBox($name, $bezeichnung, $activeElementId='', $allowNull = true, $disabled = false){
		global $libDb, $libMitglied;

		$retstr = '';
		$retstr .= '<select name="' .$name. '"';

		if($disabled){
			$retstr .= ' disabled ';
		}

		$retstr .= ' class="form-control">';

		if($allowNull){
			$retstr .= '<option value="">------------</option>'."\n";
		}

		$stmt = $libDb->prepare("SELECT id,anrede,name,vorname,titel,rang,praefix,suffix,gruppe FROM base_person ORDER BY name,vorname");
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$retstr .= '<option value="' .$row['id']. '"';

			if($activeElementId == $row['id']){
				$retstr .= ' selected="selected"';
			}

			$retstr .= '>' .$libMitglied->formatMitgliedNameString($row['anrede'],$row['titel'],$row['rang'],$row['vorname'],$row['praefix'],$row['name'],$row['suffix'],7).' ['.$row['gruppe'].']</option>'."\n";
		}

		$retstr .= '</select>';
		return $retstr;
	}

	function getVereineDropDownBox($name, $bezeichnung, $activeElementId='', $allowNull = true, $disabled = false){
		global $libDb;

		$retstr = '';
		$retstr .= '<select name="' .$name. '"';

		if($disabled){
			$retstr .= ' disabled ';
		}

		$retstr .= ' class="form-control">';

		if($allowNull){
			$retstr .= '<option value="">------------</option>'."\n";
		}

		$stmt = $libDb->prepare("SELECT id,titel,name FROM base_verein ORDER BY name");
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$retstr .= '<option value="' .$row['id']. '"';

			if($activeElementId == $row['id']){
				$retstr .= ' selected="selected"';
			}

			$retstr .= '>' .$row['titel'].' '.$row['name'].'</option>'."\n";
		}

		$retstr .= '</select>';
		return $retstr;
	}

	function getSemesterDropDownBox($name, $bezeichnung, $selectedSemester='', $allowNull = true, $disabled = false){
		global $libDb;

		$retstr = '';
		$retstr .= '<select name="' .$name. '"';

		if($disabled){
			$retstr .= ' disabled ';
		}

		$retstr .= ' class="form-control">';

		if($allowNull){
			$retstr .= '<option value="">------------</option>'."\n";
		}

		$stmt = $libDb->prepare("SELECT semester FROM base_semester ORDER BY SUBSTRING(semester,3) DESC");
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$retstr .= '<option value="' .$row['semester']. '"';

			if($selectedSemester == $row['semester']){
				$retstr .= ' selected="selected"';
			}

			$retstr .= '>'.$row['semester'].'</option>'."\n";
		}

		$retstr .= '</select>';
		return $retstr;
	}

	function getStatusDropDownBox($name, $bezeichnung, $selectedStatus='', $allowNull = true, $disabled = false){
		global $libDb;

		$retstr = '';
		$retstr .= '<select name="'.$name.'"';

		if($disabled){
			$retstr .= ' disabled';
		}

		$retstr .= ' class="form-control">';

		if($allowNull){
			$retstr .= '<option value="">------------</option>'."\n";
		}

		$stmt = $libDb->prepare("SELECT bezeichnung,beschreibung FROM base_status ORDER BY bezeichnung");
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$retstr .= '<option value="' .$row['bezeichnung']. '"';

			if($selectedStatus == $row['bezeichnung']){
				$retstr .= ' selected="selected"';
			}

			$retstr .= '>'.$row['bezeichnung'].' - ' .$row['beschreibung'].'</option>'."\n";
		}

		$retstr .= '</select>';
		return $retstr;
	}

	function getGruppeDropDownBox($name, $bezeichnung, $selectedGruppe='', $allowNull = true, $disabled = false){
		global $libDb;

		$retstr = '';
		$retstr .= '<select name="'.$name.'"';

		if($disabled){
			$retstr .= ' disabled';
		}

		$retstr .= ' class="form-control">';

		if($allowNull){
			$retstr .= '<option value="">------------</option>'."\n";
		}

		$stmt = $libDb->prepare("SELECT bezeichnung,beschreibung FROM base_gruppe ORDER BY bezeichnung");
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$retstr .= '<option value="' .$row['bezeichnung']. '"';

			if($selectedGruppe == $row['bezeichnung']){
				$retstr .= ' selected="selected"';
			}

			$retstr .= '>'.$row['bezeichnung'].' - ' .$row['beschreibung'].'</option>'."\n";
		}

		$retstr .= '</select>';
		return $retstr;
	}

	function getRegionDropDownBox($name, $bezeichnung, $selectedRegion='', $allowNull = true, $disabled = false){
		global $libDb;

		$retstr = '';
		$retstr .= '<select name="'.$name.'"';

		if($disabled){
			$retstr .= ' disabled';
		}

		$retstr .= ' class="form-control">';

		if($allowNull){
			$retstr .= '<option value="">------------</option>'."\n";
		}

		$stmt = $libDb->prepare("SELECT id,bezeichnung FROM base_region ORDER BY bezeichnung");
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$stmt2 = $libDb->prepare("SELECT COUNT(*) AS number FROM base_person WHERE region1 = :region1 OR region2 = :region2");
			$stmt2->bindValue(':region1', $row['id'], PDO::PARAM_INT);
			$stmt2->bindValue(':region2', $row['id'], PDO::PARAM_INT);
			$stmt2->execute();
			$stmt2->bindColumn('number', $anzahl);
			$stmt2->fetch();

			$retstr .= '<option value="' .$row['id']. '"';

			if($selectedRegion == $row['id']){
				$retstr .= ' selected="selected"';
			}

			$retstr .= '>'.$row['bezeichnung'].' [' .$anzahl. ' Personen]</option>'."\n";
		}

		$retstr .= '</select>';
		return $retstr;
	}

	function getVeranstaltungDropDownBox($name, $bezeichnung, $selectedRegion='', $allowNull = true, $disabled = false){
		global $libDb;

		$retstr = '';
		$retstr .= '<select name="'.$name.'"';

		if($disabled){
			$retstr .= ' disabled';
		}

		$retstr .= ' class="form-control">';

		if($allowNull){
			$retstr .= '<option value="">------------</option>'."\n";
		}

		$stmt = $libDb->prepare("SELECT id,titel,datum FROM base_veranstaltung ORDER BY datum DESC");
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$retstr .= '<option value="' .$row['id']. '"';

			if($selectedRegion == $row['id']){
				$retstr .= ' selected="selected"';
			}

			$retstr .= '>'.substr($row['titel'],0,25).' [' .$row['datum']. ']</option>'."\n";
		}

		$retstr .= '</select>';
		return $retstr;
	}

	function getBoolSelectBox($name, $bezeichnung, $selectedValue = 0){
		$retstr = '';
		$retstr .= '<select name="' .$name. '" class="form-control">';
		$retstr .= '<option value="1"';

		if($selectedValue == 1){
			$retstr .= ' selected="selected"';
		}

		$retstr .= '>Ja</option>';
		$retstr .= '<option value="0"';

		if($selectedValue == 0){
			$retstr .= ' selected="selected"';
		}

		$retstr .= '>Nein</option>';
		$retstr .= '</select>';
		return $retstr;
	}
}
?>