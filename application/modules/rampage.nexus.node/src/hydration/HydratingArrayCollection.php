<?php
/**
 * Copyright (c) 2015 Axel Helmert
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
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\node\hydration;

use ArrayIterator;
use ArrayObject;
use Countable;
use Iterator;

use Zend\Hydrator\HydrationInterface;


class HydratingArrayCollection implements Iterator, Countable
{
    /**
     * @var Iterator
     */
    protected $innerIterator = null;

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * @var HydrationInterface
     */
    protected $hydrator;

    /**
     * @var object
     */
    protected $objectPrototype;

    /**
     * @var object
     */
    protected $current = null;

    /**
     * @param array              $data       The data array
     * @param HydrationInterface $hydrator   The hydrator
     * @param object             $prototype  The object prototype to hydrate to
     */
    public function __construct(array $data, HydrationInterface $hydrator = null, $prototype = null)
    {
        $this->hydrator = $hydrator;
        $this->objectPrototype = is_object($prototype)? $prototype : new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);

        $this->setData($data);
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->innerIterator = new \ArrayIterator($data);
        $this->count = count($data);
    }

    /**
     * Hydrate the current data
     */
    protected function hydrate()
    {
        $data = $this->innerIterator->current();

        if (is_array($data)) {
            $this->current = $this->hydrator->hydrate($data, clone $this->objectPrototype);
        }
    }

    /**
     * @see Countable::count()
     */
    public function count($mode = null)
    {
        return $this->count;
    }

    /**
     * @see Iterator::current()
     */
    public function current()
    {
        if (($this->current === null) && $this->valid()) {
            $this->hydrate();
        }

        return $this->current;
    }

    /**
     * @see Iterator::key()
     */
    public function key()
    {
        return $this->innerIterator->key();
    }

    /**
     * @see Iterator::next()
     */
    public function next()
    {
        $this->innerIterator->next();
        $this->current = null;
    }

    /**
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        $this->innerIterator->rewind();
        $this->current = null;
    }

    /**
     * @see Iterator::valid()
     */
    public function valid()
    {
        return $this->innerIterator->valid();
    }
}
