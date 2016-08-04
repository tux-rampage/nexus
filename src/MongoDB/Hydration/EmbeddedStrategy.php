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

use Rampage\Nexus\Exception\InvalidArgumentException;
use Zend\Hydrator\Strategy\StrategyInterface;
use Zend\Hydrator\HydratorInterface;


/**
 * Implements object embedding
 */
class EmbeddedStrategy implements StrategyInterface
{
    const ROOT_CONTEXT_KEY = '__hydrationRootObject';

    use RootContextTrait;

    /**
     * @var object
     */
    private $prototype;

    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @param object $prototype
     * @param HydratorInterface $hydrator
     * @throws InvalidArgumentException
     */
    public function __construct($prototype, HydratorInterface $hydrator)
    {
        if (!is_object($prototype)) {
            throw new InvalidArgumentException('The prototype must be an object');
        }

        $this->prototype = $prototype;
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Hydrator\Strategy\StrategyInterface::extract()
     */
    public function extract($value)
    {
        if ($value === null) {
            return null;
        }

        return $this->hydrator->extract($value);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Hydrator\Strategy\StrategyInterface::hydrate()
     */
    public function hydrate($value, array $data = [])
    {
        if (!is_array($value)) {
            return null;
        }

        $value[self::ROOT_CONTEXT_KEY] = $this->getRootContext($data);
        $object = clone $this->prototype;
        $this->hydrator->hydrate($value, $object);

        return $object;
    }
}
