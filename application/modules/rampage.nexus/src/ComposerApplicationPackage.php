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

use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator as validation;

class ComposerApplicationPackage implements PackageInstallerInterface
{
    /**
     * @var PharData
     */
    protected $file = null;

    /**
     * @var string
     */
    protected $hash = null;

    /**
     * @var array
     */
    protected $config = null;

    /**
     * @var InputFilter
     */
    protected $paramInputFilter = null;

    /**
     * @param SplFileInfo $archive
     */
    public function load(SplFileInfo $archive)
    {
        $this->file = new PharData($archive->getPathname());
        $this->hash = md5_file($archive->getPathname());
        $metaData = $this->file->getMetadata();
        $composerFile = 'composer.json';

        if (isset($metaData['deployment-config'])) {
            $composerFile = $metaData['deployment-config'];
        }

        $json = @json_decode($this->file[$composerFile]->getContent(), true);
        $this->config = is_array($json)? new ArrayObject($json, ArrayObject::ARRAY_AS_PROPS) : false;

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
     * Trigger deploy script
     *
     * @param string $script
     * @return self
     */
    protected function trigger($script)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\PackageInstallerInterface::getTypeName()
     */
    public function getTypeName()
    {
        return 'composer';
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
        if (!isset($this->config->extra['deployment'][$name])) {
            return $default;
        }

        return $this->config->extra['deployment'][$name];
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
        $result = array();
        $params = $this->getOption('parameters', array());

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
     * @see \rampage\nexus\PackageInstallerInterface::getVersion()
     */
    public function getVersion()
    {
        return $this->config->version;
    }

    /**
     * @return RecursiveDirectoryIterator
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
     * @return \Zend\InputFilter\InputFilter
     */
    protected function getParamInputFilter()
    {
        if ($this->paramInputFilter) {
            return $this->paramInputFilter;
        }

        $this->paramInputFilter = new InputFilter();
        foreach ($this->getParameters() as $parameter) {
            $input = new Input($parameter->getName());

            $input->setValidatorChain($parameter->getValidatorChain());
            $input->setRequired($parameter->isRequired());

            if ($parameter->getType() == DeployParameter::TYPE_SELECT) {
                $input->getFilterChain()->attach(new validation\InArray($parameter->getOptions()));
            }
        }

        return $this->paramInputFilter;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\PackageInstallerInterface::validateUserOptions()
     * @param \rampage\nexus\entities\ApplicationInstance $application
     */
    public function validateUserOptions(entities\ApplicationInstance $application)
    {
        /* @var $application entities\ApplicationInstance */
        $input = $application->getCurrentVersion()->getUserParameters(true);
        $filter = $this->getParamInputFilter();

        return $filter->setData($input)->isValid();
    }

    /**
     * @see \rampage\nexus\PackageInstallerInterface::install()
     */
    public function install(entities\ApplicationInstance $application)
    {
        if (!$this->validateUserOptions($application)) {
            $messages = $this->getParamInputFilter()->getMessages();
            foreach ($messages as $field => $sub) {
                $glue = "\n" . str_repeat(' ', strlen($messages) + 4);
                $messages[$field] = '- ' . $field . ': ' . implode($glue, $sub);
            }

            throw new RuntimeException(sprintf(
                'Invalid user parameters: %s',
                "\n" . implode("\n", $this->getParamInputFilter()->getMessages())
            ));
        }

        $webRoot = $this->getWebRoot();
        $strategy = $application->getDeployStrategy();
        $parameters = $this->getParamInputFilter()->getValues();

        $strategy->setUserParameters($parameters);
        $strategy->setWebRoot($webRoot);
        $strategy->prepareStaging();

        $target = $strategy->getTargetDirectory();

        if ($this->isStandalone()) {
            $pharName = $this->getOption('pharName');
            if (!$pharName) {
                $pharName = $this->file->getFilename();
            }

            mkdir($target . '/' . $webRoot);
            $this->file->extractTo($target . '/' . $webRoot, $this->getApplicationDir());
            rename($this->file->getPathname(), $target . '/' . $pharName);
        } else {
            $this->file->extractTo($target, $this->getApplicationDir());
        }

        $strategy->completeStaging();
        $strategy->activate();

        $application->setState(entities\ApplicationInstance::STATE_DEPLOYED);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\PackageInstallerInterface::remove()
     */
    public function remove(entities\ApplicationInstance $application)
    {
        // TODO: Remove application
    }
}
