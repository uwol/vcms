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

	function getMenuHtml($menu, $aktivesPid, $gruppe, $aemter){
		global $libGlobal;

		$menu = $menu->copy();
		$menu->reduceByAccessRestriction($gruppe, $aemter);

		$retstr = '';
		$retstr .= '        <div id="menu">'."\r\n";
		$retstr .= '          <ul class="folderlist">'."\r\n";
		$retstr .= $this->getMenuLevel($menu->getUpperMenuFolder(), 0, $aktivesPid);
		$retstr .= '          </ul>'."\r\n";
		$retstr .= '        </div>'."\r\n";

		return $retstr;
	}

	function getMenuLevel($menuFolder, $depth, $pid){
		$retstr = '';

		if($menuFolder->isOpen() || $menuFolder->isAlwaysOpen()){
			//for all menu elements
			foreach($menuFolder->getElements() as $folderElement){
				//internal link?
				if($folderElement->getType() == 1){
					if($folderElement->getPid() == $pid){
						$retstr .= $this->indent($depth).'            <li class="entryelementselected ' .$folderElement->getPid(). '">';
					} else {
						$retstr .= $this->indent($depth).'            <li class="entryelement ' .$folderElement->getPid(). '">';
					}

					$retstr .= '<a href="index.php?pid='.$folderElement->getPid() .'">';
					$retstr .= $folderElement->getName();
					$retstr .= '</a></li>'."\r\n";
				}
				//folder?
				elseif($folderElement->getType() == 2){
					//current page?
					if($folderElement->getPid() == $pid){
						$retstr .= $this->indent($depth).'            <li class="folderelementselected ' .$folderElement->getId(). '">';
					} else {
						$retstr .= $this->indent($depth).'            <li class="folderelement ' .$folderElement->getId(). '">';
					}

					$retstr .= '<a href="index.php?mid=' .$folderElement->getId(). '&amp;';

					//does the folder have an associated page?
					if($folderElement->getPid() != ""){
						$retstr .= 'pid='.$folderElement->getPid();
					}
					//else show current page
					else {
						$retstr .= 'pid='.$pid;
					}

					$retstr .= '">';

					$retstr .= $folderElement->getName();
					$retstr .= '</a>';

					//menu folder with elements?
					if($folderElement->hasElements()){
						if($folderElement->isOpen() || $folderElement->isAlwaysOpen()){
							$retstr .= "\r\n";
							$retstr .= $this->indent($depth).'              <ul class="folderlist">'."\r\n";
							$retstr .= $this->getMenuLevel($folderElement, $depth+1, $pid);
							$retstr .= $this->indent($depth).'              </ul>'."\r\n";
							$retstr .= $this->indent($depth).'            ';
						}
					}

					$retstr .= '</li>'."\r\n";
				}
				//external link?
				elseif($folderElement->getType() == 3){
					$retstr .= $this->indent($depth).'            <li class="entryelement externallink"><a href="' .$folderElement->getPid(). '">';
					$retstr .= $folderElement->getName();
					$retstr .= '</a></li>'."\r\n";
				}
			}
		}

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