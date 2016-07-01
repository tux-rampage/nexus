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

namespace Rampage\Nexus\MongoDB\Mapping;

/**
 * Provides mapping for a specific class
 */
interface ClassMappingInterface
{
    const REFERENCE_ONE = 1;
    const REFERENCE_MANY = 2;
    const AGGREGATE_ONE = 4;
    const AGGREGATE_MANY = 8;

    /**
     * Returns the class name this mapper is responsible for
     *
     * @return string
     */
    public function getClass();

    /**
     * @param string $name
     * @return
     */
    public function propertyIsReference($name);

    /**
     * Maps the property value
     *
     * From the persisted state to the object.
     * The property must not be a reference.
     *
     * @param string $property
     * @param mixed $mongoValue
     * @return mixed
     */
    public function mapPersistedValue($property, $mongoValue);

    /**
     * Maps the property value
     *
     * From the object to persistence state.
     * The property must not be a reference.
     *
     * @param string $property
     * @param mixed $mongoValue
     * @return mixed
     */
    public function mapObjectValue($property, $mongoValue);

    /**
     * Checks if the property is a reference
     *
     * @param string $property
     * @return bool
     */
    public function isReference($property);

    /**
     * Returns the reference typ for the property
     *
     * @param string $property The property name. The property named by it, must be a refernece
     * @return int;
     */
    public function getReferenceType($property);

    /**
     * Returns the referenced class
     *
     * @param string $porperty
     * @return null
     */
    public function getReferencedClass($porperty);

    /**
     * @return string
     */
    public function getRepositoryClass();
}
