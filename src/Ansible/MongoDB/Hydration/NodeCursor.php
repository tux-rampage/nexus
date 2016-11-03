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

namespace Rampage\Nexus\Ansible\MongoDB\Hydration;

use Rampage\Nexus\MongoDB\CursorInterface;
use Rampage\Nexus\Ansible\Entities\Host;
use Rampage\Nexus\Exception\LogicException;

/**
 * Maps a host cursor to a node cursor
 */
class NodeCursor implements CursorInterface
{
    /**
     * @var CursorInterface
     */
    private $cursor;

    /**
     * @param CursorInterface $cursor
     */
    public function __construct(CursorInterface $cursor)
    {
        $this->cursor = $cursor;
    }

    /**
     * {@inheritDoc}
     * @see Countable::count()
     */
    public function count($mode = null)
    {
        return $this->cursor->count($mode);
    }

    /**
     * {@inheritDoc}
     * @see Iterator::current()
     */
    public function current()
    {
        /** @var Host $host */
        $host = $this->cursor->current();

        if ((!$host instanceof Host) || !$host->getNode()) {
            throw new LogicException('Cannot map host without attached node');
        }

        return $host->getNode();
    }

    /**
     * {@inheritDoc}
     * @see Iterator::key()
     */
    public function key()
    {
        return $this->cursor->key();
    }

    /**
     * {@inheritDoc}
     * @see Iterator::next()
     */
    public function next()
    {
        $this->cursor->next();
    }

    /**
     * {@inheritDoc}
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        $this->cursor->rewind();
    }

    /**
     * {@inheritDoc}
     * @see Iterator::valid()
     */
    public function valid()
    {
        return $this->cursor->valid();
    }
}
