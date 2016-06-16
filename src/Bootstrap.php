<?php
/**
 * Copyright (c) 2016 Axel Helmert
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
 * @copyright Copyright (c) 2016 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config as ServiceConfig;
use Rampage\Nexus\Config\ConfigProviderInterface;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Di\ConfigProvider;

/**
 * Default application instance
 */
class Bootstrap
{
    /**
     * @param ConfigProviderInterface $configProvider
     * @return ContainerInterface
     */
    protected function createContainer(ConfigProviderInterface $configProvider)
    {
        $config = $configProvider->getConfig();
        $container = new ServiceManager();

        (new ServiceConfig($config['dependencies']))->configureServiceManager($container);

        // Inject config
        $container->setService('config', $config);
        $container->setService(ConfigProviderInterface::class, $configProvider);

        return $container;
    }

    /**
     * @param ConfigProviderInterface $configProvider
     * @return Application
     */
    public function app(ConfigProviderInterface $configProvider)
    {
        $container = $this->createContainer($configProvider);
        return $container->get(Application::class);
    }
}
