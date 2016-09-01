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

require_once('custom/systemconfig.php');
require_once('vendor/vcms/initialize.php');


if(isset($_REQUEST['iid'])){
	$libGlobal->iid = $_REQUEST['iid'];
	$libGlobal->libInclude = $libModuleHandler->getInclude($libGlobal->iid);
	$libGlobal->module = $libModuleHandler->getModuleByIncludeid($libGlobal->iid);

	if(is_object($libGlobal->libInclude) && $libSecurityManager->hasAccess($libGlobal->libInclude, $libAuth)){
		if(is_file($libGlobal->libInclude->getPath())){
			require_once($libGlobal->libInclude->getPath());
		}
	}
}
