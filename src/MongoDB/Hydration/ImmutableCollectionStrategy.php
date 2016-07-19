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

namespace Rampage\Nexus\MongoDB\Hydration;

use Rampage\Nexus\MongoDB\ImmutablePersistedCollection;
use Rampage\Nexus\Exception\UnexpectedValueException;
use Zend\Hydrator\Strategy\StrategyInterface;

/**
 * Implements a strategy for immutable collections
 */
class ImmutableCollectionStrategy implements StrategyInterface
{
    /**
     * @var callable
     */
    private $cursorProvider;

    /**
     * @param callable $cursorProvider
     */
    public function __construct(callable $cursorProvider)
    {
        $this->cursorProvider = $cursorProvider;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Hydrator\Strategy\StrategyInterface::extract()
     */
    public function extract($value)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Hydrator\Strategy\StrategyInterface::hydrate()
     */
    public function hydrate($value, $data = null)
    {
        $collection = new ImmutablePersistedCollection(function() use ($value, $data) {
            return call_user_func($this->cursorProvider, $value, $data);
        });

        if ($collection && !($collection instanceof ImmutablePersistedCollection)) {
            throw new UnexpectedValueException('Expected an immutable collection instance, got ' . (is_object($value)? get_class($value) : gettype($value)));
        }

        return $collection;
    }
}