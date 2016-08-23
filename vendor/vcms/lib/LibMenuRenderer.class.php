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

class LibMenuRenderer{

	var $defaultIndent = '            ';

	function printNavbar($menuInternet, $menuIntranet, $menuAdministration, $aktivesPid, $gruppe, $aemter){
		global $libGlobal;

		$menuInternet = $menuInternet->copy();
		$menuInternet->reduceByAccessRestriction($gruppe, $aemter);

		$menuIntranet = $menuIntranet->copy();
		$menuIntranet->reduceByAccessRestriction($gruppe, $aemter);

		$menuAdministration = $menuAdministration->copy();
		$menuAdministration->reduceByAccessRestriction($gruppe, $aemter);

		$navbarClass = $this->getNavbarClass();

		echo '    <nav id="nav" class="navbar navbar-default navbar-fixed-top ' .$navbarClass. '">' . PHP_EOL;
        echo '      <div class="container">' . PHP_EOL;
		echo '        <div id="logo"></div>' . PHP_EOL;
        echo $this->printNavbarCollapsed();
        echo $this->printNavbarInternet($menuInternet, $aktivesPid);
        echo $this->printNavbarIntranet($menuIntranet, $menuAdministration, $aktivesPid);
		echo '      </div>' . PHP_EOL;
		echo '    </nav>' . PHP_EOL;
	}

	function printNavbarCollapsed(){
		global $libGenericStorage;

		echo '        <div class="navbar-header">' . PHP_EOL;
		echo '          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-internet,#navbar-intranet" aria-expanded="false">' . PHP_EOL;
        echo $this->defaultIndent . '<span class="sr-only">Navigation</span>' . PHP_EOL;
        echo $this->defaultIndent . '<span class="icon-bar"></span>' . PHP_EOL;
        echo $this->defaultIndent . '<span class="icon-bar"></span>' . PHP_EOL;
		echo $this->defaultIndent . '<span class="icon-bar"></span>' . PHP_EOL;
		echo '          </button>' . PHP_EOL;

		$brand = $libGenericStorage->loadValue('base_core', 'brand');
		$brandXs = $libGenericStorage->loadValue('base_core', 'brandXs');

		echo '          <a href="index.php" class="navbar-brand hidden-xs">' .$brand. '</a>' . PHP_EOL;
		echo '          <a href="index.php" class="navbar-brand visible-xs">' .$brandXs. '</a>' . PHP_EOL;
		echo '        </div>' . PHP_EOL;
	}

	function printNavbarInternet($menuInternet, $aktivesPid){
		$rootMenuFolderInternet = $menuInternet->getRootMenuFolder();

		if($rootMenuFolderInternet->hasElements()){
			echo '        <div id="navbar-internet" class="collapse navbar-collapse navbar-internet">' . PHP_EOL;
			echo '          <ul class="nav navbar-nav navbar-right nav-pills">' . PHP_EOL;
			echo $this->printNavbarLevel($rootMenuFolderInternet, 0, $aktivesPid);
			echo '          </ul>' . PHP_EOL;
			echo '        </div>' . PHP_EOL;
		}
	}

	function printNavbarIntranet($menuIntranet, $menuAdministration, $aktivesPid){
		$rootMenuFolderIntranet = $menuIntranet->getRootMenuFolder();
		$rootMenuFolderAdministration = $menuAdministration->getRootMenuFolder();

		if($rootMenuFolderIntranet->hasElements()){
			echo '        <div id="navbar-intranet" class="collapse navbar-collapse navbar-intranet">' . PHP_EOL;
			echo '          <ul class="nav navbar-nav navbar-right nav-pills">' . PHP_EOL;
			echo $this->printNavbarLevel($rootMenuFolderIntranet, 0, $aktivesPid);
			echo $this->printNavbarLevel($rootMenuFolderAdministration, 0, $aktivesPid);
			echo '          </ul>' . PHP_EOL;
			echo '        </div>' . PHP_EOL;
		}
	}

	function printNavbarLevel($menuFolder, $depth, $pid){
		global $libAuth;

		//for all menu elements
		foreach($menuFolder->getElements() as $folderElement){
			//internal link?
			if($folderElement->getType() == 1){
				$this->printLiTag($folderElement, $depth, $pid);

				echo '<a href="index.php?pid=' . $folderElement->getPid() . '">';
				echo $folderElement->getName();
				echo '</a></li>' . PHP_EOL;
			}
			//folder?
			elseif($folderElement->getType() == 2){
				echo $this->defaultIndent . $this->indent($depth) . '<li class="dropdown">' . PHP_EOL;
				echo $this->defaultIndent . $this->indent($depth) . '  <a href="index.php?';

				//does the folder have an associated page?
				if($folderElement->getPid() != ''){
					echo 'pid='.$folderElement->getPid();
				}
				//else show current page
				else {
					echo 'pid='.$pid;
				}

				echo '" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">';
				echo $folderElement->getName();
				echo '<span class="caret"></span></a>' . PHP_EOL;

				//menu folder with elements?
				if($folderElement->hasElements()){
					echo $this->defaultIndent . $this->indent($depth) . '  <ul class="dropdown-menu">' . PHP_EOL;
					echo $this->printNavbarLevel($folderElement, $depth+1, $pid);
					echo $this->defaultIndent . $this->indent($depth) . '  </ul>' . PHP_EOL;
				}

				echo $this->defaultIndent . $this->indent($depth) . '</li>' . PHP_EOL;
			}
			//external link?
			elseif($folderElement->getType() == 3){
				$this->printLiTag($folderElement, $depth, $pid);

				echo '<a href="' .$folderElement->getPid(). '">';
				echo '<i class="fa fa-external-link" aria-hidden="true"></i> ';
				echo $folderElement->getName();
				echo '</a></li>' . PHP_EOL;
			}
			//login / logout
			elseif($folderElement->getType() == 4){
				$this->printLiTag($folderElement, $depth, $pid);

				if(!$libAuth->isLoggedin()){
					echo '<a href="index.php?pid=' . $folderElement->getPid() . '">';
					echo $folderElement->getName();
					echo '</a>';
				} else {
					echo '<a href="index.php?session_destroy=1">' .$folderElement->getNameLogout(). '</a>';
				}

				echo '</li>' . PHP_EOL;
			}
		}
	}

	function indent($depth = 0){
		for($i=0; $i < $depth; $i++){
			echo '    ';
		}
	}

	function printLiTag($folderElement, $depth, $pid){
		if($folderElement->getPid() == $pid){
			echo $this->defaultIndent . $this->indent($depth) . '<li class="active">';
		} else {
			echo $this->defaultIndent . $this->indent($depth) . '<li>';
		}
	}

	function getNavbarClass(){
		global $libAuth;

		return !$libAuth->isLoggedin() ? 'navbar-internet-only' : 'navbar-internet-intranet';
	}
}