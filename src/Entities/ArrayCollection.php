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

namespace Rampage\Nexus\Entities;

use ArrayObject;

class ArrayCollection extends ArrayObject implements IndexableCollectionInterface
{
    /**
     * Find an item by using a predicate callback
     *
     * ```php
     * $collection = new ArrayCollection([
     *      'a' => 'Foo',
     *      'b' => 'Bar'
     * ]);
     *
     * // $key will be "b"
     * $key = $collection->find(function($item) {
     *      return ($item == 'Bar');
     * });
     * ```
     *
     * @param   callable        $predicate  The predicate for matching. This callback should accept two parameters.
     *                                      The first parameter is the value to check, the second parameter the key. it
     *                                      should return true if the item matches the predicate or false if not.
     * @return  int|string|null             The index of the first match or null if $predicate matches nothing
     */
    public function find(callable $predicate)
    {
        foreach ($this as $key => $item) {
            if ($predicate($item, $key)) {
                return $key;
            }
        }

        return null;
    }
}
