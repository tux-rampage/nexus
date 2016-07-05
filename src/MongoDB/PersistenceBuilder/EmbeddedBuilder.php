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

/**
 * Builder for embedded documents
 */
class EmbeddedBuilder implements AggregateBuilderInterface
{
    use PersistenceBuilderTrait;

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateBuilderInterface::buildInsertDocument()
     */
    public function buildInsertDocument($object, $property, $prefix, InvokableChain $actions)
    {
        $data = $this->hydrator->extract($object);
        $propertyPath = $this->prefixPropertyPath($property, $prefix);

        foreach ($this->mappedRefProperties as $key) {
            unset($data[$key]);
        }

        foreach ($this->aggregationProperties as $key => $persister) {
            if (!isset($data[$key])) {
                continue;
            }

            $aggregatedObject = $data[$key];
            $data[$key] = $persister->buildInsertDocument($aggregatedObject, $key, $propertyPath, $actions);
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateBuilderInterface::buildUndefinedInDocument()
     */
    public function buildUndefinedInDocument($property, $prefix, $stateValue, InvokableChain $actions)
    {
        $propertyPath = $this->prefixPropertyPath($property, $prefix);

        foreach ($this->aggregationProperties as $key => $persister) {
            if (!isset($stateValue[$key])) {
                continue;
            }

            $persister->buildUndefinedInDocument($key, $propertyPath, $stateValue[$key], $actions);
        }
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateBuilderInterface::buildUpdateDocument()
     */
    public function buildUpdateDocument($object, $property, $prefix, &$stateData, InvokableChain $actions)
    {
        $updateDocument = [];
        $data = $this->hydrator->extract($object);
        $propertyPath = $this->prefixPropertyPath($property, $prefix);
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

            $updateDocument['$set'][$this->prefixPropertyPath($key, $propertyPath)] = $value;
        }

        foreach ($this->aggregationProperties as $key => $persister) {
            if (!isset($data[$key])) {
                if (isset($stateData[$key])) {
                    $persister->buildUndefinedInDocument($key, $propertyPath, $stateData[$key], $actions);
                }

                continue;
            }

            $aggregatedObject = $data[$key];

            if (isset($stateData[$key])) {
                $state = $stateData[$key];
                $updates = $persister->buildUpdateDocument($aggregatedObject, $key, $propertyPath, $state, $actions);
                $newStateData[$key] = $state;
                $updateDocument = array_merge_recursive($updateDocument, $updates);
            } else {
                $doc = $persister->buildInsertDocument($aggregatedObject, $key, $propertyPath, $actions);
                $newStateData[$key] = $doc;
                $updateDocument['$set'][$this->prefixPropertyPath($key, $propertyPath)] = $doc;
            }
        }

        $stateData = $newStateData;

        return $updateDocument;
    }
}
