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

namespace Rampage\Nexus\MongoDB\Driver;

interface CollectionInterface
{
    /**
     * Find records
     *
     * @param array $query
     * @param array $fields
     * @param int $limit
     * @param int $skip
     * @param array $order
     * @return \Traversable|\Countable
     */
    public function find(array $query, array $fields = null, $limit = null, $skip = null, array $order = null);

    /**
     * Fint a single document
     *
     * @param array $query
     * @param array $fields
     * @param array $order
     * @return array
     */
    public function findOne(array $query, array $fields = null, array $order = null);

    /**
     * Insert a document
     *
     * @param array $document
     * @param string $upsert
     */
    public function insert(array $document, $upsert = false);

    /**
     * Update documents
     *
     * @param array $query
     * @param array $document
     * @param string $upsert
     * @param string $multiple
     */
    public function update(array $query, array $document, $upsert = false, $multiple = false);

    /**
     * Remove documents
     *
     * @param array $query
     * @param string $multiple
     */
    public function remove(array $query, $multiple = false);
}
