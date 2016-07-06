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

namespace Rampage\Nexus\MongoDB\Driver;

use Zend\Hydrator\Strategy\StrategyInterface as HydrationStrategyInterface;

/**
 * Interface for mongo drivers
 */
interface DriverInterface
{
    const SORT_ASC = 1;
    const SORT_DESC = 2;

    /**
     * Returns the collection with the given name
     *
     * @param string $name
     * @return CollectionInterface
     */
    public function getCollection($name);

    /**
     * Returns the type hydration strategy
     *
     * @param   int $type
     * @return  HydrationStrategyInterface
     */
    public function getTypeHydrationStrategy($type);
}