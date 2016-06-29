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

namespace Rampage\Nexus\Exception;

class UnexpectedValueException extends \UnexpectedValueException implements ExceptionInterface
{
    const CODE_NOT_IN_SET = 1;
    const CODE_NO_MATCH = 2;
    const CODE_UNEXPECTED = 4;

    /**
     * @param mixed $value
     * @return string
     */
    private static function stringifyValue($value)
    {
        if (is_scalar($value)) {
            return '[' . gettype($value) . ':"' . $value . '"]';
        }

        if (is_object($value)) {
            return '[' . get_class($value) . ']';
        }

        return '[' . gettype($value) . ']';
    }

    /**
     * @param   string      $name
     * @param   mixed       $value
     * @param   string[]    $allowedValues
     * @return  self
     */
    public static function notInSet($name, $value, array $allowedValues)
    {
        $printableValue = self::stringifyValue($value);
        return new self(sprintf('%s must be one of [ %s ]. Received %s.', $name, implode(', ', $allowedValues), $printableValue), self::CODE_NOT_IN_SET);
    }

    /**
     * @param   string  $name
     * @param   mixed   $value
     * @param   mixed   $expected
     * @return  self
     */
    public static function notMatching($name, $value, $expected)
    {
        return new self(sprintf(
            '%s must be %s. Received %s.',
            $name,
            self::stringifyValue($value),
            self::stringifyValue($expected)
        ), self::CODE_NO_MATCH);
    }
}
