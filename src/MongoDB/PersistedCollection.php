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

use Rampage\Nexus\Entities\CollectionInterface;
use Rampage\Nexus\Exception\InvalidArgumentException;

use IteratorAggregate;
use IteratorIterator;
use SplObjectStorage;


/**
 * Implements an immutable persisted collection
 */
class PersistedCollection implements IteratorAggregate, CollectionInterface
{
    /**
     * @var CursorInterface
     */
    protected $cursor = null;

    /**
     * @var SplObjectStorage
     */
    protected $allItems = null;

    /**
     * @var SplObjectStorage
     */
    protected $persistedItems;

    /**
     * @var SplObjectStorage
     */
    protected $removedItems;

    /**
     * @var SplObjectStorage
     */
    protected $addedItems;

    /**
     * @var string
     */
    protected $acceptedType;

    /**
     * @var callable
     */
    private $cursorFactory;

    /**
     * @param callable $cursorFactory
     */
    public function __construct(callable $cursorFactory, $acceptedType = null)
    {
        $this->cursorFactory = $cursorFactory;
        $this->acceptedType = $acceptedType;
        $this->addedItems = new SplObjectStorage();
        $this->removedItems = new SplObjectStorage();
        $this->persistedItems = new SplObjectStorage();
    }

    /**
     * Set the wrapped cursor instance
     *
     * @param CursorInterface $cursor
     */
    protected function setCursor(CursorInterface $cursor)
    {
        $this->cursor = $cursor;
    }

    /**
     * Create the wrapped cursor instance
     */
    protected function ensureCursor()
    {
        if ($this->cursor) {
            return;
        }
        $factory = $this->cursorFactory;
        $this->setCursor($factory());
    }

    /**
     * Load all items, if not yet loaded
     */
    protected function ensureLoadAll()
    {
        if ($this->allItems) {
            return;
        }

        $this->ensureCursor();
        $this->allItems = new SplObjectStorage();

        foreach ($this->cursor as $key => $item) {
            $this->allItems->attach($item);
            $this->persistedItems->attach($item, $key);
        }
    }

    /**
     * Protects the collections item type safety
     *
     * @param   object                      $item   The item to check
     * @throws  InvalidArgumentException            When the item type is invalid
     */
    protected function guardItemType($item)
    {
        if ($this->acceptedType === null) {
            return;
        }

        if (!$item instanceof $this->acceptedType) {
            throw new InvalidArgumentException(sprintf(
                'Collection item must be an instanceof %s, %s given',
                $this->acceptedType,
                is_object($item)? get_class($item) : gettype($item)
            ));
        }
    }

    /**
     * Add a new object to the collection
     *
     * @api
     * @param object $item
     * @return \Rampage\Nexus\MongoDB\PersistedCollection
     */
    public function add($item)
    {
        $this->guardItemType($item);
        $this->ensureLoadAll();

        if ($this->allItems->contains($item)) {
            return $this;
        }

        if ($this->removedItems->contains($item)) {
            $this->persistedItems->attach($item, $this->removedItems[$item]);
            $this->removedItems->detach($item);
        } else {
            $this->addedItems->attach($item);
        }

        $this->allItems->attach($item);
        return $this;
    }

    /**
     * Removes a specific item from the collection
     *
     * @api
     * @param object $item
     * @return self
     */
    public function remove($item)
    {
        $this->guardItemType($item);
        $this->ensureLoadAll();

        if (!$this->allItems->contains($item)) {
            return $this;
        }

        if ($this->addedItems->contains($item)) {
            $this->addedItems->detach($item);
        } else if ($this->persistedItems->contains($item)) {
            $this->removedItems->attach($item, $this->persistedItems[$item]);
            $this->persistedItems->detach($item);
        }

        $this->allItems->detach($item);
        return $this;
    }

    /**
     * Flushes all tracked changes
     */
    public function flushPersistenceTracking()
    {
        foreach ($this->addedItems as $item) {
            $this->persistedItems->attach($item, $this->addedItems[$item]);
        }

        $this->removedItems = new SplObjectStorage();
        $this->addedItems = new SplObjectStorage();

        return $this;
    }

    /**
     * @return SplObjectStorage
     */
    public function getItemsToRemove()
    {
        return $this->removedItems;
    }

    /**
     * @return SplObjectStorage
     */
    public function getItemsToAdd()
    {
        return $this->addedItems;
    }

    /**
     * Get the already persisted items
     *
     * @return SplObjectStorage
     */
    public function getPersistedItems()
    {
        return $this->persistedItems;
    }

    /**
     * {@inheritDoc}
     * @see Iterator::current()
     */
    public function getIterator()
    {
        if ($this->allItems) {
            // Hide the inner implementation
            return new IteratorIterator($this->allItems);
        }

        $this->ensureCursor();
        return $this->cursor;
    }

    /**
     * {@inheritDoc}
     * @see Countable::count()
     */
    public function count()
    {
        if ($this->allItems) {
            return $this->allItems->count();
        }

        $this->ensureCursor();
        return $this->cursor->count();
    }
}
