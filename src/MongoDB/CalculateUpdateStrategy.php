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

namespace Rampage\Nexus\MongoDB;

use Rampage\Nexus\Exception\LogicException;

class CalculateUpdateStrategy
{
    const STRATEGY_ADDTOSET = 1;
    const STRATEGY_SET = 2;

    /**
     * @var array
     */
    private $previousData;

    /**
     * @var array
     */
    private $instructions = null;

    /**
     * @var array
     */
    private $fieldStrategies = [];

    /**
     * @param array $previousData
     */
    public function __construct(array $previousData)
    {
        $this->previousData = $previousData;
    }

    /**
     * @param array $array
     * @return boolean
     */
    private function isAssoc(array $array)
    {
        reset($array);
        return is_string(key($array));
    }

    /**
     * Checks if the given array is a valid collection
     *
     * This means all keys must be integers with an unbroken sequence
     *
     * @param array $array
     */
    private function isCollection(array $array)
    {
        $expected = 0;

        foreach (array_keys($array) as $key) {
            if ($key !== $expected) {
                return false;
            }

            $expected++;
        }

        return true;
    }

    /**
     * Calculate document changes
     *
     * @param array $doc
     * @param array $previous
     * @param string $field
     */
    private function calculateDocumentChanges(array $doc, array $previous, $field = null)
    {
        if (!$this->isAssoc($previous)) {
            if ($field !== null) {
                $this->instructions['$set'][$field] = $doc;
            } else {
                $this->instructions['$set'] = $doc;
            }

            return;
        }

        $previousKeys = array_keys($previous);
        $removedKeys = array_diff($previousKeys, array_keys($doc));
        $keptKeys = array_diff($previousKeys, $removedKeys);
        $prefix = ($field !== null)? $field . '.' : '';

        if (empty($keptKeys)) {
            if ($field !== null) {
                $this->instructions['$set'][$field] = $doc;
            } else {
                $this->instructions['$set'] = $doc;
            }

            return;
        }

        if (!empty($removedKeys)) {
            foreach ($removedKeys as $key) {
                $key = $prefix . $key;
                $this->instructions['$unset'][$key] = true;
            }
        }

        foreach ($doc as $key => $value) {
            $fieldPath = $prefix . $key;

            if (!array_key_exists($key, $previous) || (gettype($value) != gettype($previous[$key]))) {
                $this->instructions['$set'][$fieldPath] = $value;
                continue;
            }

            if (is_array($value)) {
                if ($this->isAssoc($value)) {
                    $this->calculateDocumentChanges($value, $previous[$key], $fieldPath);
                } else {
                    $strategy = $this->getFieldStrategy($fieldPath);

                    if ($strategy == self::STRATEGY_SET) {
                        $this->instructions['$set'][$fieldPath] = $value;
                    } else {
                        $this->calculateCollectionChanges($value, $previous[$key], $fieldPath, $strategy);
                    }
                }

                continue;
            }

            if ($value !== $previous[$key]) {
                $this->instructions['$set'][$fieldPath] = $value;
            }
        }
    }

    /**
     * @param string $fieldName
     * @return int|NULL
     */
    private function getFieldStrategy($fieldName)
    {
        if (isset($this->fieldStrategies[$fieldName])) {
            return $this->fieldStrategies[$fieldName];
        }

        $pattern = preg_replace('~\.\d+(\.|$)~', '.*\1', $fieldName);
        if (isset($this->fieldStrategies[$pattern])) {
            return $this->fieldStrategies[$pattern];
        }

        return null;
    }

    /**
     * @param array $collection
     * @param array $previous
     * @param unknown $field
     * @param unknown $strategy
     */
    private function calculateCollectionChanges(array $collection, array $previous, $field, $strategy)
    {
        if (!$this->isCollection($previous)) {
            $this->instructions['$set'][$field] = $collection;
            return;
        }

        $collection = array_values($collection);

        if ($strategy == self::STRATEGY_ADDTOSET) {
            $add = array_diff($collection, $previous);
            $remove = array_diff($previous, $collection);

            if (!empty($add)) {
                $this->instructions['$addToSet'][$field]['$each'] = $add;
            }

            if (!empty($remove)) {
                $this->instructions['$pullAll'][$field] = $remove;
            }

            return;
        }

        $index = 0;

        foreach ($collection as $value) {
            $previousValue = array_shift($previous);
            $path = $field . '.' . $index;

            if (is_array($value)) {
                if (!is_array($previousValue)) {
                    $this->instructions['$set'][$path] = $value;
                } else if ($this->isAssoc($value)) {
                    $this->calculateDocumentChanges($value, $previousValue, $path);
                } else {
                    $this->calculateCollectionChanges($value, $previousValue, $path, $this->getFieldStrategy($path));
                }
            } else if ($value !== $previousValue) {
                $this->instructions['$set'][$path] = $value;
            }

            $index++;
        }

        if (count($previous) > 0) {
            for ($i = 0; $i < count($previous); $i++) {
                $path = $field . '.' . ($index + $i);
                $this->instructions['$set'][$path] = null;
            }

            $this->instructions['$pull'][$field] = null;
        }
    }

    public function merge(CalculateUpdateStrategy $strategy)
    {
        foreach ($strategy->instructions as $key => $instructions) {
            if (!isset($this->instructions[$key])) {
                $this->instructions[$key] = $instructions;
                continue;
            }

            $this->instructions[$key] = array_merge($this->instructions[$key], $instructions);
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function isEmpty()
    {
        foreach (array_keys($this->instructions) as $key) {
            if ($this->hasInstructions($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $type
     * @return boolean
     */
    public function hasInstructions($type)
    {
        if (isset($this->instructions[$type])) {
            return (count($this->instructions[$type]) > 0);
        }

        return false;
    }

    /**
     * @param string $type
     * @throws LogicException
     * @return array
     */
    public function getInstructions($type)
    {
        if (!$this->hasInstructions($type)) {
            throw new LogicException('No instructions available for type ' . $type);
        }

        return $this->instructions[$type];
    }

    /**
     * Returns the update instructions
     *
     * @return array
     */
    public function calculate(array $data)
    {
        $this->instructions = [];
        $this->calculateDocumentChanges($data, $this->previousData);

        return $this;
    }
}