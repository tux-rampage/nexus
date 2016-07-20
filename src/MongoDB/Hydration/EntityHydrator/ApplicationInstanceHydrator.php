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

use Rampage\Nexus\Repository\PackageRepositoryInterface;
use Rampage\Nexus\Repository\ApplicationRepositoryInterface;
use Rampage\Nexus\Repository\DeployTargetRepositoryInterface;
use Rampage\Nexus\MongoDB\Hydration\ReferenceStrategy;

class ApplicationInstanceHydrator extends ReflectionHydrator
{
    /**
     * @var PackageRepositoryInterface
     */
    private $packageRepository;

    /**
     * @var ApplicationRepositoryInterface
     */
    private $applicationRepository;

    /**
     * @var DeployTargetRepositoryInterface
     */
    private $targetRepository;

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Hydration\ReflectionHydrator::__construct()
     */
    public function __construct(DriverInterface $driver, ApplicationRepositoryInterface $applicationRepository)
    {
        parent::__construct([
            'label',
            'state',
            'application',
            'path',
            'flavor',
            'userParameters',
            'previousUserParameters',
            'package',
            'previousPackage',
            'vhost',
        ], null);

        $packageStrategy = new PackageStrategy();

        $this->addStrategy('*', $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_STRING));
        $this->addStrategy('userParameters', $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_HASH));
        $this->addStrategy('previousUserParameters', $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_HASH));
        $this->addStrategy('application', new ReferenceStrategy($applicationRepository));
        $this->addStrategy('vhost', new VHostStrategy());
        $this->addStrategy('package', $packageStrategy);
        $this->addStrategy('previousPackage', $packageStrategy);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Hydration\ReflectionHydrator::hydrate()
     */
    public function hydrate(array $data, $object)
    {
        // Make sure package and vhost are hydrated at last
        $ensureLast = ['package', 'previousPackage', 'vhost'];

        foreach ($ensureLast as $key) {
            if (!isset($data[$key])) {
                continue;
            }

            $value = $data[$key];
            unset($data[$key]);
            $data[$key] = $value;
        }

        return parent::hydrate($data, $object);
    }
}
