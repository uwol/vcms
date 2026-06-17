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
		global $libGenericStorage;

		$menuInternet = $menuInternet->copy();
		$menuInternet->reduceByAccessRestriction($gruppe, $aemter);

		$menuIntranet = $menuIntranet->copy();
		$menuIntranet->reduceByAccessRestriction($gruppe, $aemter);

		$menuAdministration = $menuAdministration->copy();
		$menuAdministration->reduceByAccessRestriction($gruppe, $aemter);

		$navbarClass = $this->getNavbarClass();

		$brand = $libGenericStorage->loadValue('base_core', 'brand');
		$brandXs = $libGenericStorage->loadValue('base_core', 'brand_xs');

		echo '    <nav id="nav" class="navbar navbar-vcms navbar-expand-lg fixed-top navbar-light bg-light ' .$navbarClass. '" role="navigation">' . PHP_EOL;
		echo '      <div class="container">' . PHP_EOL;
		echo '      	<div class="flex-column d-flex flex-grow-1">' . PHP_EOL;

		echo '      		<div class="flex-row position-relative">' . PHP_EOL;
		echo '        			<div id="logo"></div>' . PHP_EOL;
		echo '        			<div class="position-absolute top-50 start-50 translate-middle">' . PHP_EOL;
		echo '        				<a href="index.php" class="navbar-brand d-inline d-sm-none">' .$brandXs. '</a>' . PHP_EOL;
		echo '        				<a href="index.php" class="navbar-brand d-none d-sm-inline d-lg-none">' .$brand. '</a>' . PHP_EOL;
		echo '        			</div>' . PHP_EOL;
		echo $this->printNavbarCollapsed();
		echo '        		</div>' . PHP_EOL;

		echo '      		<div id="navbar-internet" class="w-100 collapse navbar-collapse navbar-internet">' . PHP_EOL;

		echo '        			<a href="index.php" class="navbar-brand d-none d-lg-inline">' .$brand. '</a>' . PHP_EOL;

		echo $this->printNavbarInternet($menuInternet, $aktivesPid);
		echo '        		</div>' . PHP_EOL;

		echo $this->printNavbarIntranet($menuIntranet, $menuAdministration, $aktivesPid);
		echo '      	</div>' . PHP_EOL;
		echo '      </div>' . PHP_EOL;
		echo '    </nav>' . PHP_EOL;
	}

	function printNavbarCollapsed(){
		echo '        <button class="navbar-toggler position-absolute top-50 end-0 translate-middle-y" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-internet,#navbar-intranet" aria-controls="navbar-internet" aria-expanded="false" aria-label="Navigation">' . PHP_EOL;
		echo $this->defaultIndent . '<span class="navbar-toggler-icon" style="background-image: url(\'vendor/vcms/styles/navigation/menu.svg\');"></span>' . PHP_EOL;
		echo '        </button>' . PHP_EOL;
	}

	function printNavbarInternet($menuInternet, $aktivesPid){
		global $libAuth, $libPerson;

		$rootMenuFolderInternet = $menuInternet->getRootMenuFolder();

		if($rootMenuFolderInternet->hasElements()){
			echo '          <ul class="w-100 navbar-nav ms-auto justify-content-end nav-pills navbar-internet">' . PHP_EOL;
			echo $this->printNavbarLevel($rootMenuFolderInternet, 0, $aktivesPid);

			if($libAuth->isLoggedin() && $libPerson->hasImageFile($libAuth->getId())){
				echo '            <li class="nav-item d-none d-lg-block">' .$libPerson->getImage($libAuth->getId(), 'xs'). '</li>' . PHP_EOL;
			}

			echo '          </ul>' . PHP_EOL;
		}
	}

	function printNavbarIntranet($menuIntranet, $menuAdministration, $aktivesPid){
		$rootMenuFolderIntranet = $menuIntranet->getRootMenuFolder();
		$rootMenuFolderAdministration = $menuAdministration->getRootMenuFolder();

		if($rootMenuFolderIntranet->hasElements()){
			echo '        <div id="navbar-intranet" class="w-100 collapse navbar-collapse navbar-intranet">' . PHP_EOL;
			echo '          <ul class="w-100 navbar-nav ms-auto justify-content-end ms-lg-3 nav-pills navbar-intranet">' . PHP_EOL;
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
			$isDropdownItem = $depth > 0;
			$isActive = $folderElement->getPid() == $pid;
			$linkClass = $isDropdownItem ? 'dropdown-item' : 'nav-link';
			if($isActive){
				$linkClass .= ' active';
			}

			//internal link?
			if($folderElement->getType() == 1){
				if($isDropdownItem){
					echo $this->defaultIndent . $this->indent($depth) . '<li>';
				} else {
					echo $this->defaultIndent . $this->indent($depth) . '<li class="nav-item">';
				}

				echo '<a class="' .$linkClass. '" href="index.php?pid=' . $folderElement->getPid() . '"';
				if($isActive){
					echo ' aria-current="page"';
				}
				echo '>';
				echo $folderElement->getName();
				echo '</a></li>' . PHP_EOL;
			}
			//folder?
			elseif($folderElement->getType() == 2){
				echo $this->defaultIndent . $this->indent($depth) . '<li class="nav-item dropdown">' . PHP_EOL;
				echo $this->defaultIndent . $this->indent($depth) . '  <a class="nav-link dropdown-toggle" href="index.php?';

				//does the folder have an associated page?
				if($folderElement->getPid() != ''){
					echo 'pid='.$folderElement->getPid();
				}
				//else show current page
				else {
					echo 'pid='.$pid;
				}

				echo '" data-bs-toggle="dropdown" role="button" aria-expanded="false">';
				echo $folderElement->getName();
				echo '</a>' . PHP_EOL;

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
				if($isDropdownItem){
					echo $this->defaultIndent . $this->indent($depth) . '<li>';
				} else {
					echo $this->defaultIndent . $this->indent($depth) . '<li class="nav-item">';
				}

				echo '<a class="' .$linkClass. '" href="' .$folderElement->getPid(). '"';
				if($isActive){
					echo ' aria-current="page"';
				}
				echo '>';
				echo '<i class="fa fa-external-link" aria-hidden="true"></i> ';
				echo $folderElement->getName();
				echo '</a></li>' . PHP_EOL;
			}
			//login / logout
			elseif($folderElement->getType() == 4){
				if($isDropdownItem){
					echo $this->defaultIndent . $this->indent($depth) . '<li>';
				} else {
					echo $this->defaultIndent . $this->indent($depth) . '<li class="nav-item">';
				}

				if(!$libAuth->isLoggedin()){
					echo '<a class="' .$linkClass. '" href="index.php?pid=' . $folderElement->getPid() . '"';
					if($isActive){
						echo ' aria-current="page"';
					}
					echo '>';
					echo $folderElement->getName();
					echo '</a>';
				} else {
					echo '<a class="' .$linkClass. '" href="index.php?logout=1">' .$folderElement->getNameLogout(). '</a>';
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
