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

use Interop\Container\ContainerInterface;
use Rampage\Nexus\Config\PropertyConfigInterface;
use Rampage\Nexus\Config\ArrayConfig;

/**
 * Provides methods to retrieve the runtime config
 */
trait RuntimeConfigTrait
{
    /**
     * Returns the Runtime config from the container
     *
     * It also ensures that always an Instance of `PropertyConfigInterface` is returned
     * even if the container does not provide it
     *
     * @param ContainerInterface $container
     * @return PropertyConfigInterface
     */
    protected function getRuntimeConfig(ContainerInterface $container)
    {
        if ($container->has('RuntimeConfig')) {
            return $container->get('RuntimeConfig');
        }

        return new ArrayConfig([]);
    }
}
