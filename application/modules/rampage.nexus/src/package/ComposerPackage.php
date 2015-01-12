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

use ArrayObject;
use SplFileInfo;
use PharData;
use RecursiveIterator;
use RecursiveDirectoryIterator;
use RuntimeException;

use rampage\nexus\ArrayConfig;
use rampage\nexus\DeployParameter;
use rampage\nexus\entities\Application;


class ComposerPackage extends AbstractApplicationPackage
{
    const TYPE_NAME = 'composer';

    /**
     * @var PharData
     */
    protected $file = null;

    /**
     * @var string
     */
    protected $hash = null;

    /**
     * @var ArrayConfig
     */
    protected $config = null;

    /**
     * @var array
     */
    protected $parameters = null;

    /**
     * @param SplFileInfo $archive
     */
    protected function load(SplFileInfo $archive)
    {
        $this->file = new PharData($archive->getPathname());
        $this->hash = md5_file($archive->getPathname());
        $metaData = $this->file->getMetadata();
        $composerFile = 'composer.json';

        if (isset($metaData['deployment-config'])) {
            $composerFile = $metaData['deployment-config'];
        }

        $json = @json_decode($this->file[$composerFile]->getContent(), true);
        $this->config = is_array($json)? new ArrayConfig($json) : false;

        if (!$this->config) {
            throw new \RuntimeException('Failed to load deployment definition from package');
        }

        return $this;
    }

    /**
     * @param SplFileInfo $package
     * @return bool
     */
    public function supports(SplFileInfo $archive)
    {
        try {
            $this->file = new PharData($archive->getFilename());
            $metaData = $this->file->getMetadata();
            $composerFile = (isset($metaData['deployment-config']))? $metaData['deployment-config'] : 'composer.json';
            $json = @json_decode($this->file[$composerFile]->getContent(), false);

            if (!is_object($json)) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\package\ApplicationPackageInterface::create()
     */
    public function create(SplFileInfo $archive)
    {
        $package = new self();
        $package->load($archive);

        return $this;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\PackageInstallerInterface::getHash()
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function getOption($name, $default = null)
    {
        return $this->config->getSection('extra.deployment')->get($name, $default);
    }

    /**
     * @see \rampage\nexus\PackageInstallerInterface::getIcon()
     */
    public function getIcon()
    {
        $icon = $this->getOption('icon', false);
        $file = ($icon && isset($this->file[$icon]))? $this->file[$icon] : false;

        return $file;
    }

    /**
     * @see \rampage\nexus\PackageInstallerInterface::getLicense()
     */
    public function getLicense()
    {
        return $this->config->license;
    }

    /**
     * @see \rampage\nexus\PackageInstallerInterface::getName()
     */
    public function getName()
    {
        return $this->config->name;
    }

    /**
     * @see \rampage\nexus\PackageInstallerInterface::getParameters()
     */
    public function getParameters()
    {
        if ($this->parameters === null) {
            $this->parameters = array();
            $params = $this->config->getSection('extra.deployment.parameters');

            foreach ($params as $name => $options) {
                $param = DeployParameter::factory($name, ($options instanceof ArrayConfig)? $options->toArray() : array());
                $this->parameters[$name] = $param;
            }
        }

        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->config->version;
    }

    /**
     * @return string
     */
    public function getApplicationDir()
    {
        $subDir = $this->getOption('applicationDir');
        if ($subDir === null) {
            return null;
        }

        $subDir = trim((string)$subDir, '/');

        if (!isset($this->file[$subDir]) || !$this->file[$subDir]->isDir()) {
            throw new RuntimeException('Could not find application directory: ' . $subDir);
        }

        return $subDir;
    }

    /**
     * Document root (relative)
     *
     * @return string
     */
    public function getWebRoot()
    {
        $root = $this->getOption('webRoot');

        if ($root !== null) {
            $root = trim($root, '/');
        }

        return $root;
    }

    /**
     * @return boolean
     */
    public function isStandalone()
    {
        return (bool)$this->getOption('standalone');
    }

    /**
     * @see \rampage\nexus\PackageInstallerInterface::install()
     */
    public function install(Application $application)
    {
        $webRoot = $this->getWebRoot();
        $target = $this->deployStrategy->getTargetDirectory();

        if ($this->isStandalone()) {
            $pharName = $this->getOption('pharName');
            if (!$pharName) {
                $pharName = $this->file->getFilename();
            }

            if ($webRoot != '') {
                mkdir($target . '/' . $webRoot);

                $prefix = $this->getApplicationDir();
                $prefix .= ($prefix)? '/' : '';

                $this->file->extractTo($target . '/' . $webRoot, $prefix . $webRoot);
            }

            rename($this->file->getPathname(), $target . '/' . $pharName);
        } else {
            $this->file->extractTo($target, $this->getApplicationDir());
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\PackageInstallerInterface::remove()
     */
    public function remove(Application $application)
    {
        // Nothing to do
    }
}
