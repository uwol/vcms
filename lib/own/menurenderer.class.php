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

class LibMenuRenderer{

	var $defaultIndent = '                  ';

	function getMenuHtml($menuInternet, $menuIntranet, $menuAdministration, $aktivesPid, $gruppe, $aemter){
		global $libGlobal;

		$menuInternet = $menuInternet->copy();
		$menuInternet->reduceByAccessRestriction($gruppe, $aemter);

		$menuIntranet = $menuIntranet->copy();
		$menuIntranet->reduceByAccessRestriction($gruppe, $aemter);

		$menuAdministration = $menuAdministration->copy();
		$menuAdministration->reduceByAccessRestriction($gruppe, $aemter);

		$retstr = '';
		$retstr .= '          <nav class="navbar navbar-default">' . "\r\n";
        $retstr .= '            <div class="container-fluid">' . "\r\n";

        $retstr .= $this->getNavbarCollapsed();

        $retstr .= $this->getNavbarInternet($menuInternet, $aktivesPid);
        $retstr .= $this->getNavbarIntranet($menuIntranet, $menuAdministration, $aktivesPid);

		$retstr .= '            </div>' . "\r\n";
		$retstr .= '          </nav>' . "\r\n";

		return $retstr;
	}

	function getNavbarInternet($menuInternet, $aktivesPid){
		$retstr = '';

		$rootMenuFolderInternet = $menuInternet->getRootMenuFolder();

		if($rootMenuFolderInternet->hasElements()){
			$retstr .= '              <div id="navbar-internet" class="collapse navbar-collapse navbar-internet">' . "\r\n";
			$retstr .= '                <ul class="nav navbar-nav">' . "\r\n";
			$retstr .= $this->getMenuLevel($rootMenuFolderInternet, 0, $aktivesPid);
			$retstr .= '                </ul>' . "\r\n";
			$retstr .= '              </div>' . "\r\n";
		}

		return $retstr;
	}

	function getNavbarIntranet($menuIntranet, $menuAdministration, $aktivesPid){
		$retstr = '';

		$rootMenuFolderIntranet = $menuIntranet->getRootMenuFolder();
		$rootMenuFolderAdministration = $menuAdministration->getRootMenuFolder();

		if($rootMenuFolderIntranet->hasElements()){
			$retstr .= '              <div id="navbar-intranet" class="collapse navbar-collapse navbar-intranet">' . "\r\n";
			$retstr .= '                <ul class="nav navbar-nav">' . "\r\n";
			$retstr .= $this->getMenuLevel($rootMenuFolderIntranet, 0, $aktivesPid);
			$retstr .= $this->getMenuLevel($rootMenuFolderAdministration, 0, $aktivesPid);
			$retstr .= '                </ul>' . "\r\n";
			$retstr .= '              </div>' . "\r\n";
		}

		return $retstr;
	}

	function getMenuLevel($menuFolder, $depth, $pid){
		$retstr = '';

		//for all menu elements
		foreach($menuFolder->getElements() as $folderElement){
			//internal link?
			if($folderElement->getType() == 1){
				if($folderElement->getPid() == $pid){
					$retstr .= $this->defaultIndent . $this->indent($depth) . '<li class="active">';
				} else {
					$retstr .= $this->defaultIndent . $this->indent($depth) . '<li>';
				}

				$retstr .= '<a href="index.php?pid=' . $folderElement->getPid() . '">';
				$retstr .= $folderElement->getName();
				$retstr .= '</a></li>' . "\r\n";
			}
			//folder?
			elseif($folderElement->getType() == 2){
				$retstr .= $this->defaultIndent . $this->indent($depth) . '<li class="dropdown">' . "\r\n";

				$retstr .= $this->defaultIndent . $this->indent($depth) . '  <a href="index.php?';

				//does the folder have an associated page?
				if($folderElement->getPid() != ""){
					$retstr .= 'pid='.$folderElement->getPid();
				}
				//else show current page
				else {
					$retstr .= 'pid='.$pid;
				}

				$retstr .= '" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">';
				$retstr .= $folderElement->getName();
				$retstr .= '<span class="caret"></span></a>' . "\r\n";

				//menu folder with elements?
				if($folderElement->hasElements()){
					$retstr .= $this->defaultIndent . $this->indent($depth) . '  <ul class="dropdown-menu">' . "\r\n";
					$retstr .= $this->getMenuLevel($folderElement, $depth+1, $pid);
					$retstr .= $this->defaultIndent . $this->indent($depth) . '  </ul>' . "\r\n";
				}

				$retstr .= $this->defaultIndent . $this->indent($depth) . '</li>' . "\r\n";
			}
			//external link?
			elseif($folderElement->getType() == 3){
				$retstr .= $this->defaultIndent . $this->indent($depth) . '<li><a href="' .$folderElement->getPid(). '">';
				$retstr .= $folderElement->getName();
				$retstr .= '</a></li>' . "\r\n";
			}
		}

		return $retstr;
	}

	function getNavbarCollapsed(){
		$retstr = '';

		$retstr .= '              <div class="navbar-header">' . "\r\n";
		$retstr .= '                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-internet,#navbar-intranet" aria-expanded="false">' . "\r\n";
        $retstr .= $this->defaultIndent . '<span class="sr-only">Navigation</span>' . "\r\n";
        $retstr .= $this->defaultIndent . '<span class="icon-bar"></span>' . "\r\n";
        $retstr .= $this->defaultIndent . '<span class="icon-bar"></span>' . "\r\n";
		$retstr .= $this->defaultIndent . '<span class="icon-bar"></span>' . "\r\n";
		$retstr .= '                </button>' . "\r\n";
		$retstr .= '              </div>' . "\r\n";

		return $retstr;
	}

	function indent($depth = 0){
		$str = '';

		for($i=0; $i < $depth; $i++){
			$str .= '    ';
		}

		return $str;
	}
}
?>