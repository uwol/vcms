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

class LibGlobal{
	var $version = '6.79';

	var $semester;
	var $module;
	var $pid;
	var $page;
	var $iid;
	var $libInclude;

	var $errorTexts = array();
	var $notificationTexts = array();

	var $vcmsHostname;
	var $mkHostname;

	function __construct() {
		$this->vcmsHostname = 'ver' . 'bin' . 'dung' . 'scms' . '.' . 'de';
		$this->mkHostname = 'www' . '.' . 'mar' . 'kom' . 'ann' . 'ia' . '.' . 'org';
	}

	function getSiteUrl(){
		global $libGenericStorage;

		$result = $libGenericStorage->loadValue('base_core', 'siteUrl');
		return $result;
	}

	function getSiteUrlAuthority(){
		$siteUrl = $this->getSiteUrl();
		$result = preg_replace('/https?:\/\//', '', $siteUrl);
		return $result;
	}

	function getSiteUrlHost(){
		$siteUrl = $this->getSiteUrl();
		$result = parse_url($siteUrl, PHP_URL_HOST);
		return $result;
	}
}