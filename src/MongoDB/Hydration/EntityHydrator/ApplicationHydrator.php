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

use Rampage\Nexus\Repository\PackageRepositoryInterface;

use Rampage\Nexus\MongoDB\Hydration\ReflectionHydrator;
use Rampage\Nexus\MongoDB\Driver\DriverInterface;
use Rampage\Nexus\MongoDB\Hydration\ImmutableCollectionStrategy;
use Rampage\Nexus\MongoDB\EmptyCursor;

/**
 * Hydrator for application packages
 */
class ApplicationHydrator extends ReflectionHydrator
{
    /**
     * @var PackageRepositoryInterface
     */
    protected $packageRepository;

    /**
     * @param PackageRepositoryInterface $repo
     * @param DriverInterface $driver
     */
    public function __construct(PackageRepositoryInterface $repository, DriverInterface $driver)
    {
        $this->packageRepository = $repository;
        $properties = [
            'label',
            'icon',
            'packages'
        ];

        parent::__construct($properties);

        $this->addStrategy('id', $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_STRING));
        $this->addStrategy('packages', new ImmutableCollectionStrategy(function($value, $data = null) {
            if (!isset($data['_id'])) {
                return new EmptyCursor();
            }

            return $this->packageRepository->findByPackageName($data['_id']);
        }));
    }
}
