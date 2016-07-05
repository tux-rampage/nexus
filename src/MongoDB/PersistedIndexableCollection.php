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

use Rampage\Nexus\Entities\IndexableCollectionInterface;
use Rampage\Nexus\Entities\ArrayCollection;

/**
 * Provides a persisted indexable collection
 */
class PersistedIndexableCollection extends PersistedCollection implements IndexableCollectionInterface
{
    /**
     * @var ArrayCollection
     */
    protected $allItems = null;

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistedCollection::ensureLoadAll()
     */
    protected function ensureLoadAll()
    {
        if ($this->allItems) {
            return;
        }

        $this->ensureCursor();
        $this->allItems = new ArrayCollection();

        foreach ($this->cursor as $key => $item) {
            $this->allItems[$key] = $item;
        }
    }



    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        // TODO Auto-generated method stub

    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        // TODO Auto-generated method stub

    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        // TODO Auto-generated method stub

    }

    /**
     * {@inheritDoc}
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        // TODO Auto-generated method stub

    }


}
