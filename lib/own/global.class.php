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

class LibGlobal{
	var $semester;
	var $module;
	var $pid;
	var $page;
	var $iid;
	var $libInclude;
	var $version = '4.32';
	var $errorTexts = array();
	var $notificationTexts = array();
	var $vcmsHostname;

	function __construct() {
		$this->vcmsHostname = 'ver' . 'bin' . 'dung' . 'scms' . '.' . 'de';
	}
}
?>