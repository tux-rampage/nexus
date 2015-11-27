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

namespace rampage\nexus\entities;

use Doctrine\ODM\MongoDB\Mapping\Annotations as odm;


/**
 * Vhost definition
 */
class VHost
{
    /**
     * @odm\Id(strategy="NONE")
     * @var string
     */
    protected $name;

    /**
     * @odm\Field(type="string")
     * @var string
     */
    protected $flavor = null;

    /**
     * @odm\Hash()
     * @var string[]
     */
    protected $aliases = [];

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * @param string $flavor
     */
    public function setFlavor($flavor)
    {
        $this->flavor = ($flavor !== null)? (string)$flavor : null;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * @param string $name
     * @return self
     */
    public function addAlias($name)
    {
        if (!in_array($name, $this->aliases)) {
            $this->aliases[] = $name;
        }

        return $this;
    }

    /**
     * @param string $name
     * @return self
     */
    public function removeAlias($name)
    {
        $offset = array_search($name, $this->aliases);

        if ($offset !== false) {
            unset($this->aliases[$offset]);
        }

        return $this;
    }
}