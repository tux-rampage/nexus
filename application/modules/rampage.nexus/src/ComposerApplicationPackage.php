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

use ArrayObject;
use SplFileInfo;
use PharData;
use RecursiveIterator;
use RecursiveDirectoryIterator;

use RuntimeException;

class ComposerApplicationPackage implements ApplicationPackageInterface
{
    /**
     * @var PharData
     */
    protected $file = null;

    /**
     * @var array
     */
    protected $config = null;

    /**
     * @param SplFileInfo $packageFile
     */
    public function __construct(SplFileInfo $packageFile)
    {
        $this->file = new PharData($packageFile->getFilename());
        $metaData = $this->file->getMetadata();
        $composerFile = (isset($metaData['composerfile']))? $metaData['composerfile'] : 'composer.json';

        $json = @json_decode($this->file[$composerFile]->getContent(), true);
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
        if (!isset($this->config->extra['deployment']['icon'])) {
            return false;
        }

        $icon = $this->config->extra['deployment']['icon'];
        $file = ($icon && isset($this->file[$file]))? $this->file[$file] : false;

        return $file;
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
        if (!isset($this->config->extra['deployment']['parameters'])) {
            return array();
        }

        $result = array();
        $params = $this->config->extra['deployment']['parameters'];

        if (!is_array($params)) {
            return array();
        }

        foreach ($params as $name => $options) {
            $param = DeployParameter::factory($name, $options);
            $result[$name] = $param;
        }

        return $result;
    }

    /**
     * @see \rampage\nexus\ApplicationPackageInterface::getVersion()
     */
    public function getVersion()
    {
        return $this->config->version;
    }

    /**
     * @return RecursiveDirectoryIterator
     */
    public function getApplicationFiles()
    {
        if (!isset($this->config->extra['deployment']['applicationDir'])) {
            return $this->file;
        }

        $subDir = $this->config->extra['deployment']['applicationDir'];

        if (!isset($this->file[$subDir])) {
            throw new RuntimeException('Could not find application directory: ' . $subDir);
        }

        $dir = $this->file[$subDir];
        if (!$dir->isDir()) {
            throw new RuntimeException('Bad application directory: ' . $subDir . ' (not a directory)');
        }

        return new RecursiveDirectoryIterator($dir->getPathname());
    }

    /**
     * @see \rampage\nexus\ApplicationPackageInterface::install()
     */
    public function install(entities\ApplicationInstance $application)
    {
        foreach ($this->getApplicationFiles() as $file) {
        }
    }
}
