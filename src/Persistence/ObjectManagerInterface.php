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

namespace Rampage\Nexus\Persistence;

/**
 * Interface for the persitence object manager
 *
 * This is the api for persisting and querying objects
 */
interface ObjectManagerInterface
{
    /**
     * Persist the given object
     *
     * @param object $object
     */
    public function persist($object);

    /**
     * Remove the persisted state for the given object
     *
     * @param object $object
     */
    public function remove($object);

    /**
     * Remove the entity from tracking
     *
     * This will release the entity from change tracking. It will not be flushed, even if its state
     * changes
     *
     * @param object $object
     */
    public function untrack($object);

    /**
     * Find an object by its id
     *
     * @param   string      $class  The object type to load
     * @param   int|string  $id     The identifier
     * @return  object|null         The resulting object or null if the id does not exist
     */
    public function find($class, $id);

    /**
     * Flush all changes to the underlying persistence layer
     *
     * @param   object  $object Only flush changes for the given object
     */
    public function flush($object = null);

    /**
     * Clear the current unit of work state
     *
     * This will reset the object manager to a clean state which means:
     *
     *  * All tracked entities will be released and no longer be tracked.
     *  * All uncommitted/unflushed changes will be lost
     */
    public function clear();
}
