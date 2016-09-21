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

namespace Rampage\Nexus\Action;

use Rampage\Nexus\Entities\Api\ArrayExportableInterface;
use Rampage\Nexus\Exception\InvalidArgumentException;
use Rampage\Nexus\Exception\UnexpectedValueException;
use Zend\Stdlib\ArraySerializableInterface;

use ArrayObject;
use Traversable;

/**
 * Trait for exporting json collections
 */
trait JsonCollectionTrait
{
    /**
     * @param mixed $item
     * @return \Rampage\Nexus\Entities\Api\ArrayExportableInterface
     */
    protected function exportCollectionItemToArray($item)
    {
        if ($item instanceof ArrayExportableInterface) {
            $item = $item->toArray();
        } else if ($item instanceof ArraySerializableInterface) {
            $item = $item->getArrayCopy();
        }

        return $item;
    }

    /**
     * Converts a collection to an array
     *
     * @param array|Traversable|ArrayExportableInterface $collection
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @return array[]
     */
    protected function collectionToArray($collection)
    {
        if ($collection instanceof ArrayExportableInterface) {
            return $collection;
        }

        if (!is_array($collection) && !($collection instanceof Traversable)) {
            throw new InvalidArgumentException(sprintf('The provided collection must be an array or implement the Traversable interface, %s given', is_object($collection)? get_class($collection) : gettype($collection)));
        }

        $result = [
            'count' => count($collection),
            'items' => []
        ];

        foreach ($collection as $item) {
            $item = $this->exportCollectionItemToArray($item);

            if (!is_array($item) && !($item instanceof ArrayObject)) {
                throw new UnexpectedValueException(sprintf('Expected collection item to be an array or array representative. Got %s', is_object($item)? get_class($item) : gettype($item)));
            }

            $result['items'][] = $item;
        }

        return $result;
    }

}
