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

namespace Rampage\Nexus\Persistence\Predicate;

/**
 * Interface for property comparsion
 */
interface PropertyPredicateInterface extends PredicateInterface
{
    const OPERATOR_EQUAL = '=';
    const OPERATOR_NOT_EQUAL = '!=';
    const OPERATOR_GREATER = '>';
    const OPERATOR_LOWER = '<';
    const OPERATOR_GREATER_OR_EQUEAL = '>=';
    const OPERATOR_LOWER_OR_EQUAL = '<=';
    const OPERATOR_IN = 'in';
    const OPERATOR_NOT_IN = 'not_in';

    /**
     * Returns the operator
     *
     * See the `OPERATOR_*` constants for possible types
     *
     * @return string
     */
    public function getOperator();

    /**
     * Returns the property
     *
     * The properties of nested objects are separated via dot.
     * i.e. `foo.bar` will reflect `$entity->foo->bar`.
     *
     * Persistence builders have to detect if the property is an object reference or not
     * via mapping and act accordingly.
     *
     * The persistence builder may throw exceptions when the property is embedded and does not
     * have an (internal) identifier to map to.
     *
     * @return string
     */
    public function getProperty();

    /**
     * The value used to comparsion against the property
     *
     * This might be an object or the identifier value if the property is an object reference.
     *
     * @return mixed
     */
    public function getCompareValue();
}
