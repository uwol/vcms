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

namespace vcms\module;

class LibModule{
	var $id;
	var $version;
	var $name;
	var $path;
	var $pages;
	var $includes;
	var $headerStrings;
	var $installScript;
	var $uninstallScript;
	var $updateScript;

	function __construct($id, $name, $version, $path, &$pages, &$includes, $headerStrings, $installScript, $uninstallScript, $updateScript){
		if($id == ''){
			echo('Fehler: Fehlende Module-Id<br />');
		}

		if($version != '' && !is_numeric($version)){
			echo('Fehler: Versionsangabe nicht numerisch. Korrekt wäre zum Beispiel 1.6<br />');
		}

		if($name == ''){
			echo('Fehler: Fehlende Namensangabe<br />');
		}

		if($path == ''){
			echo('Fehler: Fehlender Modulpfad<br />');
		}

		if(!is_array($pages)){
			echo('Fehler: Fehlendes Array pages<br />');
		}

		if(!is_array($includes)){
			echo('Fehler: Fehlendes Array includes<br />');
		}

		$this->id = $id;
		$this->version = $version;
		$this->name = $name;
		$this->path = $path;
		$this->pages = $pages;
		$this->installScript = $installScript;
		$this->uninstallScript = $uninstallScript;
		$this->updateScript = $updateScript;
		$this->includes = $includes;
		$this->headerStrings = $headerStrings;
	}

	function getId(){
		return $this->id;
	}

	function getName(){
		return $this->name;
	}

	function getVersion(){
		return $this->version;
	}

	function getPath(){
		return $this->path;
	}

	function getPages(){
		return $this->pages;
	}

	function getInstallScript(){
		return $this->installScript;
	}

	function getUninstallScript(){
		return $this->uninstallScript;
	}

	function getUpdateScript(){
		return $this->updateScript;
	}

	function getIncludes(){
		return $this->includes;
	}

	function getHeaderStrings(){
		return $this->headerStrings;
	}
}