<?php
/**
 * This is part of rampage-nexus
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

use RuntimeException;
use Zend\Form\Annotation\AnnotationBuilder as AnnotationFormBuilder;

/**
 * FPM web configuration
 */
class FPMWebConfig implements WebConfigInterface
{
    /**
     * @var string
     */
    protected $serviceCommand = 'service php-fpm';

    /**
     * @var string
     */
    protected $configFileFormat = '/etc/php5/fpm/pool.d/%pool%.conf';

    /**
     * @var options\FPMOptions
     */
    protected $options = null;

    /**
     * @var \Zend\Form\Form
     */
    protected $optionsForm = null;

    /**
     * @var entities\ApplicationInstance
     */
    protected $application = null;

    /**
     * @var ConfigTemplateLocator
     */
    protected $templateLocator = null;

    /**
     * @param ConfigTemplateLocator $templateLocator
     */
    public function __construct(ConfigTemplateLocator $templateLocator = null)
    {
        $this->templateLocator = $templateLocator? : new ConfigTemplateLocator();
        $this->options = new options\FPMOptions();
    }

    /**
     * @param array $options
     * @param ConfigTemplateLocator $templateLocator
     * @return \rampage\nexus\FPMWebConfig
     */
    public static function factory(array $options, ConfigTemplateLocator $templateLocator = null)
    {
        $instance = new self($templateLocator);

        if (isset($options['configFileFormat'])) {
            $instance->configFileFormat = $options['configFileFormat'];
        }

        return $instance;
    }

    /**
     * @param string $action
     * @throws RuntimeException
     * @return \rampage\nexus\FPMWebConfig
     */
    protected function serviceControl($action)
    {
        $cmd = $this->serviceCommand . ' ' . escapeshellarg($action);
        $return = 1;
        $out = array();

        exec($cmd, $out, $return);

        if ($return != 0) {
            throw new RuntimeException('Failed to invoke service action ' . $action . ":\n" . implode("\n", $out));
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function createPoolConfig($webRoot, $appRoot)
    {
        $config = $this->templateLocator->resolve('fpm/pool');
        $params = array(
            'pool' => $this->application->getName(),
            'docroot' => $webRoot,
            'approot' => $appRoot
        );

        $params = array_merge($this->options->toArray(), $params);
        return $config->setVariables($params)->render();
    }

    /**
     * @return string
     */
    protected function createMaintenanceConfig()
    {
        $config = $this->templateLocator->resolve('fpm/maintenance');
        $params = $this->options->toArray();

        $params['pool'] = $this->application->getName();

        return $config->setVariables($params)->render();
    }

    /**
     * @param string $content
     * @throws RuntimeException
     * @return self
     */
    public function savePoolConfig($content)
    {
        if (!file_put_contents($this->getConfigFile(), $content)) {
            $error = new LastPhpError();
            throw new RuntimeException(sprintf('Failed to save pool config "%s": %s', $this->getPoolName(), $error->getMessage()));
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function getConfigFile()
    {
        $file = $this->configFileFormat;
        $file = str_replace('%pool%', $this->getPoolName(), $file);

        return $file;
    }

    /**
     * @return string
     */
    protected function getPoolName()
    {
        $name = $this->application->getName();
        return $name;
    }

    /**
     * @see \rampage\nexus\WebConfigInterface::getOptionsForm()
     */
    public function getOptionsForm()
    {
        if (!$this->optionsForm) {
            $builder = new AnnotationFormBuilder();
            $this->optionsForm = $builder->createForm($this->options);
        }

        return $this->optionsForm;
    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::activate()
     */
    public function activate()
    {
        $this->serviceControl('reload');
    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::configure()
     */
    public function configure(DeployStrategyInterface $strategy)
    {
        $config = $this->createPoolConfig($strategy->getWebRoot(), $strategy->getTargetDirectory());
        $this->savePoolConfig($config);

        return $this;
    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::maintenance()
     */
    public function maintenance()
    {
        $this->savePoolConfig($this->createMaintenanceConfig());
        $this->serviceControl('reload');

        return $this;
    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::remove()
     */
    public function remove()
    {
        unlink($this->getConfigFile());
        $this->serviceControl('reload');

        return $this;
    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::setApplication()
     */
    public function setApplication(entities\ApplicationInstance $application)
    {
        $this->application = $application;
        $this->templateLocator->setApplication($application);

        return $this;
    }

    /**
     * @param array $options
     * @return \rampage\nexus\FPMWebConfig
     */
    public function setOptions(array $options)
    {
        $form =$this->getOptionsForm()
            ->setData($options);

        if (!$form->isValid()) {
            throw new \InvalidArgumentException('Invalid web config options');
        }

        $form->bind($this->options);
        return $this;
    }
}
