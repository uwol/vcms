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

namespace vcms\module;

class LibModule
{
    public $id;
    public $version;
    public $name;
    public $path;
    public $pages;
    public $includes;
    public $headerStrings;
    public $installScript;
    public $uninstallScript;
    public $updateScript;
    public $menuElementsInternet;
    public $menuElementsIntranet;
    public $menuElementsAdministration;

    public function __construct(
        $id,
        $name,
        $version,
        $path,
        $pages,
        $includes,
        $headerStrings,
        $installScript,
        $uninstallScript,
        $updateScript,
        $menuElementsInternet,
        $menuElementsIntranet,
        $menuElementsAdministration
    ) {
        global $libGlobal;

        if ($id == '') {
            $libGlobal->errorTexts[] = 'Fehlende Module-Id';
        }

        if ($version != '' && !is_numeric($version)) {
            $libGlobal->errorTexts[] = 'Versionsangabe nicht numerisch';
        }

        if ($name == '') {
            $libGlobal->errorTexts[] = 'Fehlende Namensangabe';
        }

        if ($path == '') {
            $libGlobal->errorTexts[] = 'Fehlender Modulpfad';
        }

        if (!is_array($pages)) {
            $libGlobal->errorTexts[] = 'Fehlendes Array pages';
        }

        if (!is_array($includes)) {
            $libGlobal->errorTexts[] = 'Fehlendes Array includes';
        }

        $this->id = $id;
        $this->version = $version;
        $this->name = $name;
        $this->path = $path;
        $this->pages = $pages;
        $this->includes = $includes;

        $this->installScript = $installScript;
        $this->uninstallScript = $uninstallScript;
        $this->updateScript = $updateScript;

        $this->menuElementsInternet = $menuElementsInternet;
        $this->menuElementsIntranet = $menuElementsIntranet;
        $this->menuElementsAdministration = $menuElementsAdministration;

        $this->headerStrings = $headerStrings;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getPages()
    {
        return $this->pages;
    }

    public function getIncludes()
    {
        return $this->includes;
    }

    public function getInstallScript()
    {
        return $this->installScript;
    }

    public function getUninstallScript()
    {
        return $this->uninstallScript;
    }

    public function getUpdateScript()
    {
        return $this->updateScript;
    }

    public function getMenuElementsInternet()
    {
        return $this->menuElementsInternet;
    }

    public function getMenuElementsIntranet()
    {
        return $this->menuElementsIntranet;
    }

    public function getMenuElementsAdministration()
    {
        return $this->menuElementsAdministration;
    }

    public function getHeaderStrings()
    {
        return $this->headerStrings;
    }
}
