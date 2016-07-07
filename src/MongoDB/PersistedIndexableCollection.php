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

use Rampage\Nexus\Entities\IndexableCollectionInterface;
use Rampage\Nexus\Entities\ArrayCollection;
use SplObjectStorage;
use Rampage\Nexus\Exception\LogicException;


/**
 * Provides a persisted indexable collection
 */
class PersistedIndexableCollection extends PersistedCollection implements IndexableCollectionInterface
{
    /**
     * @var ArrayCollection
     */
    protected $items = null;

    /**
     * @var array
     */
    protected $changes = [
        'modified' => [],
        'added' => [],
        'removed' => []
    ];

    /**
     * @var bool
     */
    protected $allowAppend = false;

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistedCollection::ensureLoadAll()
     */
    protected function ensureLoadAll()
    {
        if ($this->items) {
            return;
        }

        $this->ensureCursor();
        $this->items = new ArrayCollection();

        foreach ($this->cursor as $key => $item) {
            $this->items[$key] = $item;
        }
    }

    /**
     * Returns the modified offsets
     *
     * @return string[]|int[]
     */
    public function getModifiedOffsets()
    {
        return $this->changes['modified'];
    }

    /**
     * Returns newly added offsets
     *
     * @return string[]|int[]
     */
    public function getAddedOffsets()
    {
        return $this->changes['added'];
    }

    /**
     * Returns removed offsets
     *
     * @return string[]|int[]
     */
    public function getRemovedOffsets()
    {
        return $this->changes['removed'];
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistedCollection::flushPersistenceTracking()
     */
    public function flushPersistenceTracking()
    {
        $this->addedItems = new SplObjectStorage();

        foreach (array_keys($this->changes) as $key) {
            $this->changes[$key] = [];
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistedCollection::add()
     */
    public function add($item)
    {
        if (!$this->allowAppend) {
            throw new LogicException('Appending items without index to this indexed collection is not allowed');
        }

        $this->guardItemType($item);
        $this->addedItems->attach($item);
        $this->items[] = $item;

        return $this;
    }

    /**
     * This is an alias of offsetUnset
     *
     * @return self
     */
    public function remove($offset)
    {
        $this->offsetUnset($offset);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        $this->ensureLoadAll();
        return $this->items->offsetExists($offset);
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        $this->ensureLoadAll();
        return $this->items->offsetGet($offset);
    }

    /**
     * Track offset set
     *
     * @param string|int $offset
     */
    protected function trackSet($offset)
    {
        if (isset($this->changes['removed'][$offset])) {
            unset($this->changes['removed'][$offset]);
            $this->changes['modified'][$offset] = $offset;
            return;
        }

        if (isset($this->changes['added'][$offset])) {
            return;
        }

        if ($this->offsetExists($offset)) {
            $this->changes['modified'] = $offset;
        } else {
            $this->changes['added'] = $offset;
        }
    }

    /**
     * Track offset removal
     *
     * @param string $offset
     */
    protected function trackRemove($offset)
    {
        $item = $this->offsetGet($offset);

        if ($this->addedItems->contains($item)) {
            $this->addedItems->detach($item);
        }

        if ($this->changes['added'][$offset]) {
            unset($this->changes['added'][$offset]);
            return;
        }

        unset($this->changes['modified'][$offset]);
        $this->changes['removed'][$offset] = $offset;
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        $this->ensureLoadAll();

        if ($offset === null) {
            $this->add($value);
            return;
        }

        $this->guardItemType($value);
        $this->trackSet($offset);
        $this->items->offsetSet($offset, $value);
    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        $this->ensureLoadAll();

        if (!$this->offsetExists($offset)) {
            return;
        }

        $this->trackRemove($offset);
        $this->items->offsetUnset($offset);
    }
}
