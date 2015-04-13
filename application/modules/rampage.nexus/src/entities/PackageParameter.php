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
 * @odm\EmbeddedDocument
 */
class PackageParameter
{
    /**
     * @odm\String
     * @var string
     */
    protected $name;

    /**
     * @odm\String
     * @var string
     */
    protected $type = 'text';

    /**
     * @odm\String
     * @var string
     */
    protected $default = null;

    /**
     * @odm\Hash
     * @var array
     */
    protected $options = [];

    /**
     * @odm\Boolean
     * @var bool
     */
    protected $required = false;

    /**
     * @param string $name
     */
    public function __construct($name = null)
    {
        if ($name !== null) {
            $this->setName($name);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = (string)$name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = (string)$type;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param string $default
     * @return self
     */
    public function setDefault($default)
    {
        $this->default = (string)$default;
        return $this;
    }

    /**
     * @return multitype:
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $option
     * @return self
     */
    public function addOption($option, $label = null)
    {
        $option = (string)$option;

        if ($label === null) {
            $label = $option;
        }

        $this->options[$option] = (string)$label;
        return $this;
    }

    /**
     * @param array $options
     * @return self
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param boolean $required
     * @return self
     */
    public function setRequired($required)
    {
        $this->required = (bool)$required;
        return $this;
    }
}
