<?php
/**
 * Copyright (c) 2015 Axel Helmert
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
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\Package;

/**
 * Defines Package parameters
 */
interface ParameterInterface
{
    /**
     * The parameter name
     *
     * This name is a valid variable name an can be passed as environment varaible.
     *
     * @return  string
     */
    public function getName();

    /**
     * The parameter type
     *
     * This can be used to build the proper form input
     *
     * @return string
     */
    public function getType();

    /**
     * The default value for this parameter
     *
     * @return string
     */
    public function getDefault();

    /**
     * The human readable label of this parameter
     *
     * @return string
     */
    public function getLabel();

    /**
     * Check if allowed options are defined
     *
     * If so the implementation should check if the user provided value
     * matches on of the keys in the array returned by `getOptions()`
     *
     * @return  bool
     */
    public function hasValueOptions();

    /**
     * Allowed values
     *
     * This method will return an array with allowed values as keys and a human readable
     * label as value.
     *
     * This can be used to build select options for example.
     *
     * @return array
     */
    public function getValueOptions();

    /**
     * Arbitary element options
     *
     * @return array
     */
    public function getOptions();

    /**
     * This parameters requirement
     *
     * @return  boolean     `true` if the parameter is required, `false` if not.
     */
    public function isRequired();
}
