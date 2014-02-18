<?php
/**
 * This is part of rampage-nexus
 * Copyright (c) 2014 Axel Helmert
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
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus;

class ConfigProperty
{
    /**
     * @orm\Column(type="string", nullable=false) @orm\Id
     * @var string
     */
    protected $name = null;

    /**
     * @orm\Column(type="text", nullable=true)
     * @var string
     */
    protected $value = null;

    /**
     * @orm\Column(type="string", nullable=false)
     * @var string
     */
    protected $type = 'string';

    /**
     * @param string $name
     * @param string $type
     */
    public function __construct($name = null, $type = null)
    {
        $this->name = $name;
        $this->type = $type? : 'string';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        // TODO
    }

    /**
     * @param string $value
     * @return self
     */
    public function setValue($value)
    {
        // TODO
        $this->value = $value;
        return $this;
    }
}
