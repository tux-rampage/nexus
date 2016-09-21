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

use Rampage\Nexus\Entities\ArrayCollection;
use Zend\Hydrator\Strategy\StrategyInterface;
use Traversable;

/**
 * Hydrates a collection
 */
class CollectionStrategy implements StrategyInterface
{
    /**
     * @var bool
     */
    protected $indexed;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var StrategyInterface
     */
    protected $itemStrategy;

    /**
     * @param StrategyInterface $strategy
     * @param bool $indexed
     * @param string $type
     */
    public function __construct(StrategyInterface $strategy, $indexed = false, $type = null)
    {
        $this->itemStrategy = $strategy;
        $this->indexed = (bool)$indexed;
        $this->type = $type;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Hydrator\Strategy\StrategyInterface::extract()
     */
    public function extract($value)
    {
        if (!is_array($value) && !($value instanceof Traversable)) {
            return [];
        }

        $data = [];

        if ($this->indexed) {
            foreach ($value as $key => $item) {
                $data[$key] = $this->itemStrategy->extract($item);
            }
        } else {
            foreach ($value as $item) {
                $data[] = $this->itemStrategy->extract($item);
            }
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Hydrator\Strategy\StrategyInterface::hydrate()
     */
    public function hydrate($value, array &$data = null)
    {
        $collection = new ArrayCollection();

        if (!is_array($value)) {
            return $collection;
        }


        if ($this->indexed) {
            foreach ($value as $key => $item) {
                $collection[$key] = $this->itemStrategy->hydrate($item, $data);
            }
        } else {
            foreach ($value as $item) {
                $collection[] = $this->itemStrategy->hydrate($item, $data);
            }
        }

        return $collection;
    }
}
