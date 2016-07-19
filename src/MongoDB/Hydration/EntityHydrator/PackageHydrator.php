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

namespace Rampage\Nexus\MongoDB\Hydration\EntityHydrator;

use Rampage\Nexus\MongoDB\Hydration\ReflectionHydrator;
use Rampage\Nexus\MongoDB\Driver\DriverInterface;
use Rampage\Nexus\MongoDB\Hydration\EmbeddedStrategy;
use Rampage\Nexus\MongoDB\Hydration\CollectionStrategy;

use Rampage\Nexus\Repository\PackageRepositoryInterface;
use Rampage\Nexus\Entities\PackageParameter;
use Zend\Hydrator\HydratorInterface;

/**
 * Hydrator for application packages
 */
class PackageHydrator extends ReflectionHydrator
{
    /**
     * @var HydratorInterface
     */
    protected $parameterHydrator;

    /**
     * @param PackageRepositoryInterface $repo
     * @param DriverInterface $driver
     */
    public function __construct(DriverInterface $driver)
    {
        $properties = [
            'archive',
            'name',
            'version',
            'type',
            'documentRoot',
            'parameters',
            'extra'
        ];

        parent::__construct($properties);
        $this->parameterHydrator = $this->createParameterHydrator();

        $this->addStrategy('id', $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_STRING));
        $this->addStrategy('extra', $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_DYNAMIC));
        $this->addStrategy('*', $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_STRING));
        $this->addStrategy('parameters', new CollectionStrategy(new EmbeddedStrategy(new PackageParameter(), $this->parameterHydrator), true, PackageParameter::class));
    }

    /**
     * Creates the package hydrator
     *
     * @param DriverInterface $driver
     * @return ReflectionHydrator
     */
    private function createParameterHydrator(DriverInterface $driver)
    {
        $stringStrategy = $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_STRING);
        $hydrator = new ReflectionHydrator();

        $hydrator->addStrategy('name', $stringStrategy);
        $hydrator->addStrategy('label', $stringStrategy);
        $hydrator->addStrategy('type', $stringStrategy);

        return $hydrator;
    }

    /**
     * @return \Zend\Hydrator\HydratorInterface
     */
    public function getParameterHydrator()
    {
        return $this->parameterHydrator;
    }
}
