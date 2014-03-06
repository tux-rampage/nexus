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
use LogicException;

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
     * @var array
     */
    protected $options = array();

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
        $params = array(
            '%vhost%' => '__default__',
            '%port%' => '80',
            '%appname%' => $this->getApplication()->getName()
        );

        if ($name != 'confdir') {
            $params['%confdir%'] = $this->getPath('confdir');
        }

        return str_replace(array_keys($params), array_values($params), $format);
    }

    /**
     * @see \rampage\nexus\WebConfigInterface::getOptionsForm()
     */
    public function getOptionsForm()
    {
        // TODO Auto-generated method stub

    }

	/**
     * @see \rampage\nexus\WebConfigInterface::setOptions()
     */
    public function setOptions(array $options)
    {
        // TODO Auto-generated method stub
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
        // TODO Auto-generated method stub

    }

	/**
     * {@inheritdoc}
     * @see \rampage\nexus\VHostCapableInterface::removeVirtualHost()
     */
    public function removeVirtualHost(entities\VirtualHost $vhost)
    {
        // TODO Auto-generated method stub

    }
}
