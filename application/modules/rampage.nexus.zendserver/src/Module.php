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

use rampage\nexus\features;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class Module implements
    features\PackageTypeProviderInterface,
    features\ServerApiProviderInterface,
    ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceManager = null;

    /**
     * @param string $name
     * @return array
     */
    protected function fetchConfig($name)
    {
        return include __DIR__ . '/../config/' . $name . '.conf.php';
    }

    /**
     * {@inheritdoc}
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::getServiceLocator()
     */
    public function getServiceLocator()
    {
        return $this->serviceManager;
    }

    /**
     * {@inheritdoc}
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::setServiceLocator()
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceManager = $serviceLocator;
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\features\PackageTypeProviderInterface::getDeploymentPackageTypes()
     */
    public function getDeploymentPackageTypes()
    {
        $config = $this->serviceManager->get('DeploymentConfig');

        return array(
            new ZendServerPackageInstaller(new Config($config->getSection('zendserver')))
        );
    }

    /**
     * {@inheritdoc}
     * @see \rampage\nexus\features\ServerApiProviderInterface::getServerApisConfig()
     */
    public function getServerApisConfig()
    {
        return $this->fetchConfig('server-apis');
    }
}
