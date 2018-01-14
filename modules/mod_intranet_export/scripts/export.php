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


$semesterIterator = $libTime->getSemesterName();

for($i=0; $i<50; $i++){
	$semesterIterator = $libTime->getPreviousSemesterNameOfSemester($semesterIterator);
}

$semester50zurueck = $semesterIterator;
$semester49zurueck = $libTime->getFollowingSemesterNameOfSemester($semester50zurueck);
$semester51zurueck = $libTime->getPreviousSemesterNameOfSemester($semester50zurueck);

for($i=0; $i<50; $i++){
	$semesterIterator = $libTime->getPreviousSemesterNameOfSemester($semesterIterator);
}

$semester100zurueck = $semesterIterator;
$semester99zurueck = $libTime->getFollowingSemesterNameOfSemester($semester100zurueck);
$semester101zurueck = $libTime->getPreviousSemesterNameOfSemester($semester100zurueck);
?>
<h1>Export</h1>
<p>Das VCMS kann Datenbestände als CSV- und HTML-Tabellen exportieren. Die Dateien können in Word und LibreOffice/OpenOffice in der Serienbrieffunktion verwendet werden. Bitte behandle diese Dateien vertraulich, verschicke sie nicht per E-Mail und lösche sie nach der Verwendung.</p>

<div class="panel panel-default">
	<div class="panel-body">
		<table class="table table-condensed">
			<tr>
				<th colspan="2">Adressdaten</th>
			</tr>
			<tr>
				<td rowspan="4">Adressen für Anschreiben:</td>
				<td>Mitglieder (<a href="api.php?iid=intranet_admin_export_daten_adressen&amp;datenart=mitglieder_anschreiben&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_adressen&amp;datenart=mitglieder_anschreiben&amp;type=html">HTML</a>)</td>
			</tr>
			<tr>
				<td>Damen (<a href="api.php?iid=intranet_admin_export_daten_adressen&amp;datenart=damenflor_anschreiben&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_adressen&amp;datenart=damenflor_anschreiben&amp;type=html">HTML</a>)</td>
			</tr>
			<tr>
				<td>Vips (<a href="api.php?iid=intranet_admin_export_daten_adressen&amp;datenart=vips&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_adressen&amp;datenart=vips&amp;type=html">HTML</a>)</td>
			</tr>
			<tr>
				<td>Vereine (<a href="api.php?iid=intranet_admin_export_daten_adressen&amp;datenart=vereine&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_adressen&amp;datenart=vereine&amp;type=html">HTML</a>)</td>
			</tr>
			<tr>
				<td rowspan="2">Adressen für Spendenquittungen:</td>
				<td>Spendenquittungsanschriften der Mitglieder (<a href="api.php?iid=intranet_admin_export_daten_adressen&amp;datenart=mitglieder_spendenquittung&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_adressen&amp;datenart=mitglieder_spendenquittung&amp;type=html">HTML</a>)</td>
			</tr>
			<tr>
				<td>Spendenquittungsanschriften des Damenflors (<a href="api.php?iid=intranet_admin_export_daten_adressen&amp;datenart=damenflor_spendenquittung&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_adressen&amp;datenart=damenflor_spendenquittung&amp;type=html">HTML</a>)</td>
			</tr>
			<tr>
				<th colspan="2">Geburtstage</th>
			</tr>
			<tr>
				<td>Sämtliche Geburtstage eines Jahres:</td>
				<td>
					<?php echo @date('Y')-2;?> (<a href="api.php?iid=intranet_admin_export_daten_geburtstage&amp;jahr=<?php echo @date('Y')-2;?>&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_geburtstage&amp;jahr=<?php echo @date('Y')-2;?>&amp;type=html">HTML</a>),
					<?php echo @date('Y')-1;?> (<a href="api.php?iid=intranet_admin_export_daten_geburtstage&amp;jahr=<?php echo @date('Y')-1;?>&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_geburtstage&amp;jahr=<?php echo @date('Y')-1;?>&amp;type=html">HTML</a>), <br />
					<?php echo @date('Y');?> (<a href="api.php?iid=intranet_admin_export_daten_geburtstage&amp;jahr=<?php echo @date('Y');?>&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_geburtstage&amp;jahr=<?php echo @date('Y');?>&amp;type=html">HTML</a>),
					<?php echo @date('Y')+1;?> (<a href="api.php?iid=intranet_admin_export_daten_geburtstage&amp;jahr=<?php echo @date('Y')+1;?>&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_geburtstage&amp;jahr=<?php echo @date('Y')+1;?>&amp;type=html">HTML</a>)
				</td>
			</tr>
			<tr>
				<td>Runde Geburtstage eines Jahres:</td>
				<td>
					<?php echo @date('Y')-2;?> (<a href="api.php?iid=intranet_admin_export_daten_rundegeburtstage&amp;jahr=<?php echo @date('Y')-2;?>&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_rundegeburtstage&amp;jahr=<?php echo @date('Y')-2;?>&amp;type=html">HTML</a>),
					<?php echo @date('Y')-1;?> (<a href="api.php?iid=intranet_admin_export_daten_rundegeburtstage&amp;jahr=<?php echo @date('Y')-1;?>&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_rundegeburtstage&amp;jahr=<?php echo @date('Y')-1;?>&amp;type=html">HTML</a>), <br />
					<?php echo @date('Y');?> (<a href="api.php?iid=intranet_admin_export_daten_rundegeburtstage&amp;jahr=<?php echo @date('Y');?>&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_rundegeburtstage&amp;jahr=<?php echo @date('Y');?>"&amp;type=html>HTML</a>),
					<?php echo @date('Y')+1;?> (<a href="api.php?iid=intranet_admin_export_daten_rundegeburtstage&amp;jahr=<?php echo @date('Y')+1;?>&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_rundegeburtstage&amp;jahr=<?php echo @date('Y')+1;?>"&amp;type=html>HTML</a>)
				</td>
			</tr>
			<tr>
				<th colspan="2">Receptionsjubiläen</th>
			</tr>
			<tr>
				<td>50-semestrige:</td>
				<td>
					<?php echo $libTime->getSemesterString($semester49zurueck);?> (<a href="api.php?iid=intranet_admin_export_daten_jubilaeen&amp;semester=<?php echo $semester49zurueck ;?>&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_jubilaeen&amp;semester=<?php echo $semester49zurueck ;?>&amp;type=html">HTML</a>),
					<?php echo $libTime->getSemesterString($semester50zurueck);?> (<a href="api.php?iid=intranet_admin_export_daten_jubilaeen&amp;semester=<?php echo $semester50zurueck ;?>&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_jubilaeen&amp;semester=<?php echo $semester50zurueck ;?>&amp;type=html">HTML</a>), <br />
					<?php echo $libTime->getSemesterString($semester51zurueck);?> (<a href="api.php?iid=intranet_admin_export_daten_jubilaeen&amp;semester=<?php echo $semester51zurueck ;?>&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_jubilaeen&amp;semester=<?php echo $semester51zurueck ;?>&amp;type=html">HTML</a>)
				</td>
			</tr>
			<tr>
				<td rowspan="3">100-semestrige:</td>
				<td>
					<?php echo $libTime->getSemesterString($semester99zurueck);?> (<a href="api.php?iid=intranet_admin_export_daten_jubilaeen&amp;semester=<?php echo $semester99zurueck ;?>&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_jubilaeen&amp;semester=<?php echo $semester99zurueck ;?>&amp;type=html">HTML</a>),
					<?php echo $libTime->getSemesterString($semester100zurueck);?> (<a href="api.php?iid=intranet_admin_export_daten_jubilaeen&amp;semester=<?php echo $semester100zurueck ;?>&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_jubilaeen&amp;semester=<?php echo $semester100zurueck ;?>&amp;type=html">HTML</a>), <br />
					<?php echo $libTime->getSemesterString($semester101zurueck);?> (<a href="api.php?iid=intranet_admin_export_daten_jubilaeen&amp;semester=<?php echo $semester101zurueck ;?>&amp;type=csv">CSV</a>, <a href="api.php?iid=intranet_admin_export_daten_jubilaeen&amp;semester=<?php echo $semester101zurueck ;?>&amp;type=html">HTML</a>)
				</td>
			</tr>
		</table>
	</div>
</div>
