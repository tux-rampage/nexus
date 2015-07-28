<?php
/**
 * This is part of rampage-nexus
 * Copyright (c) 2013 Axel Helmert
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
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\package;

use rampage\nexus\entities;
use rampage\nexus\PackageInterface;

use SplFileInfo;

/**
 * Interface for implementing application packages
 */
interface InstallerInterface
{
    /**
     * Check if this installer supports the given package
     *
     * @param ApplicationInstance $package
     * @return boolean
     */
    public function supports(PackageInterface $package);

    /**
     * Set the package file/archive to operate on
     *
     * @param SplFileInfo $file
     */
    public function setPackageFile(SplFileInfo $file);

    /**
     * Returns the package by reading it from the package file
     *
     * @return PackageInterface
     */
    public function getPackage();

    /**
     * Set the target directory to install to
     *
     * @param SplFileInfo $dir
     */
    public function setTargetDirectory(SplFileInfo $dir);

    /**
     * Returns the common name of this package type
     *
     * Like zpk, composer, etc ...
     *
     * @return string
     */
    public function getTypeName();

    /**
     * Returns the relative web root path.
     *
     * If the return value is NULL or empty, the deploy strategy may assume that the application directory is the web root.
     *
     * @param entities\ApplicationInstance
     * @return string|null
     */
    public function getWebRoot(entities\ApplicationInstance $application);

    /**
     * Install this application package for the given application
     *
     * @param entities\ApplicationInstance $application
     * @return self
     */
    public function install(entities\ApplicationInstance $application);

    /**
     * Remove this application
     *
     * @param entities\ApplicationInstance $application
     */
    public function remove(entities\ApplicationInstance $application);

    /**
     * Remove this application
     *
     * @param entities\ApplicationInstance $application
     */
    public function rollback(entities\ApplicationInstance $application);
}
