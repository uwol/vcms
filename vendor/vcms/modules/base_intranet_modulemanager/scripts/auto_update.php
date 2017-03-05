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


$libDb->connect();

if($libGenericStorage->loadValue('base_core', 'auto_update')){
	$stmt = $libDb->prepare('SELECT COUNT(*) AS number FROM sys_log_intranet WHERE aktion = 20 AND DATEDIFF(NOW(), datum) < 1');
	$stmt->execute();
	$stmt->bindColumn('number', $numberOfAutoUpdateExecutionsToday);
	$stmt->fetch();

	if($numberOfAutoUpdateExecutionsToday == 0){
		$libDb->query('INSERT INTO sys_log_intranet (aktion, datum) VALUES (20, NOW())');

		$moduleStates = $libRepositoryClient->getModuleStates();
		$autoUpdated = false;

		if($moduleStates['engine']){
			$libRepositoryClient->updateEngine();
			$autoUpdated = true;
		}

		foreach($moduleStates as $key => $value){
		  if($key !== 'engine' && $value){
				$libRepositoryClient->installModule($key);
				$autoUpdated = true;
		  }
		}

		if($autoUpdated){
			$libDb->query('INSERT INTO sys_log_intranet (aktion, datum) VALUES (21, NOW())');

			$libRepositoryClient->resetTempDirectory();
			$libCronjobs->executeJobs();
		}
	}
}
