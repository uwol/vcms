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

class LibTable{
	var $header = array();
	var $tableRows = array();
	var $libDb;

	function __construct(LibDb $libDb){
		$this->libDb = $libDb;
	}

	function addHeader($header){
		$this->header = $header;
	}

	function addTableByStatement($stmt){
		$rowindex = 0;
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_NUM)){
			$this->tableRows[$rowindex] = $row;
			$rowindex++;
		}
	}

	function addRowByArray($array){
		$this->tableRows[count($this->tableRows)] = $array;
	}

	function addRow($row){
		$this->tableRows[count($this->tableRows)] = $row;
	}

	function writeContentAsHtmlTable($filename){
		global $libString;

		$br = chr(13).chr(10);

   		header('Content-Type: application/octet-stream');
		header('Content-Type: application/force-download');
    	header('Content-Type: application/download');
		header('Content-Disposition: attachment; filename="' .$filename. '"');
		header('Content-Transfer-Encoding: binary');
		header('Pragma: no-cache');
    	header('Expires: 0');

		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?".">\n";
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"

       "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";

		echo '<html xmlns="http://www.w3.org/1999/xhtml">'."\n".'<head>'."\n".'<title>Tabelle</title>'."\n".'<meta http-equiv="content-type" content="text/xhtml; charset=UTF-8" />'."\n".'</head>'."\n";
		echo '<body><table>';

		//header
		if(is_array($this->header) && count($this->header) > 0){
			echo '<tr>'.$br.'<td>' .implode('</td>'.$br.'<td>', $this->header). '</td>'.$br.'</tr>'.$br;
		}

		//rows
		foreach($this->tableRows as $rowKey => $row){
			if(is_array($row) && count($row) > 0){
				echo '<tr>'.$br;

				foreach($row as $fieldKey => $field){
					echo '<td>'.$libString->xmlentities($field).'</td>'.$br;
				}

				echo '</tr>'.$br;
			}
		}

		echo '</table></body></html>';
	}

	function writeContentAsCSV($filename){
	    header('Content-Type: application/csv');
		header('Content-Type: application/force-download');
    	header('Content-Type: application/download');
	    header('Content-Disposition: attachment; filename="' .$filename. '"');
	    header('Content-Transfer-Encoding: binary');
	    header('Pragma: no-cache');
	    header('Expires: 0');

		$fp = fopen('php://output', 'w');

		if($fp){
			//header
			if(is_array($this->header) && count($this->header) > 0){
				fputcsv($fp, $this->header);
			}

			//rows
			foreach($this->tableRows as $rowKey => $row){
				if(is_array($row) && count($row) > 0){
					fputcsv($fp, array_values($row));
				}
			}

			fclose($fp);
		}
	}
}
?>