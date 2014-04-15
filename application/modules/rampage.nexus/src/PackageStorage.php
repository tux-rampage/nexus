<?php
/**
 * This is part of rampage-nexus
 * Copyright (c) 2014 Axel Helmert
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Axel Helmert
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus;

use rampage\io\WritableLocalFilesystem;
use rampage\nexus\entities\ApplicationVersion;

use SplFileInfo;


class PackageStorage extends WritableLocalFilesystem
{
    /**
     * @param string $name
     * @return string
     */
    protected function formatName($name)
    {
        $name = strtolower($name);
        $name = preg_replace('~[^a-z0-9]+~i', '-', $name);

        return $name;
    }

    /**
     * @param ApplicationVersion $version
     * @return string
     */
    protected function packagePath(ApplicationVersion $version)
    {
        return sprintf(
            'app-%d/%s-%d-%s.rpk',
            $version->getApplication()->getId(),
            $this->formatName($version->getApplication()->getApplicationName()),
            $version->getId(),
            $version->getVersion()
        );
    }

    /**
     * @param ApplicationVersion
     */
    public function getPackageFile(ApplicationVersion $version)
    {
        $path = $this->packagePath($version);
        return $this->offsetGet($path);
    }

    /**
     * @param ApplicationVersion $version
     * @param SplFileInfo $file
     */
    public function putPackageFile(ApplicationVersion $version, SplFileInfo $package)
    {
        $path = $this->packagePath($version);
        $this->offsetSet($path, $package);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\io\WritableLocalFilesystem::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        if ($offset instanceof ApplicationVersion) {
            return $this->putPackageFile($offset, $value);
        }

        $path = dirname($offset);
        if ($path) {
            $this->mkdir($path);
        }

        return parent::offsetSet($offset, $value);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\io\LocalFilesystem::offsetGet()
     */
    public function offsetGet($offset)
    {
        if ($offset instanceof ApplicationVersion) {
            return $this->getPackageFile($offset);
        }

        return parent::offsetGet($offset);
    }
}
