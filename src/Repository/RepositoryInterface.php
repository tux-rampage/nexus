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

namespace Rampage\Nexus\Repository;

use Rampage\Nexus\Exception\InvalidArgumentException;

/**
 * Repository definition
 */
interface RepositoryInterface
{
    /**
     * Find a single entity by id
     *
     * Consider all objects returned by this method as state tracked.
     *
     * @see     persist()       Persisting an object
     * @param   string      $id The object's identifier
     * @return  object|null     The resultin object or null
     */
    public function findOne($id);

    /**
     * A collection of all entities
     *
     * @return
     */
    public function findAll();

    /**
     * Detatches the given object from repository tracking
     *
     * @param object $object
     */
    public function detatch($object);

    /**
     * Check if this repository accepts the given entity
     *
     * @param   object  $object
     * @return  bool
     */
    public function accepts($object);

    /**
     * Persist an entity
     *
     * Ensures the persistence of the entity
     *
     * @param   object                      $object The entity to persist
     * @throws  RuntimeException
     * @throws  InvalidArgumentException            When the object is not accepted
     */
    public function persist($object);

    /**
     * Remove the object's persisted data
     *
     * @param   object                      $object The entity to persist
     * @throws  RuntimeException
     * @throws  InvalidArgumentException            When the object is not accepted
     */
    public function remove($object);

    /**
     * Detatches all objects from state tracking
     *
     * This might be useful to force full updates/inserts for all entities on the next `persist()` call.
     */
    public function clear();
}
