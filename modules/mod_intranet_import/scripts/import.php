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


$semesterNow = $libTime->getSemesterName();

?>
<h1>Import</h1>
<p class="mb-4">Das VCMS kann Datenbestände per CSV-Tabellen importieren. Damit das funktionieren kann, <u>müssen</u> die Spalten der CSV genau so geordnet und benannt sein, wie <a href="import_mitglieder_beispiel.csv">im hier verlinktem Beispiel für Mitglieder</a> und <a href="import_veranstaltungen_beispiel.csv">hier für Veranstaltungen</a> zu sehen ist.</p>

<div class="panel panel-default">
	<div class="panel-body">
		<table class="table table-condensed">
			<tr>
				<th colspan="2">Import</th>
			</tr>
			<tr>
				<td>von Mitgliedern:</td>
				<td>
					<form action="api.php?iid=intranet_admin_import_persons&amp;datenart=import" method="post" enctype="multipart/form-data">
						<div class="input-group">
							<div class="custom-file">
								<input type="file" class="custom-file-input" id="customFileInput" name="file">
								<label class="custom-file-label" for="customFileInput">CSV Upload:</label>
							</div>
							<div class="input-group-append">
								<input type="submit" name="submit" value="Upload" class="btn btn-primary">
							</div>
						</div>
					</form>
				</td>
			</tr>
			<tr>
				<td>von Veranstaltungen:</td>
				<td>
					<form action="api.php?iid=intranet_admin_import_veranstaltungen&amp;datenart=import" method="post" enctype="multipart/form-data">
						<div class="input-group">
							<div class="custom-file">
								<input type="file" class="custom-file-input" id="customFileInput" name="file">
								<label class="custom-file-label" for="customFileInput">CSV Upload:</label>
							</div>
							<div class="input-group-append">
								<input type="submit" name="submit" value="Upload" class="btn btn-primary">
							</div>
						</div>
					</form>
				</td>
			</tr>
		</table>
	</div>
</div>
