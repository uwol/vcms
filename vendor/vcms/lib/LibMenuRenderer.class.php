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

class LibMenuRenderer
{
    public $defaultIndent = '            ';

    public function printNavbar($menuInternet, $menuIntranet, $menuAdministration, $activePid, $group, $offices)
    {
        global $libGlobal;

        $menuInternet = $menuInternet->copy();
        $menuInternet->reduceByAccessRestriction($group, $offices);

        $menuIntranet = $menuIntranet->copy();
        $menuIntranet->reduceByAccessRestriction($group, $offices);

        $menuAdministration = $menuAdministration->copy();
        $menuAdministration->reduceByAccessRestriction($group, $offices);

        $navbarClass = $this->getNavbarClass();

        /*
        * Position and background colour of the navbar are owned by navigation.css and not
        * set with utilities, because utilities would override the affix-top state in
        * navigation.css and navigation_transparent.css.
        */
        echo '    <nav id="nav" class="ui-navbar ' .$navbarClass. '">' . PHP_EOL;
        echo '      <div class="ui-container">' . PHP_EOL;
        echo '        <div id="logo"></div>' . PHP_EOL;
        echo $this->printNavbarCollapsed();
        echo $this->printNavbarInternet($menuInternet, $activePid);
        echo $this->printNavbarIntranet($menuIntranet, $menuAdministration, $activePid);
        echo '      </div>' . PHP_EOL;
        echo '    </nav>' . PHP_EOL;
    }

    public function printNavbarCollapsed()
    {
        global $libGenericStorage, $libString;

        $brand = $libGenericStorage->loadValue('base_core', 'brand');
        $brandXs = $libGenericStorage->loadValue('base_core', 'brand_xs');

        echo '          <a href="index.php" class="ui-navbar-brand hidden md:block">' .$libString->protectXSS((string) $brand). '</a>' . PHP_EOL;
        echo '          <a href="index.php" class="ui-navbar-brand md:hidden">' .$libString->protectXSS((string) $brandXs). '</a>' . PHP_EOL;
        echo '          <button type="button" class="ui-navbar-toggler" data-ui-toggle="collapse" data-ui-target="#navbar-internet,#navbar-intranet" aria-controls="navbar-internet navbar-intranet" aria-expanded="false" aria-label="Navigation">' . PHP_EOL;
        echo $this->defaultIndent . '<span class="ui-navbar-toggler-icon"></span>' . PHP_EOL;
        echo '          </button>' . PHP_EOL;
    }

    public function printNavbarInternet($menuInternet, $activePid)
    {
        global $libAuth, $libPerson;

        $rootMenuFolderInternet = $menuInternet->getRootMenuFolder();

        if ($rootMenuFolderInternet->hasElements()) {
            echo '        <div id="navbar-internet" class="ui-collapse ui-navbar-collapse navbar-internet">' . PHP_EOL;

            /*
            * The person image below is taller than the text links beside it. ui-navbar-nav is
            * a flex row from the expand breakpoint upwards without align-items, so the default
            * stretch would make the shorter nav-link boxes sit at the top of the row instead
            * of on its centre line. The breakpoint variant of the utility is needed: below
            * 768px ui-navbar-nav is a flex column, where align-items would centre the entries
            * horizontally.
            */
            echo '          <ul class="ui-navbar-nav md:items-center">' . PHP_EOL;
            echo $this->printNavbarLevel($rootMenuFolderInternet, 0, $activePid);

            if ($libAuth->isLoggedin() && $libPerson->hasImageFile($libAuth->getId())) {
                echo '            <li class="hidden lg:block ps-1">' .$libPerson->getImage($libAuth->getId(), 'xs'). '</li>' . PHP_EOL;
            }

            echo '          </ul>' . PHP_EOL;
            echo '        </div>' . PHP_EOL;
        }
    }

    public function printNavbarIntranet($menuIntranet, $menuAdministration, $activePid)
    {
        $rootMenuFolderIntranet = $menuIntranet->getRootMenuFolder();
        $rootMenuFolderAdministration = $menuAdministration->getRootMenuFolder();

        if ($rootMenuFolderIntranet->hasElements()) {
            echo '        <div id="navbar-intranet" class="ui-collapse ui-navbar-collapse navbar-intranet">' . PHP_EOL;
            echo '          <ul class="ui-navbar-nav">' . PHP_EOL;
            echo $this->printNavbarLevel($rootMenuFolderIntranet, 0, $activePid);
            echo $this->printNavbarLevel($rootMenuFolderAdministration, 0, $activePid);
            echo '          </ul>' . PHP_EOL;
            echo '        </div>' . PHP_EOL;
        }
    }

    public function printNavbarLevel($menuFolder, $depth, $pid)
    {
        global $libAuth, $libString;

        //for all menu elements
        foreach ($menuFolder->getElements() as $folderElement) {
            //internal link?
            if ($folderElement->getType() == 1) {
                $this->printLinkTag($folderElement, $depth, $pid, 'index.php?pid=' .$folderElement->getPid());

                echo $libString->protectXSS($folderElement->getName());
                echo '</a></li>' . PHP_EOL;
            }
            //folder?
            elseif ($folderElement->getType() == 2) {
                echo $this->defaultIndent . $this->indent($depth) . '<li class="ui-dropdown">' . PHP_EOL;
                echo $this->defaultIndent . $this->indent($depth) . '  <a class="ui-nav-link ui-dropdown-toggle" href="index.php?';

                //does the folder have an associated page?
                if ($folderElement->getPid() != '') {
                    echo 'pid='.$libString->protectXSS($folderElement->getPid());
                }
                //else show current page
                else {
                    echo 'pid='.$libString->protectXSS($pid);
                }

                echo '" data-ui-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">';
                echo $libString->protectXSS($folderElement->getName());
                echo '</a>' . PHP_EOL;

                //menu folder with elements?
                if ($folderElement->hasElements()) {
                    echo $this->defaultIndent . $this->indent($depth) . '  <ul class="ui-dropdown-menu">' . PHP_EOL;
                    echo $this->printNavbarLevel($folderElement, $depth + 1, $pid);
                    echo $this->defaultIndent . $this->indent($depth) . '  </ul>' . PHP_EOL;
                }

                echo $this->defaultIndent . $this->indent($depth) . '</li>' . PHP_EOL;
            }
            //external link?
            elseif ($folderElement->getType() == 3) {
                // A stored value without an http(s) prefix gets one prepended, so
                // that it can never be rendered as a javascript: link: such a value
                // ends up as the harmless http://javascript:... instead.
                $externalUrl = $libString->assureHttpScheme((string) $folderElement->getPid());

                $this->printLinkTag($folderElement, $depth, $pid, $externalUrl);

                echo '<i class="fa fa-external-link" aria-hidden="true"></i> ';
                echo $libString->protectXSS($folderElement->getName());
                echo '</a></li>' . PHP_EOL;
            }
            //login / logout
            elseif ($folderElement->getType() == 4) {
                if (!$libAuth->isLoggedin()) {
                    $this->printLinkTag($folderElement, $depth, $pid, 'index.php?pid=' .$folderElement->getPid());

                    echo $libString->protectXSS($folderElement->getName());
                } else {
                    $this->printLinkTag($folderElement, $depth, $pid, 'index.php?logout=1');

                    echo $libString->protectXSS($folderElement->getNameLogout());
                }

                echo '</a></li>' . PHP_EOL;
            }
        }
    }

    public function indent($depth = 0)
    {
        for ($i = 0; $i < $depth; $i++) {
            echo '    ';
        }
    }

    public function printLinkTag($folderElement, $depth, $pid, $url)
    {
        global $libString;

        $linkClass = ($depth == 0) ? 'ui-nav-link' : 'ui-dropdown-item';

        if ($folderElement->getPid() == $pid) {
            $linkClass .= ' active';
        }

        echo $this->defaultIndent . $this->indent($depth) . '<li>' . PHP_EOL;
        echo $this->defaultIndent . $this->indent($depth) . '  <a href="' .$libString->protectXSS((string) $url). '" class="' .$linkClass. '">';
    }

    public function getNavbarClass()
    {
        global $libAuth;

        return !$libAuth->isLoggedin() ? 'navbar-internet-only' : 'navbar-internet-intranet';
    }
}
