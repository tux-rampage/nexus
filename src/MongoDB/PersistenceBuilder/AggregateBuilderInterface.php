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
     * @param   object          $object     The aggregated object to persist
     * @param   array           $parent     The parent document to build into. The property in this document will contain the previous state data (also if it is null).
     * @param   string          $property   The property
     * @param   string          $prefix     The path perfix
     * @param   array           $root       The root document
     * @param   EntityState     $state      The entity state
     * @return  callable|null   Additional operations that need to be performed after persisting the aggregate
     */
    public function buildUpdateDocument($object, array &$parent, $property, $prefix, array &$root, EntityState $state);

    /**
     * Populate removal
     *
     * @param   string          $property   The property
     * @param   string          $prefix     The path perfix
     * @param   object          $stateValue The previously known state value
     * @param   EntityState     $state      The entity state
     * @return  callable|null
     */
    public function buildUndefinedInDocument($property, $prefix, $stateValue, EntityState $state);

    /**
     * Build the insert for this document
     *
     * @param   object          $object     The aggregated object to persist
     * @param   array           $parent     The parent document to build into
     * @param   string          $property   The property
     * @param   string          $prefix     The path perfix
     * @param   array           $root       The root document
     *
     * @return  callable|null   Additional operations that need to be performed after persisting the aggregate
     */
    public function buildInsertDocument($object, array &$parent, $property, $prefix, array &$root);
}
