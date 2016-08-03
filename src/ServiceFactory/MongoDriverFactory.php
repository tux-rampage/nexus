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

use Rampage\Nexus\MongoDB\Driver\Legacy\Driver as LegacyDriver;
use Rampage\Nexus\Exception\RuntimeException;
use Rampage\Nexus\Config\ArrayConfig;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class MongoDriverFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = new ArrayConfig($container->has('config')? $container->get('config') : []);
        $server = $config->get('mongodb.server', 'localhost');
        $database = $config->get('mongodb.database', 'deployment');
        $driverOptions = $config->get('mongodb.driver.options');

        if (extension_loaded('mongo')) {
            return new LegacyDriver($server, $database, $driverOptions);
        }

        if (!extension_loaded('mongodb')) {
            throw new RuntimeException('A mongo driver extension is required');
        }

        throw new RuntimeException('mongodb driver is not implemented, yet');
    }


}