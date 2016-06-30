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
 * Wraps the entity state
 */
class EntityState
{
    const STATE_NEW = 1;
    const STATE_PERSISTED = 2;
    const STATE_REMOVED = 3;

    /**
     * @var int
     */
    private $state;

    /**
     * @var array
     */
    private $data;

    /**
     * @var string|int
     */
    private $id;

    /**
     * @param int $state
     * @param array $data
     * @param string|int $id
     */
    public function __construct($state, array $data, $id = null)
    {
        $this->id = $id;
        $this->state = $state;
        $this->data = $data;
    }

    /**
     * @return number
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return Ambigous <string, number>
     */
    public function getId()
    {
        return $this->id;
    }
}
