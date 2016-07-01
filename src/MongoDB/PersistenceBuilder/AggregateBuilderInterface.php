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

namespace Rampage\Nexus\MongoDB\PersistenceBuilder;

use Rampage\Nexus\MongoDB\EntityState;

/**
 * Aggregate builder
 */
interface AggregateBuilderInterface
{
    /**
     * Build update instructions for aggregate
     *
     * This will build the update document
     *
     * @param   array           $document   The update document
     * @param   string          $property   The property path
     * @param   object          $object     The object to persist
     * @return  callable|null   Additional operations that need to be performed after persisting the aggregate
     */
    public function buildUpdateDocument(array &$document, $property, $object, EntityState $state);

    /**
     * Populate removal
     *
     * @param array $document
     * @param string $property
     * @return callable|null
     */
    public function buildUndefinedInDocument(array &$document, $property, EntityState $state);

    /**
     * Build the insert for this document
     *
     * @param   array           $document   The document to build into
     * @param   string          $property   The property path
     * @param   object          $object     The object to persist
     * @return  callable|null   Additional operations that need to be performed after persisting the aggregate
     */
    public function buildInsertDocument(array &$document, $property, $object);
}