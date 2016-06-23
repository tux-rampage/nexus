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
 * Implements a platform dependency
 */
class PlatformDependency implements DependencyInterface
{
    /**
     * Typename for platforms
     */
    const TYPE_PLATFORM = 'platform';

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $constraint = null;

    /**
     * @param string $name
     * @param string $constraint
     */
    public function __construct($name, $constraint = '*')
    {
        $this->name = $name;
        $this->constraint = $constraint;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraint()
    {
        return (new Constraint\ConstraintBuilder())->createConstraint($this->constraint);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_PLATFORM;
    }
}
