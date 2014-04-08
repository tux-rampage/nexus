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

use Zend\Form\Annotation\AnnotationBuilder as AnnotationFormBuilder;

use Traversable;
use RuntimeException;
use LogicException;
use SplFileInfo;

/**
 * FPM web configuration
 */
class NginxWebConfig implements WebConfigInterface, VHostCapableInterface
{
    /**
     * @var string
     */
    protected $serviceCmd = 'service nginx';

    /**
     * @var string[]
     */
    protected $paths = array(
        'confdir' => '/etc/rampage-nexus/nginx/sites/%vhost%',
        'serverconfig' => '/etc/rampage-nexus/nginx/vhosts/%vhost%_%port%.conf',
        'rootconfig' => '%confdir%/conf.d/90-%appname%.conf',
        'aliasconfig' => '%confdir%/conf.d/50-%appname%.conf'
    );

    /**
     * @var entities\ApplicationInstance
     */
    protected $application = null;

    /**
     * @var options\NginxOptions
     */
    protected $options = null;

    /**
     * @var \Zend\Form\Form
     */
    protected $optionsForm = null;

    /**
     * @var ConfigTemplateLocator
     */
    protected $configTemplateLocator = null;

    /**
     * @param ConfigTemplateLocator $locator
     * @return \rampage\nexus\NginxWebConfig
     */
    public function __construct(ConfigTemplateLocator $locator)
    {
        $this->configTemplateLocator = $locator;
        $this->options = new options\NginxOptions();

        return $this;
    }

    /**
     * @param array $options
     * @param ConfigTemplateLocator $templateLocator
     * @return \rampage\nexus\FPMWebConfig
     */
    public static function factory(array $options, ConfigTemplateLocator $templateLocator = null)
    {
        $instance = new self($templateLocator);

        if (isset($options['paths']) && (is_array($options['paths']) || ($options['paths'] instanceof Traversable))) {
            foreach ($options['paths'] as $type => $path) {
                if (!isset($this->paths[$type])) {
                    continue;
                }

                $this->paths[$type] = $path;
            }
        }

        return $instance;
    }

    /**
     * @param string $action
     * @throws RuntimeException
     * @return self
     */
    protected function serviceControl($action)
    {
        $cmd = $this->serviceCmd . ' ' . escapeshellarg($action);
        $result = 1;
        $output = array();

        exec($cmd, $output, $result);

        if ($result != 0) {
            throw new RuntimeException(sprintf(
                'Failed to invoke service action "%s": %s',
                $action, "\n" . implode("\n", $output)
            ));
        }

        return $this;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getPath($name)
    {
        $format = $this->paths[$name];
        $vhost = $this->getApplication()->getVirtualHost();

        $params = array(
            '%vhost%' => $vhost->getServerName(),
            '%port%' => $vhost->getPort(),
            '%appname%' => $this->getApplication()->getName()
        );

        if ($name != 'confdir') {
            $params['%confdir%'] = $this->getPath('confdir');
        }

        return str_replace(array_keys($params), array_values($params), $format);
    }

    /**
     * @param entities\VirtualHost $vhost
     * @return string
     */
    protected function createVhostConfig(entities\VirtualHost $vhost)
    {
        /* @var $vhost entities\VirtualHost */
        $template = $vhost->isSslEnabled()? 'nginx/vhost-ssl' : 'nginx/vhost';
        $config = $this->configTemplateLocator->resolve($template);
        $names = $vhost->getAliases();

        array_unshift($names, $vhost->getServerName());

        $options = array(
            'servernames' => implode(' ', $names),
            'port' => $vhost->getPort(),
            'sslcert' => $vhost->getSslCertFile(),
            'sslkey' => $vhost->getSslKeyFile(),
            'sslchain' => $vhost->getSslChainFile()
        );

        return $config->setVariables($options)->render();
    }

    /**
     * @see \rampage\nexus\WebConfigInterface::getOptionsForm()
     */
    public function getOptionsForm()
    {
        if (!$this->optionsForm) {
            $builder = new AnnotationFormBuilder();
            $this->optionsForm = $builder->createForm($builder);
            $this->optionsForm->bind($this->options);
        }

        return $this->optionsForm;
    }

	/**
     * @see \rampage\nexus\WebConfigInterface::setOptions()
     */
    public function setOptions(array $options)
    {
        $form = $this->getOptionsForm();

        if (!$form->setData($options)->isValid()) {
            throw new RuntimeException('Invalid Options');
        }

        $form->bindValues();
        return $this;
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
     * @return boolean
     */
    protected function hasVHostConfig()
    {
        $config = $this->getPath('serverconfig');
        return file_exists($config);
    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::configure()
     */
    public function configure(DeployStrategyInterface $strategy)
    {
        if (!$this->hasVHostConfig()) {
            $this->createVirtualHost($this->getApplication()->getVirtualHost());
        }

        // TODO: Create application config
    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::maintenance()
     */
    public function maintenance()
    {
        // TODO Auto-generated method stub

    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::remove()
     */
    public function remove()
    {
        // TODO Auto-generated method stub

    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\WebConfigInterface::setApplication()
     */
    public function setApplication(entities\ApplicationInstance $application)
    {
        $this->application = $application;
        return $this;
    }

    /**
     * @return entities\ApplicationInstance
     */
    protected function getApplication()
    {
        if (!$this->application) {
            throw new LogicException('Missing application instance for web config.');
        }

        return $this->application;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\VHostCapableInterface::createVirtualHost()
     */
    public function createVirtualHost(entities\VirtualHost $vhost)
    {
        $path = new SplFileInfo($this->getPath('serverconfig'));

        if ($path->isFile()) {
            return $this;
        }

        $config = $this->createVhostConfig($vhost);
        if (!file_put_contents($path->getPathname(), $config)) {
            throw new RuntimeException(sprintf('Failed to write vhost config to %s', $path));
        }

        return $this;
    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\VHostCapableInterface::removeVirtualHost()
     */
    public function removeVirtualHost(entities\VirtualHost $vhost)
    {
        $path = new SplFileInfo($this->getPath('serverconfig'));

        if ($path->isFile()) {
            return $this;
        }

        if (!unlink($path->getPathname())) {
            throw new RuntimeException(sprintf('Failed to write vhost config to %s', $path));
        }

        return $this;
    }
}
