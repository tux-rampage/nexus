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

use rampage\nexus\exceptions;
use rampage\nexus\entities\ApplicationInstance;
use rampage\nexus\entities\ApplicationPackage;
use rampage\nexus\entities\PackageParameter;

use rampage\core\GracefulArrayAccess;

use Zend\Json\Json;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodHydrator;

use SplFileInfo;
use PharData;
use RuntimeException;


class ComposerInstaller extends AbstractInstaller
{
    const TYPE_NAME = ComposerPackage::TYPE_COMPOSER;

    /**
     * @var PharData
     */
    protected $archive = null;

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
     * @see \rampage\nexus\package\InstallerInterface::getPackage()
     */
    public function getPackage()
    {
        if (!isset($this->archive['composer.json'])) {
            throw new RuntimeException(sprintf('Could not find composer.json in package file "%s"', $this->archive->getAlias()));
        }

        $json = Json::decode($this->archive['composer.json']->getContents(), Json::TYPE_ARRAY);

        if (isset($json['extra']['deployment']) && is_string($json['extra']['deployment'])) {
            $deploymentFile = $json['extra']['deployment'];

            if (!isset($this->archive[$deploymentFile])) {
                throw new RuntimeException(sprintf(
                    'Could not find referenced deployment file "%s" in package file "%s"',
                    $this->archive->getAlias()
                ));
            }

            $json['extra']['deployment'] = Json::decode($this->archive[$deploymentFile]->getContents(), Json::TYPE_ARRAY);
        }

        return new ComposerPackage($json);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\package\ApplicationPackageInterface::getTypeName()
     */
    public function getTypeName()
    {
        return static::TYPE_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getWebRoot(ApplicationInstance $application)
    {
        // TODO Auto-generated method stub

    }

    /**
     * {@inheritdoc}
     */
    public function install(ApplicationInstance $application)
    {
        // TODO Auto-generated method stub

    }

    /**
     * {@inheritdoc}
     */
    public function remove(ApplicationInstance $application)
    {
        // TODO Auto-generated method stub

    }

    /**
     * {@inheritdoc}
     */
    public function rollback(ApplicationInstance $application)
    {
        // TODO Auto-generated method stub

    }
}
