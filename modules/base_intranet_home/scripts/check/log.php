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


$numberOfLoginErrorsThreshold = 14;
$numberOfLoginErrorDaysThreshold = 14;

if(in_array('internetwart', $libAuth->getAemter())){
	$stmt = $libDb->prepare('SELECT COUNT(mitglied) AS numberOfLoginErrors FROM sys_log_intranet WHERE aktion = 2 AND DATEDIFF(NOW(), datum) < ' .$numberOfLoginErrorDaysThreshold. ' GROUP BY mitglied HAVING numberOfLoginErrors >= ' .$numberOfLoginErrorsThreshold);
	$stmt->execute();
	$stmt->bindColumn('numberOfLoginErrors', $anzahl);
	$stmt->fetch();

	if($anzahl > 0){
		$logText = 'Personen mit erfolglosen Intranet-Anmeldungen in den letzten ' .$numberOfLoginErrorDaysThreshold. ' Tagen: ';

		$stmt = $libDb->prepare('SELECT COUNT(mitglied) AS numberOfLoginErrors, mitglied FROM sys_log_intranet WHERE aktion = 2 AND DATEDIFF(NOW(), datum) < ' .$numberOfLoginErrorDaysThreshold. ' GROUP BY mitglied HAVING numberOfLoginErrors >= ' .$numberOfLoginErrorsThreshold. ' ORDER BY numberOfLoginErrors DESC');
		$stmt->execute();

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$logText .= '<span class="badge">' .$row['numberOfLoginErrors']. '</span>';
			$logText .= ' ';
			$logText .= '<a href="index.php?pid=intranet_person_daten&personid=' .$row['mitglied']. '">' .$libMitglied->getMitgliedNameString($row['mitglied'], 4). '</a>';
			$logText .= ' ';
		}

		$libGlobal->errorTexts[] = $logText;
	}
}
?>