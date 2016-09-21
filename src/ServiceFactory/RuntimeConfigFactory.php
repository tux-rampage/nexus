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

namespace Rampage\Nexus\ServiceFactory;

use Rampage\Nexus\Config\ArrayConfig;

use Interop\Container\ContainerInterface;

use Zend\Config\Factory as ZendConfigFactory;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Expressive\ConfigManager\ConfigManager;
use Zend\Expressive\ConfigManager\ZendConfigProvider;

use Phar;

/**
 * Factory for loading the runtime config
 */
class RuntimeConfigFactory implements FactoryInterface
{
    const KEY = 'runtime_config';

    /**
     * @param mixed $var
     * @return boolean
     */
    private function isArrayAccessible($var)
    {
        return (is_array($var) || ($var instanceof \ArrayAccess));
    }

    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $system = $container->has('config')? $container->get('config') : [];
        $data = [];

        if (isset($system[self::KEY]) && $this->isArrayAccessible($system[self::KEY])) {
            $data = $system[self::KEY];
        }

        ZendConfigFactory::registerReader('conf', 'ini');

        $localPrefix = Phar::running()? '' : __DIR__ . '/../..';
        $configManager = new ConfigManager([
            function() use ($data) { return $data; },
            new ZendConfigProvider($localPrefix . '/etc/php-deployment/*.conf'),
        ]);

        return new ArrayConfig($configManager->getMergedConfig());
    }
}
