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

namespace Rampage\Nexus\Config;

use Rampage\Nexus\Exception\InvalidArgumentException;
use ArrayAccess;

/**
 * Array config
 *
 * Allows to access even nested array elements as properties.
 */
class ArrayConfig implements PropertyConfigInterface
{
    /**
     * Config data array
     *
     * @var array
     */
    protected $data = [];

    /**
     * Properties cache
     *
     * @var array
     */
    protected $properties = [];

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        if (!is_array($data) && !($data instanceof ArrayAccess)) {
            throw new InvalidArgumentException('$data must be an array or implement array access');
        }

        $this->data = $data;
    }

    /**
     * Flatten the config data into the property value
     *
     * @param   string  $property
     * @return  mixed
     */
    private function flatten($property)
    {
        $parts = explode('.', $property);
        $current = $this->data;

        foreach ($parts as $key) {
            if ((!is_array($current) && !($current instanceof \ArrayAccess)) || !isset($current[$key])) {
                return null;
            }

            $current = $current[$key];
        }

        return $current;
    }

    /**
     * Returns the value for the requested property
     *
     * nested properties might be dot separated
     *
     * @param   string  $property
     * @param   mixed   $default
     * @return  mixed
     */
    public function get($property, $default = null)
    {
        if (!array_key_exists($property, $this->properties)) {
            $this->properties[$property] = $this->flatten($property);
        }

        if (!isset($this->properties[$property])) {
            return $default;
        }

        return $this->properties[$property];
    }
}
