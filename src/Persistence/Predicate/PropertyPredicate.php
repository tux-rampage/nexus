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

namespace Rampage\Nexus\Persistence\Predicate;

use Rampage\Nexus\Exception\UnexpectedValueException;
use Rampage\Nexus\Exception\BadMethodCallException;

/**
 * Implements the default property predicate
 *
 * @method PropertyPredicate eq($value)
 * @method PropertyPredicate gt($value)
 * @method PropertyPredicate gte($value)
 * @method PropertyPredicate in($value)
 * @method PropertyPredicate lt($value)
 * @method PropertyPredicate lte($value)
 * @method PropertyPredicate neq($value)
 * @method PropertyPredicate notIn($value)
 */
class PropertyPredicate implements PropertyPredicateInterface
{
    const PROPERTY_PATTERN = '[a-z_][a-z0-9_]*';

    /**
     * @var string
     */
    private $property;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $operator;

    /**
     * @param string $property
     * @param string $value
     * @param string $operator
     */
    public function __construct($property, $value = null, $operator = self::OPERATOR_EQUAL)
    {
        $regex = '~^' . self::PROPERTY_PATTERN . '(\.' . self::PROPERTY_PATTERN . ')*$~i';
        if (!is_string($property) || !preg_match($regex, $property)) {
            throw new UnexpectedValueException('Bad property ');
        }

        $this->property = $property;
        $this->setCompareValue($value);
        $this->setOperator($operator);
    }

    /**
     * Call for mapping comparsion operators
     *
     * @param   string  $method
     * @param   array   $args
     * @throws  BadMethodCallException
     * @return  self
     */
    public function __call($method, $args)
    {
        $map = [
            'eq' => self::OPERATOR_EQUAL,
            'gt' => self::OPERATOR_GREATER,
            'gte' => self::OPERATOR_GREATER_OR_EQUEAL,
            'in' => self::OPERATOR_IN,
            'lt' => self::OPERATOR_LOWER,
            'lte' => self::OPERATOR_LOWER_OR_EQUAL,
            'neq' => self::OPERATOR_NOT_EQUAL,
            'notIn' => self::OPERATOR_NOT_IN,
        ];

        if (!isset($map[$method])) {
            throw new BadMethodCallException('Invalid comparsion method: ' . $method);
        }

        $value = (isset($args[0]))? $args[0] : null;

        $this->setCompareValue($value);
        $this->setOperator($map[$method]);

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Persistence\Predicate\PropertyPredicateInterface::getCompareValue()
     */
    public function getCompareValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return self
     */
    public function setCompareValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Persistence\Predicate\PropertyPredicateInterface::getOperator()
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     * @return self
     */
    public function setOperator($operator)
    {
        $this->operator = (string)$operator;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Persistence\Predicate\PropertyPredicateInterface::getProperty()
     */
    public function getProperty()
    {
        return $this->property;
    }
}
