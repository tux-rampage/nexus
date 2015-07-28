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

namespace rampage\nexus\package\constraint;

/**
 * Match a specific version constraint
 */
class MatchVersionConstraint implements ConstraintInterface
{
    /**
     * @var string
     */
    protected $version;

    /**
     * @var string
     */
    protected $operator;

    /**
     * @param string $version
     * @param string $operator
     */
    public function __construct($version, $operator)
    {
        $this->version = $version;
        $this->operator = $operator;
    }

    /**
     * {@inheritdoc}
     */
    public function match($version)
    {
        return version_compare($version, $this->version, $this->operator);
    }
}
