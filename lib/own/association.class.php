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

class LibAssociation{
	var $libTime;
	var $libDb;

	function __construct(LibDb $libDb, LibTime $libTime){
		$this->libTime = $libTime;
		$this->libDb = $libDb;
	}

	function getFarbe($farbe){
		$farben['blau']          = '0000ff';
		$farben['dunkelblau']    = '000033';
		$farben['dunkelgrün']    = '003300';
		$farben['flieder']       = '336699';
		$farben['gelb']          = 'ffff00';
		$farben['gold']          = 'ffd700';
		$farben['grün']          = '009900';
		$farben['hellblau']      = '0066ff';
		$farben['hellgrün']      = '00ff00';
		$farben['hellrot']       = 'ff0000';
		$farben['himmelblau']    = '0066ff';
		$farben['moosgrün']      = '66ff66';
		$farben['orange']        = 'ff6600';
		$farben['purpur']        = '660066';
		$farben['rosa']          = 'ff99cc';
		$farben['rot']           = 'ff0000';
		$farben['schwarz']       = '000000';
		$farben['silber']        = 'C0C0C0';
		$farben['violett']       = 'B200CC';
		$farben['weinrot']       = '660000';
		$farben['weiß']          = 'FFFFFF';
		$farben['weiss']         = $farben['weiß'];
		$farben['zinnoberrot']   = 'cc0000';
		$farben['karmesinrot']   = '960018';
		$farben['grau']          = '808080';
		$farben['braun']         = 'A52A2A';
		$farben['saatgrün']      = '00FF00';
		$farben['kirschrot']     = 'FF0000';
		$farben['stahlblau']     = '30406A';
		$farben['purpur']        = 'FF0033';

		$farbe = strtolower($farbe);

		if($farben[$farbe] != ''){
			return '#'.$farben[$farbe];
		} else {
			return '#000000';
		}
	}

	function getGruendungString($date){
		$retstr = '';

		if($date != ''){
			if(substr($date, 8, 2) != '00' && substr($date, 5, 2) != '00'){ //day
				$retstr .= substr($date, 8, 2) .'.';
			}

			if(substr($date, 5, 2) != '00'){ //month
				$retstr .= substr($date, 5, 2) .'.';
			}

			if(substr($date, 0, 4) != '0000'){ //year
				$retstr .= substr($date, 0, 4);
			}
		}

		return $retstr;
	}

	function getVereinNameString($vereinid){
		$stmt = $this->libDb->prepare("SELECT titel, name FROM base_verein WHERE id = :id");
		$stmt->bindValue(':id', $vereinid, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		return $row['titel'] .' '. $row['name'];
	}

	function getToechterString($vereinid){
		$retstr = '';
		$stmt = $this->libDb->prepare("SELECT tochter.id, tochter.titel, tochter.name FROM base_verein AS mutter, base_verein AS tochter WHERE mutter.id = tochter.mutterverein AND mutter.id = :id");
		$stmt->bindValue(':id', $vereinid, PDO::PARAM_INT);
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($retstr != ''){
				$retstr .= ', ';
			}

			$retstr .= '<a href="index.php?pid=vereindetail&amp;verein=' .$row['id'] .'">' .$row['titel'] .' '. $row['name'] .'</a>';
		}

		return $retstr;
	}

	function getFusionertString($vereinid){
		$retstr = '';
		$stmt = $this->libDb->prepare("SELECT fusionierend.id, fusionierend.titel, fusionierend.name FROM base_verein AS fusionierend, base_verein AS fusioniert WHERE fusioniert.id = fusionierend.fusioniertin AND fusioniert.id = :id");
		$stmt->bindValue(':id', $vereinid, PDO::PARAM_INT);
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($retstr != ''){
				$retstr .= ', ';
			}

			$retstr .= '<a href="index.php?pid=vereindetail&amp;verein=' .$row['id'] .'">' .$row['titel'] .' '. $row['name'] .'</a>';
		}

		return $retstr;
	}

	function getAnsprechbarerAktivenVorstandIds(){
		$aktuellermonat = @date('m');

		if($aktuellermonat == 2 || $aktuellermonat == 3 || $aktuellermonat == 8 || $aktuellermonat == 9){
			$vorstandssemester = $this->libTime->getNaechstesSemester();
		} else {
			$vorstandssemester = $this->libTime->getAktuellesSemester();
		}

		$stmt = $this->libDb->prepare("SELECT senior, jubelsenior, consenior, fuchsmajor, fuchsmajor2, scriptor, quaestor FROM base_semester WHERE semester = :semester");
		$stmt->bindValue(':semester', $vorstandssemester);
		$stmt->execute();

		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	function getValideInternetWarte(){
		// ein valider Internetwart
		// 1. muss als solcher mindestens einmal in einem Semester angegeben worden sein
		// 2. muss einen Benutzernamen und Passworthash haben
		// 3. darf nicht in der Gruppe T oder X (tot oder ausgetreten) sein

		$internetwarte = array();

		$stmt = $this->libDb->prepare('SELECT COUNT(*) AS anzahlsemester, base_person.id FROM base_person, base_semester WHERE base_semester.internetwart = base_person.id AND gruppe != "X" AND gruppe != "T" AND gruppe != "C" AND gruppe != "W" AND gruppe != "G" AND username IS NOT NULL AND username != "" AND password_hash IS NOT NULL AND password_hash != "" GROUP BY id');
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$internetwarte[$row['id']] = $row['anzahlsemester'];
		}

		return $internetwarte;
	}
}
?>