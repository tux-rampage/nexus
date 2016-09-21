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

namespace Rampage\Nexus\MongoDB;

use SplObjectStorage;
use Rampage\Nexus\Exception\RuntimeException;
use Rampage\Nexus\Exception\LogicException;

class EntityStateContainer
{
    /**
     * Contains all object states
     *
     * This will utilize the `SplObjectStore` to attach state information
     * to the object.
     *
     * @var SplObjectStorage
     */
    private $states;

    /**
     * Tracks the objects by their identifier
     *
     * @var object[]
     */
    private $byIdentifier = [];

    /**
     * Construct
     */
    public function __construct()
    {
        $this->states = new SplObjectStorage();
    }

    /**
     * @param object $object
     * @param EntityState $state
     */
    public function attach($object, EntityState $state)
    {
        if ($this->states->contains($object)) {
            return;
        }

        $id = $state->getId();

        if ($id) {
            $this->attachById($id, $object);
        }

        $this->states->attach($object, $state);
    }

    /**
     * Detatches the given object
     *
     * @param object $object
     */
    public function detatch($object)
    {
        if (!$this->isAttached($object)) {
            return;
        }

        $id = $this->states->offsetGet($object)->getId();

        if (isset($this->byIdentifier[$id])) {
            unset($this->byIdentifier[$id]);
        }

        $this->states->detach($object);
    }

    /**
     * @param int|string $id
     * @param object $object
     * @throws RuntimeException
     */
    private function attachById($id, $object)
    {
        if (isset($this->byIdentifier[$id]) && ($this->byIdentifier[$id] !== $object)) {
            throw new RuntimeException('Duplicate identifier: ' . $id);
        }

        $this->byIdentifier[$id] = $object;
    }

    /**
     * Check if the object is attached
     *
     * @param string $object
     * @return boolean
     */
    public function isAttached($object)
    {
        return $this->states->contains($object);
    }

    /**
     * Returns the state information for an attached object
     *
     * @param   object          $object The object to fetch for
     * @throws  LogicException          When the object is not attached
     * @return  EntityState             The entity state
     */
    public function getState($object)
    {
        if (!$this->isAttached($object)) {
            throw new LogicException('Object is not attached');
        }

        return $this->states->offsetGet($object);
    }

    /**
     * Update the entities state information
     *
     * @param   object          $object The object to update
     * @param   EntityState     $state  The new state information
     * @throws  LogicException          When the object is not attached
     */
    public function updateState($object, EntityState $state)
    {
        if (!$this->isAttached($object)) {
            throw new LogicException('This object is not attached');
        }

        $lastId = $this->states->offsetGet($object)->getId();
        $id = $state->getId();

        if ($id) {
            $this->attachById($id, $object);
        }

        if ($lastId && ($lastId != $id)) {
            unset($this->byIdentifier[$lastId]);
        }

        $this->states->offsetSet($object, $state);
    }

    /**
     * Check if there is an instance for the given identifier
     *
     * @param string|int $id
     * @return bool
     */
    public function hasInstanceByIdentifier($id)
    {
        return isset($this->byIdentifier[$id]);
    }

    /**
     * Returns the instance for an identifier
     *
     * @param   int|string      $id     The identifier
     * @throws  LogicException          If there is no object with this id attached
     * @return  object
     */
    public function getInstanceByIdentifier($id)
    {
        if (!$this->hasInstanceByIdentifier($id)) {
            throw new LogicException(sprintf('There is no instance for identifier %s', $id));
        }

        return $this->byIdentifier[$id];
    }

    /**
     * Returns the tracked identity or uses the create callback to create one and attach it
     *
     * @param   string      $class
     * @param   EntityState $state
     * @param   callable    $create
     */
    public function getOrCreate(EntityState $state, callable $create)
    {
        $id = $state->getId();

        if ($id && $this->hasInstanceByIdentifier($id)) {
            return $this->getInstanceByIdentifier($id);
        }

        $object = $create();
        $this->attach($object, $state);

        return $object;
    }

    /**
     * Clear the current state
     */
    public function clear()
    {
        $this->byIdentifier = [];
        $this->states = new SplObjectStorage();
    }
}
