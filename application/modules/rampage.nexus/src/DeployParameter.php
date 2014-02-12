<?php
/**
 * This is part of rampage-nexus
 * Copyright (c) 2013 Axel Helmert
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
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus;

class DeployParameter
{
    const TYPE_TEXT = 'text';
    const TYPE_SELECT = 'select';
    const TYPE_PASSWORD = 'password';
    const TYPE_CHECKBOX = 'checkbox';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $type = 'text';

    /**
     * @var bool
     */
    protected $required = false;

    /**
     * @var string
     */
    protected $options = null;

    /**
     * @param string $name
     * @param string $label
     */
    public function __construct($name, $label = null)
    {
        $this->name = $name;
        $this->label = $label? : $name;
    }

    /**
     * @param string $name
     * @param array $options
     * @return DeployParameter
     */
    public static function factory($name, $options = array())
    {
        if (!is_array($options)) {
            $options = array();
        }

        $label = isset($options['label'])? $options['label'] : null;
        $param = new DeployParameter($name, $label);

        $param->setIsRequired((isset($options['required']))? $options['required'] : false)
            ->setOptions(isset($options['options'])? $options['options'] : array());

        if (isset($options['type'])) {
            $param->setType($options['type']);
        }

        return $param;
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
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $label
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param boolean $required
     * @return self
     */
    public function setIsRequired($required = true)
    {
        $this->required = (bool)$required;
        return $this;
    }

    /**
     * @param string $options
     * @return self
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }



}
