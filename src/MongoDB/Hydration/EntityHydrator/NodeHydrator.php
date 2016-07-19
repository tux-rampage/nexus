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
use Rampage\Nexus\MongoDB\Hydration\ReferenceStrategy;
use Rampage\Nexus\Repository\DeployTargetRepositoryInterface;


/**
 * Deploytarget repository
 */
class NodeHydrator extends ReflectionHydrator
{
    /**
     * @var DeployTargetRepositoryInterface
     */
    private $targetRepository;

    /**
     * @param DriverInterface $driver
     * @param DeployTargetRepositoryInterface $targetRepository
     */
    public function __construct(DriverInterface $driver, DeployTargetRepositoryInterface $targetRepository)
    {
        parent::__construct([
            'name',
            'deployTarget',
            'url',
            'state',
            'applicationStates',
            'publicKey',
            'serverInfo'
        ]);

        $targetStrategy = new ReferenceStrategy($targetRepository);

        $this->addStrategy('id', $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_ID));
        $this->addStrategy('applicationStates', $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_HASH));
        $this->addStrategy('serverInfo', $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_HASH));
        $this->addStrategy('deployTarget', $targetStrategy);
        $this->addStrategy('*', $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_STRING));
    }
}
