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

namespace Rampage\Nexus\Entities;

use Rampage\Nexus\Package\ParameterInterface;
use Zend\Stdlib\Parameters;

/**
 * Implements a persistable package parameter
 *
 * @return array
 */
class PackageParameter implements ParameterInterface, Api\ArrayExchangeInterface
{
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
     * @var string
     */
    protected $default = null;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $valueOptions = null;

    /**
     * @var bool
     */
    protected $required = false;

    /**
     * Construct the parameter
     *
     * @param string $name
     */
    public function __construct($name = null)
    {
        if ($name instanceof ParameterInterface) {
            $this->copy($name);
        } else {
            $this->setName($name);
        }
    }

    /**
     * Copy from another parameter implementation
     *
     * @param ParameterInterface $parameter
     */
    protected function copy(ParameterInterface $parameter)
    {
        $this->default = $parameter->getDefault();
        $this->label = $parameter->getLabel();
        $this->name = $parameter->getName();
        $this->options = null;
        $this->required = $parameter->isRequired();
        $this->type = $parameter->getType();
        $this->options = $parameter->getOptions();

        if ($parameter->hasValueOptions()) {
            $this->valueOptions = $parameter->getValueOptions();
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
     * @return string
     */
    public function getLabel()
    {
        if (!$this->label) {
            return $this->getName();
        }

        return $this->label;
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
     * {@inheritDoc}
     * @see \Rampage\Nexus\Package\ParameterInterface::hasValueOptions()
     */
    public function hasValueOptions()
    {
        return ($this->valueOptions !== null);
    }


    /**
     * @return multitype:
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Package\ParameterInterface::getValueOptions()
     */
    public function getValueOptions()
    {
        return $this->valueOptions;
    }

    /**
     * Remove all options
     *
     * @return self
     */
    public function removeValueOptions()
    {
        $this->valueOptions = null;
        return $this;
    }

    /**
     * @param string $option
     * @return self
     */
    public function addValueOption($option, $label = null)
    {
        $option = (string)$option;

        if ($label === null) {
            $label = $option;
        }

        if (!is_array($this->valueOptions)) {
            $this->valueOptions = [];
        }

        $this->valueOptions[$option] = (string)$label;
        return $this;
    }

    /**
     * @param array $options
     * @return self
     */
    public function setValueOptions(array $options)
    {
        $this->valueOptions = [];

        foreach ($options as $key => $value) {
            $this->addValueOption($key, $value);
        }

        return $this;
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * Set arbitary element options
     *
     * @param array $options
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

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExchangeInterface::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        $data = new Parameters($array);

        $this->name = $data->get('name', $this->name);
        $this->default = $data->get('default');
        $this->label = $data->get('label');
        $this->required = (bool)$data->get('required');
        $this->type = $data->get('type');
        $this->valueOptions = null;
        $this->setOptions($data->get('options'));

        $valueOptions = $data->get('valueOptions');

        if (is_array($valueOptions)) {
            $this->setValueOptions($valueOptions);
        }
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExportableInterface::toArray()
     */
    public function toArray()
    {
        $array = [
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'default' => $this->getDefault(),
            'type' => $this->getType(),
            'required' => $this->isRequired(),
            'options' => $this->getOptions(),
        ];

        if ($this->hasValueOptions()) {
            $array['valueOptions'] = $this->getValueOptions();
        }

        return $array;
    }
}
