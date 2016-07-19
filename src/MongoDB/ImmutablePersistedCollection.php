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
use IteratorAggregate;


/**
 * Implements an immutable persisted collection
 */
class ImmutablePersistedCollection implements IteratorAggregate, CollectionInterface
{
    /**
     * @var CursorInterface
     */
    private $cursor = null;

    /**
     * @var callable
     */
    private $cursorFactory;

    /**
     * @param callable $cursorFactory
     */
    public function __construct(callable $cursorFactory = null)
    {
        $this->cursorFactory = $cursorFactory;
    }

    /**
     * @return self
     */
    public function reload()
    {
        $this->cursor = null;
        return $this;
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
    protected function createCursor()
    {
        if ($this->cursorFactory) {
            $factory = $this->cursorFactory;
            $this->setCursor($factory());
        } else {
            $this->setCursor(new EmptyCursor());
        }

    }

    /**
     * {@inheritDoc}
     * @see Countable::count()
     */
    public function count($mode = null)
    {
        if (!$this->cursor) {
            $this->createCursor();
        }

        return $this->cursor->count($mode);
    }

    /**
     * {@inheritDoc}
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        if (!$this->cursor) {
            $this->createCursor();
        }

        return $this->cursor;
    }
}
