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

/**
 * Strategy for calculating document updates
 */
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
     * @var string
     */
    private $field;

    /**
     * @param array $previousData
     */
    public function __construct(array $previousData, $field = null)
    {
        $this->previousData = $previousData;
        $this->field = $field;
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
     * @return CalculateUpdateStrategy
     */
    private function calculateDocumentChanges(array $doc, array $previous, $field = null)
    {
        return (new self($previous, $field))->calculate($doc);
    }

    /**
     * @param string $fieldName
     * @return int|NULL
     */
    protected function getFieldStrategy($fieldName)
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
                    $changes = $this->calculateDocumentChanges($value, $previousValue, $path);
                    $this->merge($changes);
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

    /**
     * Merge an update strategy result
     *
     * @param CalculateUpdateStrategy $strategy
     * @return \Rampage\Nexus\MongoDB\CalculateUpdateStrategy
     */
    protected function merge(CalculateUpdateStrategy $strategy)
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
     * Returns all update instruction in execution order
     *
     * @return array[]
     */
    public function getOrderedInstructions()
    {
        $updates = array_filter($this->instructions, 'count');
        $pulls = [];

        foreach (['$pull', '$pullAll'] as $op) {
            if (isset($updates[$op])) {
                $pulls[$op] = $updates[$op];
                unset($updates[$op]);
            }
        }

        $all = [ $updates, $pulls ];
        return array_filter($all, 'count');
    }

    /**
     * Returns the update instructions
     *
     * @return array
     */
    public function calculate(array $data)
    {
        $this->instructions = [];

        if (empty($data) || !$this->isAssoc($this->previousData)) {
            if (empty($data)) {
                $data = new \stdClass();
            }

            if ($this->field !== null) {
                $this->instructions['$set'][$this->field] = $data;
            } else {
                $this->instructions['$set'] = $data;
            }

            return;
        }

        $previousKeys = array_keys($this->previousData);
        $removedKeys = array_diff($previousKeys, array_keys($data));
        $keptKeys = array_diff($previousKeys, $removedKeys);
        $prefix = ($this->field !== null)? $this->field . '.' : '';

        if (empty($keptKeys)) {
            if ($this->field !== null) {
                $this->instructions['$set'][$this->field] = $data;
            } else {
                $this->instructions['$set'] = $data;
            }

            return;
        }

        if (!empty($removedKeys)) {
            foreach ($removedKeys as $key) {
                $key = $prefix . $key;
                $this->instructions['$unset'][$key] = true;
            }
        }

        foreach ($data as $key => $value) {
            $fieldPath = $prefix . $key;

            if (!array_key_exists($key, $this->previousData) || (gettype($value) != gettype($this->previousData[$key]))) {
                $this->instructions['$set'][$fieldPath] = $value;
                continue;
            }

            if (is_array($value)) {
                if ($this->isAssoc($value)) {
                    $changes = $this->calculateDocumentChanges($value, $this->previousData[$key], $fieldPath);

                    if (!$changes->isEmpty()) {
                        $this->merge($changes);
                    }
                } else {
                    $strategy = $this->getFieldStrategy($fieldPath);

                    if ($strategy == self::STRATEGY_SET) {
                        $this->instructions['$set'][$fieldPath] = $value;
                    } else {
                        $this->calculateCollectionChanges($value, $this->previousData[$key], $fieldPath, $strategy);
                    }
                }

                continue;
            }

            if ($value !== $this->previousData[$key]) {
                $this->instructions['$set'][$fieldPath] = $value;
            }
        }

        return $this;
    }
}
