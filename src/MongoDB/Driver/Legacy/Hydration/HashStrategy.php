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

namespace Rampage\Nexus\MongoDB\Driver\Legacy\Hydration;

use Rampage\Nexus\Entities\Api\ArrayExportableInterface;
use Zend\Stdlib\ArraySerializableInterface;
use Zend\Hydrator\Strategy\StrategyInterface;
use ArrayObject;


/**
 * Hast hydration strategy
 */
class HashStrategy implements StrategyInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\Hydrator\Strategy\StrategyInterface::extract()
     */
    public function extract($value)
    {
        if ($value instanceof ArrayExportableInterface) {
            $value = $value->toArray();
        } else if ($value instanceof ArraySerializableInterface) {
            $value = $value->getArrayCopy();
        }

        if (!is_array($value) && !($value instanceof ArrayObject)) {
            return null;
        }

        if (empty($value)) {
            return new \stdClass();
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Hydrator\Strategy\StrategyInterface::hydrate()
     */
    public function hydrate($value)
    {
        if (($value !== null) && !is_array($value)) {
            return [];
        }

        return $value;
    }
}
