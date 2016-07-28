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

class LibEvent{

	function isFacebookEvent($row){
		global $libGenericStorage;

		$fbAppId = $libGenericStorage->loadValue('base_core', 'fbAppId');
		$fbSecretKey = $libGenericStorage->loadValue('base_core', 'fbSecretKey');

		$result = isset($row['fb_eventid']) && is_numeric($row['fb_eventid'])
			&& ini_get('allow_url_fopen') && $fbAppId != '' && $fbSecretKey != '';
		return $result;
	}
}