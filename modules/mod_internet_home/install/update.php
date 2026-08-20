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


if(!function_exists('vcmsMakeDateColumnNullable')){
	/**
	* Macht eine date- oder datetime-Spalte nullable ohne Default und konvertiert
	* bestehende Zero-Dates nach NULL. Kann beliebig oft ausgeführt werden.
	*/
	function vcmsMakeDateColumnNullable($table, $column, $type){
		global $libDb, $libGlobal;

		$definition = vcmsGetColumnDefinition($table, $column);

		if($definition === null){
			return;
		}

		//sql_mode entschärfen, damit bestehende Zero-Dates die Tabellenkopie überleben
		$previousSqlMode = null;
		$stmt = $libDb->query('SELECT @@SESSION.sql_mode AS sql_mode');

		if($stmt !== false){
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$previousSqlMode = $row['sql_mode'];

			$stmt = $libDb->prepare('SET SESSION sql_mode = :sql_mode');
			$stmt->bindValue(':sql_mode', 'ALLOW_INVALID_DATES');
			$stmt->execute();
		}

		if($definition['Null'] != 'YES' || $definition['Default'] !== null){
			$libGlobal->notificationTexts[] = 'Aktualisiere Spalte ' .$table. '.' .$column;
			$libDb->query('ALTER TABLE ' .$table. ' MODIFY ' .$column. ' ' .$type. ' NULL DEFAULT NULL');
		}

		//Zero-Dates nach NULL konvertieren; der Vergleich vermeidet das Zero-Literal
		$libDb->query('UPDATE ' .$table. ' SET ' .$column. ' = NULL WHERE ' .$column. " < '1000-01-01'");

		if($previousSqlMode !== null){
			$stmt = $libDb->prepare('SET SESSION sql_mode = :sql_mode');
			$stmt->bindValue(':sql_mode', $previousSqlMode);
			$stmt->execute();
		}

		//Ergebnis prüfen, da PDO im ERRMODE_SILENT läuft und Fehler sonst unsichtbar bleiben
		$definition = vcmsGetColumnDefinition($table, $column);

		if($definition !== null && $definition['Null'] != 'YES'){
			$libGlobal->errorTexts[] = 'Spalte ' .$table. '.' .$column. ' konnte nicht auf NULL umgestellt werden.';
		}
	}

	/**
	* Liefert die Definition einer Spalte (Field, Type, Null, Key, Default, Extra)
	* oder null, falls Tabelle oder Spalte nicht existieren.
	*/
	function vcmsGetColumnDefinition($table, $column){
		global $libDb;

		$stmt = $libDb->prepare('SHOW COLUMNS FROM ' .$table);

		if($stmt === false || !$stmt->execute()){
			return null;
		}

		$result = null;

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($row['Field'] == $column){
				$result = $row;
			}
		}

		return $result;
	}
}


vcmsMakeDateColumnNullable('mod_internethome_nachricht', 'startdatum', 'datetime');
vcmsMakeDateColumnNullable('mod_internethome_nachricht', 'verfallsdatum', 'datetime');
