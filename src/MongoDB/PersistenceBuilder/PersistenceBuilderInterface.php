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

namespace Rampage\Nexus\MongoDB\PersistenceBuilder;

use Rampage\Nexus\MongoDB\EntityState;

/**
 * Interface for PErsistence builders
 */
interface PersistenceBuilderInterface
{
    /**
     * Build the persist actions for the given object
     *
     * @param   object      $object The object
     * @param   EntityState $state  The current entity state. This will be replaced with an updated state after the call
     * @return  callable
     */
    public function buildPersist($object, EntityState &$state);

    /**
     * Build the remove actions for the given object
     *
     * @param   string      $object
     * @return  callable
     */
    public function buildRemove($object);
}