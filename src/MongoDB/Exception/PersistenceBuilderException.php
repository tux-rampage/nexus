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

namespace Rampage\Nexus\MongoDB\Exception;

use Rampage\Nexus\Exception\RuntimeException;
use Throwable;

class PersistenceBuilderException extends RuntimeException
{
    /**
     * @var string
     */
    protected $property;

    /**
     * @var string
     */
    protected $class;

    /**
     * @param string            $class
     * @param string|Throwable  $messageOrPrevious
     * @param string            $property
     * @param int               $code
     * @param Throwable         $previous
     */
    public function __construct($class, $messageOrPrevious, $property = null, $code = 0, Throwable $previous = null)
    {
        if ($messageOrPrevious instanceof self) {
            $message = $messageOrPrevious->getMessage();
            $previous = $messageOrPrevious;
            $code = $previous->getCode();
            $property = $this->cascadePreviousProperty($property, $previous);
        } else if ($messageOrPrevious instanceof Throwable) {
            $message = $messageOrPrevious->getMessage();
            $previous = $messageOrPrevious;
            $code = $previous->getCode();
        } else {
            $message = $messageOrPrevious;
        }

        $this->property = $property;
        $this->class = $class;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Cascade exception properties to the property path
     *
     * @param   string                      $property
     * @param   PersistenceBuilderException $exception
     * @return  string
     */
    private function cascadePreviousProperty($property, PersistenceBuilderException $exception)
    {
        if (!$exception instanceof NestedBuilderException) {
            return $property;
        }

        if (!$property) {
            return $exception->getProperty();
        }

        if (!$exception->getProperty()) {
            return $property;
        }

        return $property . '.' . $exception->getProperty();
    }

    /**
     * Returns the affected property
     *
     * @return string|null
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Returns the class to perist
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     * @see RuntimeException::__toString()
     */
    public function __toString()
    {
        $class = get_class($this);
        $file = $this->getFile();
        $line = $this->getLine();
        $previous = $this->getPrevious();
        $property = $this->getProperty();

        if ($line !== null) {
            $file .= ':' . (int)$line;
        }

        $str = "exception '$class' with message '{$this->getMessage()}'\n\n"
             . "Class to persist: {$this->getClass()}\n"
             . (($property)? "Property: $property\n" : '')
             . "in $file\ncode: {$this->getCode()}\n\nStack trace:"
             . $this->getTraceAsString() . "\n\n";

        if ($previous) {
            $str = $previous . 'Next ' . $str;
        }

        return $str;
    }
}
