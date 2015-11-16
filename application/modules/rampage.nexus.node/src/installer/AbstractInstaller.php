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

namespace rampage\nexus\node\installer;

use rampage\nexus\exceptions;
use rampage\nexus\PackageInterface;

use SplFileInfo;
use PharData;


abstract class AbstractInstaller implements InstallerInterface
{
    /**
     * @var SplFileInfo
     */
    protected $targetDirectory = null;

    /**
     * @var PharData
     */
    protected $archive;

    /**
     * @var SplFileInfo
     */
    protected $archiveInfo = null;

    /**
     * @throws exceptions\LogicException
     */
    protected function assertArchive()
    {
        if (!$this->archive || !$this->archiveInfo) {
            throw new exceptions\LogicException('Cannot operate on archive without archive reference.');
        }
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\package\ApplicationPackageInterface::setDeployStrategy()
     */
    public function setTargetDirectory(SplFileInfo $dir)
    {
        $this->targetDirectory = $dir;
        return $this;
    }

    /**
     * @see \rampage\nexus\package\InstallerInterface::setPackageFile()
     */
    public function setPackageFile(SplFileInfo $file)
    {
        if (!$file->isFile()) {
            throw new exceptions\InvalidArgumentException('No such archive: ' . $file->getPathname());
        }

        $this->archive = new PharData($file->getPathname());
    }

    /**
     * {@inheritdoc}
     */
    public function supports(PackageInterface $instance)
    {
        return ($instance->getType() == $this->getTypeName());
    }
}
