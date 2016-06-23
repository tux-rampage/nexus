<?php
/**
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

namespace Rampage\Nexus\Package\Installer;

use Rampage\Nexus\Exception;
use Rampage\Nexus\Package\PackageInterface;
use Rampage\Nexus\Package\ArchiveLoaderInterface;

use SplFileInfo;
use PharData;


/**
 * Abstratc installer implementation
 */
abstract class AbstractInstaller implements InstallerInterface
{
    /**
     * @var SplFileInfo
     */
    protected $targetDirectory = null;

    /**
     * @var ArchiveLoaderInterface
     */
    protected $archiveLoader;

    /**
     * @var PharData
     */
    protected $archive = null;

    /**
     * @var SplFileInfo
     */
    protected $archiveInfo = null;

    /**
     * @param ArchiveLoaderInterface $archiveLoader
     */
    public function __construct(ArchiveLoaderInterface $archiveLoader)
    {
        $this->archiveLoader = $archiveLoader;
    }

    /**
     * @throws Exception\LogicException
     */
    protected function assertArchive()
    {
        if (!$this->archive || !$this->archiveInfo) {
            throw new Exception\LogicException('Cannot operate on archive without archive reference.');
        }
    }

    /**
     * @param bool $checkExistence
     */
    protected function assertTargetDirectory($checkExistence = false)
    {
        if (!$this->targetDirectory) {
            throw new Exception\LogicException('Cannot operate on archive without target directory.');
        }

        if ($checkExistence && !$this->targetDirectory->isDir()) {
            throw new Exception\RuntimeException(sprintf('The target directory "%s" does not exist or is not a directory.', (string)$this->targetDirectory));
        }
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Package\Installer\InstallerInterface::setTargetDirectory()
     */
    public function setTargetDirectory(SplFileInfo $dir)
    {
        $this->targetDirectory = $dir;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Package\Installer\InstallerInterface::setPackage()
     */
    public function setPackage(PackageInterface $package)
    {
        $this->package = $package;
        $this->archiveInfo = $this->archiveLoader->ensureLocalArchiveFile($package->getArchive());
        $this->archive = new PharData($this->archiveInfo->getPathname());
    }
}
