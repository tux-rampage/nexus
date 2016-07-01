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


/**
 * Implements a simple invokable chain
 */
class InvokableChain
{
    /**
     * @var callable[]
     */
    private $callbacks = [];

    /**
     * @param callable[] $callbacks
     */
    public function __construct($callbacks = null)
    {
        if (is_array($callbacks) || ($callbacks instanceof \Traversable)) {
            $this->addAll($callbacks);
        }
    }

    /**
     * @param callable $callback
     * @return self
     */
    public function add(callable $callback)
    {
        $this->callbacks[] = $callback;
        return $this;
    }

    /**
     * @param callable $callback
     * @return self
     */
    public function prepend(callable $callback)
    {
        array_unshift($this->callbacks, $callback);
        return $this;
    }

    /**
     * @param callable[] $callbacks
     * @return self
     */
    public function addAll($callbacks)
    {
        foreach ($callbacks as $callback) {
            $this->add($callback);
        }

        return $this;
    }

    /**
     * Invoke all callbacks
     */
    public function __invoke()
    {
        foreach ($this->callbacks as $callback) {
            $callback();
        }
    }
}
