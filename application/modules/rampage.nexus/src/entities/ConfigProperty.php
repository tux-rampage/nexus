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

namespace rampage\nexus\entities;

class ConfigProperty
{
    const TYPE_STRING = 'string';
    const TYPE_INT = 'integer';
    const TYPE_BOOL = 'boolean';
    const TYPE_FLOAT = 'double';

    /**
     * @orm\Id @orm\Column(type="string", nullable=false)
     * @var string
     */
    protected $name = null;

    /**
     * @orm\Column(type="text", nullable=true)
     * @var string
     */
    protected $value = null;

    /**
     * @var mixed
     */
    protected $phpValue = null;

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
     * @return self
     */
    protected function serializeValue()
    {
        if ($this->type == self::TYPE_BOOL) {
            $this->value = ($this->phpValue)? 'true' : 'false';
            return $this;
        }

        $this->value = (string)$this->phpValue;
        return $this;
    }

    /**
     * @return self
     */
    protected function unserializeValue()
    {
        switch ($this->type) {
            case self::TYPE_INT:
                $this->phpValue = (int)$this->value;

            case self::TYPE_BOOL:
                $this->phpValue = ($this->value == 'false')? false : true;
                break;

            case self::TYPE_FLOAT:
                $this->phpValue = (float)$this->value;
                break;

            case self::TYPE_STRING:
            default:
                $this->phpValue = (string)$this->value;
                break;
        }

        return $this;
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
        if ($this->phpValue === null) {
            $this->unserializeValue();
        }

        return $this->phpValue;
    }

    /**
     * @param string $value
     * @return self
     */
    public function setValue($value)
    {
        if (!is_scalar($value)) {
            throw new \UnexpectedValueException(sprintf('The config value must be a scalar value, %s given', gettype($value)));
        }

        $this->type = gettype($value);
        $this->phpValue = $value;

        $this->serializeValue();

        return $this;
    }
}
