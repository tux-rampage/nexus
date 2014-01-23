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

namespace rampage\nexus;

use SplFileInfo;
use Phar;
use ArrayObject;

class ComposerApplicationPackage implements ApplicationPackageInterface
{
    protected $file = null;

    protected $config = null;

    /**
     * @param SplFileInfo $packageFile
     */
    public function __construct(SplFileInfo $packageFile)
    {
        $this->file = new \PharData($packageFile->getFilename());

        $json = @json_decode($this->file['composer.json']->getContent(), true);
        $this->config = is_array($json)? new ArrayObject($json, ArrayObject::ARRAY_AS_PROPS) : false;

        if (!$this->config) {
            throw new \RuntimeException('Failed to load composer.json from package');
        }
    }

    /**
     * @see \rampage\nexus\ApplicationPackageInterface::getIcon()
     */
    public function getIcon()
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\ApplicationPackageInterface::getLabel()
     */
    public function getLabel()
    {
        // TODO Auto-generated method stub
    }

    /**
     * @see \rampage\nexus\ApplicationPackageInterface::getLicense()
     */
    public function getLicense()
    {
        return $this->config->license;
    }

    /**
     * @see \rampage\nexus\ApplicationPackageInterface::getName()
     */
    public function getName()
    {
        return $this->config->name;
    }

    /**
     * @see \rampage\nexus\ApplicationPackageInterface::getParameters()
     */
    public function getParameters()
    {
        // TODO Auto-generated method stub
    }

    /**
     * @see \rampage\nexus\ApplicationPackageInterface::getVersion()
     */
    public function getVersion()
    {
        return $this->config->version;
    }

    /**
     * @see \rampage\nexus\ApplicationPackageInterface::install()
     */
    public function install(entities\Application $application)
    {
        // TODO Auto-generated method stub
    }
}
