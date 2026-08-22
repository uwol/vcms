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

class LibFilesystem
{
    public $baseDir;

    public function __construct($baseDir)
    {
        $this->baseDir = realpath($baseDir);
    }

    public function getAbsolutePath($relativePath)
    {
        return $this->baseDir. '/' .$relativePath;
    }

    public function deleteDirectory($relativePath)
    {
        $absolutePath = $this->getAbsolutePath($relativePath);

        if (is_dir($absolutePath)) {
            $files = array_diff(scandir($absolutePath), ['.', '..']);

            foreach ($files as $file) {
                $relativeFilePath = $relativePath. '/' .$file;
                $absoluteFilePath = $absolutePath. '/' .$file;

                if (is_dir($absoluteFilePath)) {
                    $this->deleteDirectory($relativeFilePath);
                } elseif (is_file($absoluteFilePath)) {
                    unlink($absoluteFilePath);
                }
            }

            if (is_dir($absolutePath)) {
                rmdir($absolutePath);
            }
        }
    }

    public function copyDirectory($relativeSourcePath, $relativeDestPath)
    {
        $absoluteSourcePath = $this->getAbsolutePath($relativeSourcePath);
        $absoluteDestPath = $this->getAbsolutePath($relativeDestPath);

        if (!is_dir($absoluteDestPath)) {
            mkdir($absoluteDestPath);
        }

        $files = array_diff(scandir($absoluteSourcePath), ['.', '..']);

        foreach ($files as $file) {
            $relativeFileSourcePath = $relativeSourcePath. '/' .$file;
            $absoluteFileSourcePath = $absoluteSourcePath. '/' .$file;

            $relativeFileDestPath = $relativeDestPath. '/' .$file;
            $absoluteFileDestPath = $absoluteDestPath. '/' .$file;

            if (is_dir($absoluteFileSourcePath)) {
                $this->copyDirectory($relativeFileSourcePath, $relativeFileDestPath);
            } else {
                copy($absoluteFileSourcePath, $absoluteFileDestPath);
            }
        }
    }

    public function mergeDirectory($relativeSourcePath, $relativeDestPath)
    {
        $absoluteSourcePath = $this->getAbsolutePath($relativeSourcePath);
        $absoluteDestPath = $this->getAbsolutePath($relativeDestPath);

        if (!is_dir($absoluteDestPath)) {
            mkdir($absoluteDestPath);
        }

        $files = array_diff(scandir($absoluteSourcePath), ['.', '..']);

        foreach ($files as $file) {
            $relativeFileSourcePath = $relativeSourcePath. '/' .$file;
            $absoluteFileSourcePath = $absoluteSourcePath. '/' .$file;

            $relativeFileDestPath = $relativeDestPath. '/' .$file;
            $absoluteFileDestPath = $absoluteDestPath. '/' .$file;

            if (is_dir($absoluteFileSourcePath)) {
                $this->mergeDirectory($relativeFileSourcePath, $relativeFileDestPath);
            } elseif (!file_exists($absoluteFileDestPath)) {
                copy($absoluteFileSourcePath, $absoluteFileDestPath);
            }
        }
    }
}
