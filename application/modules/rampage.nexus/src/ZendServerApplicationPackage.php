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

namespace rampage\nexus;

use rampage\core\xml\SimpleXmlElement;
use RuntimeException;
use SplFileInfo;
use rampage\nexus\entities\ApplicationInstance;

class ZendServerApplicationPackage implements ApplicationPackageInterface
{
    const TYPENAME = 'zpk';

    /**
     * @var SimpleXmlElement
     */
    protected $deploymentXml = null;

    /**
     * @var \ZipArchive
     */
    protected $zip;

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\ApplicationPackageInterface::getIcon()
     */
    public function getIcon()
    {
        $file = (string)$this->deploymentXml->icon;
        if (!$file) {
            return false;
        }

        return $this->zip->getStream($file);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\ApplicationPackageInterface::getLicense()
     */
    public function getLicense()
    {
        $file = (string)$this->deploymentXml->eula;
        if (!$file) {
            return false;
        }

        return $this->zip->getStream($file);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\ApplicationPackageInterface::getName()
     */
    public function getName()
    {
        return (string)$this->deploymentXml->name;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\ApplicationPackageInterface::getParameters()
     */
    public function getParameters()
    {
        // TODO Auto-generated method stub

    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\ApplicationPackageInterface::getTypeName()
     */
    public function getTypeName()
    {
        return self::TYPENAME;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\ApplicationPackageInterface::getVersion()
     */
    public function getVersion()
    {
        return (string)$this->deploymentXml->version->release;
    }

    /**
     * @return string
     */
    public function getApplicationDir()
    {
        return (isset($this->deploymentXml->appdir))? (string)$this->deploymentXml->appdir : 'data';
    }

    /**
     * @return string
     */
    public function getWebRoot()
    {
        if (!isset($this->deploymentXml->webroot)) {
            return '';
        }

        $webRoot = (string)$this->deploymentXml->webroot;
        $appDir = $this->getApplicationDir();

        if (strpos($webRoot, $appDir) === 0) {
            $webRoot = trim(substr($webRoot, strlen($appDir)), '/');
        }

        return $webRoot;
    }

    /**
     * @throws \RuntimeException
     * @return self
     */
    protected function extract($destination, $dir)
    {
        if (!$dir) {
            if (!$this->zip->extractTo($destination)) {
                throw new RuntimeException('Failed to extract package content.');
            }

            return $this;
        }

        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $name = $this->zip->getNameIndex($i);
            $normalized = ltrim($name, '/');

            if (strpos($normalized, $dir) !== 0) {
                continue;
            }

            $targetPath = $destination . '/' . $normalized;
            $targetDir = dirname($targetPath);

            if (!is_dir($targetDir) && !mkdir($targetDir)) {
                throw new RuntimeException('Failed to create directory %s');
            }

            if (!$this->zip->extractTo($targetPath, $name)) {
                throw new RuntimeException(sprintf('Failed to extract "%s".', $name));
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\ApplicationPackageInterface::install()
     */
    public function install(ApplicationInstance $application)
    {
        $appDir = $this->getApplicationDir();
        $webRoot = $this->getWebRoot();
        $strategy = $application->getDeployStrategy();

        // TODO: $strategy->setUserParameters($parameters);
        $strategy->setWebRoot($webRoot);
        $strategy->prepareStaging();

        $this->extract($strategy->getTargetDirectory(), $appDir);

        $strategy->completeStaging();
        $strategy->activate();

        $application->setState(ApplicationInstance::STATE_DEPLOYED);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\ApplicationPackageInterface::load()
     */
    public function load(SplFileInfo $package)
    {
        $path = $package->getFilename();
        $this->zip = new \ZipArchive();

        if ($this->zip->open($package) !== true) {
            throw new RuntimeException(sprintf('Failed to open deployment package "%s"', $path));
        }

        $xml = $this->zip->getFromName('deployment.xml');
        if (!$xml) {
            throw new RuntimeException('Could not find deployment.xml in package file.');
        }

        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        if (!$dom->schemaValidate(dirname(__DIR__) . '/resource/xsd/zpk.xsd')) {
            throw new RuntimeException('Invalid deployment.xml');
        }

        $this->deploymentXml = simplexml_load_string($xml, SimpleXmlElement::class);
        if (!$this->deploymentXml instanceof SimpleXmlElement) {
            throw new RuntimeException('Failed to load deployment.xml from package file.');
        }
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\ApplicationPackageInterface::remove()
     */
    public function remove(ApplicationInstance $application)
    {
        $strategy = $application->getDeployStrategy();

        $this->trigger('pre_deactivate', $application);
        $strategy->deactivate();
        $this->trigger('post_deactivate', $application);

        $this->trigger('pre_unstage', $application);
        $strategy->prepareRemoval();
        $strategy->completeRemoval();
        $this->trigger('post_unstage', $application);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\ApplicationPackageInterface::supports()
     */
    public function supports(SplFileInfo $package)
    {
        $zip = new \ZipArchive();

        if ($zip->open($package->getFilename()) !== true) {
            return false;
        }

        return ($zip->statName('deployment.xml') !== false);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\ApplicationPackageInterface::validateUserOptions()
     */
    public function validateUserOptions(ApplicationInstance $application)
    {
        // TODO Auto-generated method stub
    }
}
