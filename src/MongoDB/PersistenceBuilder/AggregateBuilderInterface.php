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

use Rampage\Nexus\MongoDB\InvokableChain;

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
     * @param   string          $property   The property
     * @param   string          $prefix     The path perfix
     * @param   array           $stateValue The previously know state value for this property. The builder should populate the new state into the given variable.
     * @param   InvokableChain  $actions    The actions to perform. The builder may append additional actions
     * @return  array                       The resulting update oprations
     */
    public function buildUpdateDocument($object, $property, $prefix, &$stateValue, InvokableChain $actions);

    /**
     * Populate removal
     *
     * @param   string          $property   The property
     * @param   string          $prefix     The path perfix
     * @param   object          $stateValue The previously known state value
     * @param   InvokableChain  $actions    The actions to perform. The builder may append additional actions
     */
    public function buildUndefinedInDocument($property, $prefix, $stateValue, InvokableChain $actions);

    /**
     * Build the insert or creation for this document
     *
     * @param   object          $object     The aggregated object to persist
     * @param   string          $property   The property
     * @param   string          $prefix     The path perfix
     * @param   InvokableChain  $actions    The actions to perform. The builder may append additional actions
     * @return  mixed                       The resulting document or value to populate into the property
     */
    public function buildInsertDocument($object, $property, $prefix, InvokableChain $actions);
}
