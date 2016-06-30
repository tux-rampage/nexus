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

use Countable;
use Iterator;
use Traversable;
use IteratorIterator;
use Rampage\Nexus\Exception\InvalidArgumentException;

/**
 * Wrapped cursor implementation
 */
class Cursor implements Iterator, Countable
{
    /**
     * @var Iterator
     */
    private $wrapped;

    /**
     * @var int
     */
    private $count;

    /**
     * @var callable
     */
    private $hydration;

    /**
     * @var object
     */
    private $current = null;

    /**
     * Constructor
     *
     * @param   Traversable $wrapped
     * @param   callable    $hydration
     * @throws  InvalidArgumentException    When the wrapped cursor is not countable
     */
    public function __construct(Traversable $wrapped, callable $hydration)
    {
        if (!$wrapped instanceof Countable) {
            throw new InvalidArgumentException('The wrapped cursor must implement Countable');
        }

        $this->wrapped = ($wrapped instanceof Iterator)? $wrapped : new IteratorIterator($wrapped);
        $this->count = $wrapped->count();
        $this->hydration = $hydration;
    }

    /**
     * {@inheritDoc}
     * @see Countable::count()
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * {@inheritDoc}
     * @see Iterator::current()
     */
    public function current()
    {
        if ($this->valid() && ($this->current === null)) {
            $hydrate = $this->hydration;
            $data = parent::current();
            $this->current = $hydrate($data);
        }

        return $this->current;
    }

    /**
     * {@inheritDoc}
     * @see Iterator::key()
     */
    public function key()
    {
        return $this->wrapped->key();
    }

    /**
     * {@inheritDoc}
     * @see Iterator::next()
     */
    public function next()
    {
        $this->current = null;
        $this->wrapped->next();
    }

    /**
     * {@inheritDoc}
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        $this->current = null;
        $this->wrapped->rewind();
    }

    /**
     * {@inheritDoc}
     * @see Iterator::valid()
     */
    public function valid()
    {
        return $this->wrapped->valid();
    }
}
