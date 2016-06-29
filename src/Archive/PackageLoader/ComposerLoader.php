<?php
/**
 * Copyright (c) 2016 Axel Helmert
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
 * @copyright Copyright (c) 2016 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\Archive\PackageLoader;

use Rampage\Nexus\Exception\InvalidArgumentException;
use Rampage\Nexus\Package\ComposerPackage;

use PharData;
use Throwable;


/**
 * Implements the package loader for composer packages
 */
class ComposerLoader implements PackageLoaderInterface
{
    /**
     * The deployment descriptor filename
     */
    const DESCRIPTOR_FILE = 'composer.json';

    /**
     * @param   PharData                    $archive
     * @throws  InvalidArgumentException
     * @return  ZpkPackage
     */
    protected function read(PharData $archive)
    {
        if (!$archive->offsetExists(static::DESCRIPTOR_FILE)) {
            throw new InvalidArgumentException('The Archive does not contain a composer file');
        }

        return new ComposerPackage($archive->offsetGet(static::DESCRIPTOR_FILE)->getContent());
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Archive\PackageLoader\PackageLoaderInterface::canReadFromArchive()
     */
    public function canReadFromArchive(PharData $archive)
    {
        try {
            $this->read($archive);
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Archive\PackageLoader\PackageLoaderInterface::load()
     */
    public function load(PharData $archive)
    {
        $package = $this->read($archive);
        $package->setArchive($archive->getPathname());

        return $package;
    }
}
