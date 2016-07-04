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

namespace Rampage\Nexus\MongoDB\PersistenceBuilder;

use Rampage\Nexus\MongoDB\InvokableChain;
use Rampage\Nexus\MongoDB\EntityState;

class EmbeddedPersisterBuilder implements AggregateBuilderInterface
{
    use PersistenceBuilderTrait;

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateBuilderInterface::buildInsertDocument()
     */
    public function buildInsertDocument($object, array &$parent, $property, $prefix, array &$root)
    {
        $data = $this->hydrator->extract($object);
        $propertyPath = $this->prefixPropertyPath($property, $prefix);
        $callbacks = new InvokableChain();

        foreach ($this->mappedRefProperties as $key) {
            unset($data[$key]);
        }

        foreach ($this->aggregationProperties as $key => $persister) {
            if (!isset($data[$key])) {
                continue;
            }

            $aggregatedObject = $data[$key];
            unset($data[$key]);
            $callback = $persister->buildInsertDocument($aggregatedObject, $data, $key, $propertyPath, $root);

            if ($callback) {
                $callbacks->add($callback);
            }
        }

        $parent[$property] = $data;
        return ($callbacks->count())? $callbacks : null;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateBuilderInterface::buildUndefinedInDocument()
     */
    public function buildUndefinedInDocument($property, $prefix, array &$root, EntityState $state)
    {
        $propertyPath = $this->prefixPropertyPath($property, $prefix);
        $callbacks = new InvokableChain();

        foreach ($this->aggregationProperties as $key => $persister) {
            $callback = $persister->buildUndefinedInDocument($key, $propertyPath, $root, $state);

            if ($callback) {
                $callbacks->add($callback);
            }
        }

        return ($callbacks->count())? $callbacks : null;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateBuilderInterface::buildUpdateDocument()
     */
    public function buildUpdateDocument($object, array &$parent, $property, $prefix, array &$root, EntityState $state)
    {
        $data = $this->hydrator->extract($object);
        $propertyPath = $this->prefixPropertyPath($property, $prefix);
        $callbacks = new InvokableChain();
        $stateData = $parent[$property];
        $newStateData = [];

        if (!is_array($stateData)) {
            $stateData = [];
        }

        foreach ($data as $key => $value) {
            if ($this->isExcludedFromDiff($key)) {
                continue;
            }

            $newStateData[$key] = $value;

            if (isset($stateData[$key]) && ($stateData[$key] == $value)) {
                continue;
            }

            $root['$set'][$this->prefixPropertyPath($key, $propertyPath)] = $value;
        }

        foreach ($this->aggregationProperties as $key => $persister) {
            if (!isset($data[$key])) {
                $callback = $persister->buildUndefinedInDocument($key, $propertyPath, $root, $state);
            } else {
                $aggregatedObject = $data[$key];
                $newStateData[$key] = isset($stateData[$key])? $stateData[$key] : null;
                $callback = $persister->buildUpdateDocument($aggregatedObject, $newStateData, $key, $propertyPath, $root, $state);
            }

            if ($callback) {
                $callbacks->add($callback);
            }
        }

        return ($callbacks->count())? $callbacks : null;
    }
}
