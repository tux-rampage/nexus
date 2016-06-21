<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @license   LUKA Proprietary
 * @copyright Copyright (c) 2016 LUKA netconsult GmbH (www.luka.de)
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
