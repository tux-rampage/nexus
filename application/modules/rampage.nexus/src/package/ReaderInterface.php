<?php
/**
 * Copyright (c) 2015 Axel Helmert
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
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\package;

use SplFileInfo;

use rampage\nexus\PackageInterface;


interface ReaderInterface
{
    /**
     * Read the application package information from the archive
     *
     * @param SplFileInfo $archive The archive file
     * @return PackageInterface
     */
    public function readFromPackage(SplFileInfo $archive);

    /**
     * Read the application package from the descriptor string
     *
     * @param string $descriptor
     * @return PackageInterface
     */
    public function readFromDescriptor($descriptor);
}
