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
	var $libAuth;

	function __construct(LibAuth $libAuth){
		$this->libAuth = $libAuth;
	}

	function getMenuHtml($menuInternet, $menuIntranet, $menuAdministration, $aktivesPid, $gruppe, $aemter){
		global $libGlobal;

		$menuInternet = $menuInternet->copy();
		$menuInternet->reduceByAccessRestriction($gruppe, $aemter);

		$menuIntranet = $menuIntranet->copy();
		$menuIntranet->reduceByAccessRestriction($gruppe, $aemter);

		$menuAdministration = $menuAdministration->copy();
		$menuAdministration->reduceByAccessRestriction($gruppe, $aemter);

		$retstr = '';
		$retstr .= '          <nav class="navbar navbar-default">' . PHP_EOL;
        $retstr .= '            <div class="container-fluid">' . PHP_EOL;

        $retstr .= $this->getNavbarCollapsed();

        $retstr .= $this->getNavbarInternet($menuInternet, $aktivesPid);
        $retstr .= $this->getNavbarIntranet($menuIntranet, $menuAdministration, $aktivesPid);

		$retstr .= '            </div>' . PHP_EOL;
		$retstr .= '          </nav>' . PHP_EOL;

		return $retstr;
	}

	function getNavbarInternet($menuInternet, $aktivesPid){
		$retstr = '';

		$rootMenuFolderInternet = $menuInternet->getRootMenuFolder();

		if($rootMenuFolderInternet->hasElements()){
			$retstr .= '              <div id="navbar-internet" class="collapse navbar-collapse navbar-internet">' . PHP_EOL;
			$retstr .= '                <ul class="nav navbar-nav navbar-right nav-pills">' . PHP_EOL;
			$retstr .= $this->getMenuLevel($rootMenuFolderInternet, 0, $aktivesPid);
			$retstr .= '                </ul>' . PHP_EOL;
			$retstr .= '              </div>' . PHP_EOL;
		}

		return $retstr;
	}

	function getNavbarIntranet($menuIntranet, $menuAdministration, $aktivesPid){
		$retstr = '';

		$rootMenuFolderIntranet = $menuIntranet->getRootMenuFolder();
		$rootMenuFolderAdministration = $menuAdministration->getRootMenuFolder();

		if($rootMenuFolderIntranet->hasElements()){
			$retstr .= '              <div id="navbar-intranet" class="collapse navbar-collapse navbar-intranet">' . PHP_EOL;
			$retstr .= '                <ul class="nav navbar-nav navbar-right nav-pills">' . PHP_EOL;
			$retstr .= $this->getMenuLevel($rootMenuFolderIntranet, 0, $aktivesPid);
			$retstr .= $this->getMenuLevel($rootMenuFolderAdministration, 0, $aktivesPid);
			$retstr .= '                </ul>' . PHP_EOL;
			$retstr .= '              </div>' . PHP_EOL;
		}

		return $retstr;
	}

	function getMenuLevel($menuFolder, $depth, $pid){
		$retstr = '';

		//for all menu elements
		foreach($menuFolder->getElements() as $folderElement){
			//internal link?
			if($folderElement->getType() == 1){
				$retstr .= $this->getLiTag($folderElement, $depth, $pid);
				$retstr .= '<a href="index.php?pid=' . $folderElement->getPid() . '">';
				$retstr .= $folderElement->getName();
				$retstr .= '</a></li>' . PHP_EOL;
			}
			//folder?
			elseif($folderElement->getType() == 2){
				$retstr .= $this->defaultIndent . $this->indent($depth) . '<li class="dropdown">' . PHP_EOL;

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
				$retstr .= '<span class="caret"></span></a>' . PHP_EOL;

				//menu folder with elements?
				if($folderElement->hasElements()){
					$retstr .= $this->defaultIndent . $this->indent($depth) . '  <ul class="dropdown-menu">' . PHP_EOL;
					$retstr .= $this->getMenuLevel($folderElement, $depth+1, $pid);
					$retstr .= $this->defaultIndent . $this->indent($depth) . '  </ul>' . PHP_EOL;
				}

				$retstr .= $this->defaultIndent . $this->indent($depth) . '</li>' . PHP_EOL;
			}
			//external link?
			elseif($folderElement->getType() == 3){
				$retstr .= $this->getLiTag($folderElement, $depth, $pid);
				$retstr .= '<a href="' .$folderElement->getPid(). '">';
				$retstr .= $folderElement->getName();
				$retstr .= '</a></li>' . PHP_EOL;
			}
			//login / logout
			elseif($folderElement->getType() == 4){
				$retstr .= $this->getLiTag($folderElement, $depth, $pid);

				if(!$this->libAuth->isLoggedin()){
					$retstr .= '<a href="index.php?pid=' . $folderElement->getPid() . '">';
					$retstr .= $folderElement->getName();
					$retstr .= '</a>' . PHP_EOL;
				} else {
					$retstr .= '<a href="index.php?session_destroy=1">' .$folderElement->getNameLogout(). '</a>';
				}

				$retstr .= '</li>';
			}
		}

		return $retstr;
	}

	function getNavbarCollapsed(){
		$retstr = '';

		$retstr .= '              <div class="navbar-header">' . PHP_EOL;
		$retstr .= '                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-internet,#navbar-intranet" aria-expanded="false">' . PHP_EOL;
        $retstr .= $this->defaultIndent . '<span class="sr-only">Navigation</span>' . PHP_EOL;
        $retstr .= $this->defaultIndent . '<span class="icon-bar"></span>' . PHP_EOL;
        $retstr .= $this->defaultIndent . '<span class="icon-bar"></span>' . PHP_EOL;
		$retstr .= $this->defaultIndent . '<span class="icon-bar"></span>' . PHP_EOL;
		$retstr .= '                </button>' . PHP_EOL;
		$retstr .= '              </div>' . PHP_EOL;

		return $retstr;
	}

	function indent($depth = 0){
		$str = '';

		for($i=0; $i < $depth; $i++){
			$str .= '    ';
		}

		return $str;
	}

	function getLiTag($folderElement, $depth, $pid){
		$retstr = '';

		if($folderElement->getPid() == $pid){
			$retstr .= $this->defaultIndent . $this->indent($depth) . '<li class="active">';
		} else {
			$retstr .= $this->defaultIndent . $this->indent($depth) . '<li>';
		}

		return $retstr;
	}
}
?>