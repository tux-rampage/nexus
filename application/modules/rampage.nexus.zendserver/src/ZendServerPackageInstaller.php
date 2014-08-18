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

namespace rampage\nexus\zs;

use rampage\core\xml\SimpleXmlElement;

use rampage\nexus\PackageInstallerInterface;
use rampage\nexus\DeployParameter;
use rampage\nexus\entities\ApplicationInstance;

use Zend\Validator as validators;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;

use RuntimeException;
use SplFileInfo;


class ZendServerPackageInstaller implements PackageInstallerInterface
{
    const TYPENAME = 'zpk';

    /**
     * @var SimpleXmlElement
     */
    protected $deploymentXml = null;

    /**
     * @var \ZipArchive
     */
    protected $zip = null;

    /**
     * @var string
     */
    protected $hash = null;

    /**
     * @var string
     */
    private $tempScriptsDir = null;

    /**
     * @var Config
     */
    protected $options = null;

    /**
     * @var array
     */
    protected $variables = array();

    protected $parameters = null;

    /**
     * @var InputFilter
     */
    protected $paramInputFilter = null;

    /**
     * @param array|DeploymentConfig $options
     */
    public function __construct(Config $options)
    {
        $this->options = $options;
    }

    /**
     * @return self
     */
    protected function extractScriptsDir()
    {
        if ($this->tempScriptsDir !== null) {
            return $this;
        }

        $scripts = isset($this->deploymentXml->scriptsdir)? (string)$this->deploymentXml->scriptsdir : 'scripts';
        $dir = getenv('TMP')? : '/tmp';
        $this->tempScriptsDir = tempnam($dir, 'zpk.scripts');
        $this->extract($this->tempScriptsDir, $scripts);

        return $this;
    }

    /**
     * Trigger deploy script
     *
     * @param string $script
     * @return self
     */
    protected function triggerDeployScript($script, $application)
    {
        $this->extractScriptsDir();
        $file = $this->tempScriptsDir . '/' . $script . '.php';

        if (!file_exists($file)) {
            return $this;
        }

        $exec = new StageScript($file, $application, $this->options, $this->variables);

        if (!$exec->execute(true)) {
            // TODO: Logging
            throw new RuntimeException(sprintf('Stage script %s failed.', $script));
        }

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
     * {@inheritdoc}
     * @see \rampage\nexus\PackageInstallerInterface::getIcon()
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
     * @see \rampage\nexus\PackageInstallerInterface::getLicense()
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
     * @see \rampage\nexus\PackageInstallerInterface::getName()
     */
    public function getName()
    {
        return (string)$this->deploymentXml->name;
    }

    protected function getParameterTypeValidator($type)
    {
        switch ($type) {
            case 'email':
                return new validators\EmailAddress();

            case 'number':
                return new validators\Digits();

            case 'hostname':
                return new validators\Hostname();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\PackageInstallerInterface::getParameters()
     */
    public function getParameters()
    {
        if ($this->parameters !== null) {
            return $this->parameters;
        }

        $this->parameters = array();

        foreach ($this->deploymentXml->xpath('./parameters/parameter') as $parameterNode) {
            $name = (string)$parameterNode['id'];
            $type = (string)$parameterNode['type'];
            $options = array(
                'label' => (string)$parameterNode['display'],
                'required' => ($parameterNode['required'] == 'true'),
                'readonly' => ($parameterNode['readonly'] == 'true'),
                'default' => (string)$parameterNode->defaultvalue,
                'validators' => array()
            );

            switch ($type) {
                case 'choice':
                    $options['type'] = DeployParameter::TYPE_SELECT;
                    $options['options'] = array();

                    foreach ($parameterNode->xpath('./validation/enums/enum') as $enum) {
                        $enumValue = (string)$enum;
                        $options[$enumValue] = $enumValue;
                    }

                    break;

                case 'checkbox':
                    $options['type'] = DeployParameter::TYPE_CHECKBOX;
                    break;

                case 'password':
                    $options['type'] = DeployParameter::TYPE_PASSWORD;
                    break;

                case 'email': // break intentionally omitted
                case 'number': // break intentionally omitted
                case 'hostname': // break intentionally omitted
                case 'string': // break intentionally omitted
                default:
                    $options['type'] = DeployParameter::TYPE_TEXT;
                    $validator = $this->getParameterTypeValidator($type);
                    if ($validator) {
                        $options['validators'][] = $validator;
                    }

            }

            $this->parameters[] = DeployParameter::factory($name, $options);
        }

        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\PackageInstallerInterface::getTypeName()
     */
    public function getTypeName()
    {
        return self::TYPENAME;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\PackageInstallerInterface::getVersion()
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

            if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
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
     * @see \rampage\nexus\PackageInstallerInterface::install()
     */
    public function install(ApplicationInstance $application)
    {
        $appDir = $this->getApplicationDir();
        $webRoot = $this->getWebRoot();
        $strategy = $application->getDeployStrategy();

        $this->triggerDeployScript('pre_stage', $application);
        $strategy->setWebRoot($webRoot);
        $strategy->prepareStaging();

        $this->extract($strategy->getTargetDirectory(), $appDir);

        $strategy->completeStaging();
        $this->triggerDeployScript('post_stage', $application);

        $this->triggerDeployScript('pre_activate', $application);
        $strategy->activate();
        $this->triggerDeployScript('post_activate', $application);

        $application->setState(ApplicationInstance::STATE_DEPLOYED);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\PackageInstallerInterface::load()
     */
    public function load(SplFileInfo $package)
    {
        $path = $package->getPathname();
        $this->zip = new \ZipArchive();
        $this->hash = md5_file($path);
        $this->variables = array();
        $this->parameters = null;
        $this->paramInputFilter = null;

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

        foreach ($this->deploymentXml->xpath('./variables/variable') as $variableNode) {
            $name = (string)$variableNode['name'];
            $this->variables[$name] = (string)$variableNode['value'];
        }
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\PackageInstallerInterface::remove()
     */
    public function remove(ApplicationInstance $application)
    {
        $strategy = $application->getDeployStrategy();

        $this->triggerDeployScript('pre_deactivate', $application);
        $strategy->deactivate();
        $this->triggerDeployScript('post_deactivate', $application);

        $this->triggerDeployScript('pre_unstage', $application);
        $strategy->prepareRemoval();
        $strategy->completeRemoval();
        $this->triggerDeployScript('post_unstage', $application);
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\PackageInstallerInterface::supports()
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

            switch ($parameter->getType()) {
                case DeployParameter::TYPE_SELECT:
                    $input->getFilterChain()->attach(new validators\InArray($parameter->getOptions()));
                    break;
            }
        }

        return $this->paramInputFilter;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\PackageInstallerInterface::validateUserOptions()
     */
    public function validateUserOptions(ApplicationInstance $application)
    {
        $input = $application->getCurrentVersion()->getUserParameters(true);
        $filter = $this->getParamInputFilter();

        return $filter->setData($input)->isValid();
    }
}
