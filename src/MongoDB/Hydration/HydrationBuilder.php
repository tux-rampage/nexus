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

namespace Rampage\Nexus\MongoDB\Hydration;

use Zend\Hydrator\HydratorInterface;

/**
 * Hydrator Builder
 */
class HydratorBuilder implements HydratorBuilderInterface
{
    /**
     * @var HydratorInterface[]
     */
    protected $hydrators = [];

    /**
     * @param string $entityClass
     */
    private function createHydrator($entityClass)
    {
        // FIXME
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Hydration\HydratorBuilderInterface::getHydrator()
     */
    public function getHydrator($entityClass)
    {
        if (!isset($this->hydrators[$entityClass])) {
            $this->hydrators[$entityClass] = $this->createHydrator($entityClass);
        }

        return $this->hydrators[$entityClass];
    }
}
