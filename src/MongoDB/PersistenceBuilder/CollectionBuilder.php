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

use Rampage\Nexus\Entities\IndexableCollectionInterface;
use Rampage\Nexus\Exception\InvalidArgumentException;
use Rampage\Nexus\MongoDB\Driver;
use Rampage\Nexus\MongoDB\InvokableChain;
use Rampage\Nexus\MongoDB\ImmutablePersistedCollection;
use Rampage\Nexus\MongoDB\PersistedCollection;

use Traversable;
use Rampage\Nexus\Exception\LogicException;


/**
 * Persistence builder vor aggregate collections
 */
class AggregateCollectionBuilder implements AggregateBuilderInterface
{
    use PropertyPathTrait;

    /**
     * @var bool
     */
    private $indexed = false;

    /**
     * @var AggregateBuilderInterface
     */
    private $itemBuilder;

    /**
     * @var Driver\CollectionInterface
     */
    protected $collection;

    /**
     * @param AggregateBuilderInterface $itemBuilder
     */
    public function __construct(AggregateBuilderInterface $itemBuilder, Driver\CollectionInterface $collection)
    {
        $this->itemBuilder = $itemBuilder;
        $this->collection = $collection;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateBuilderInterface::buildInsertDocument()
     */
    public function buildInsertDocument($object, $property, $prefix, InvokableChain $actions)
    {
        if (!is_array($object) && !($object instanceof Traversable)) {
            throw new InvalidArgumentException('The collection to persist must be an arrayor traversable');
        }

        $propertyPath = $this->prefixPropertyPath($property, $prefix);
        $collection = [];
        $offset = 0;

        if ($this->indexed) {
            foreach ($object as $key => $item) {
                $collection[$key] = $this->buildInsertDocument($item, $key, $propertyPath, $actions);
            }
        } else {
            $offset = 0;

            foreach ($object as $item) {
                $collection[] = $this->buildInsertDocument($item, $offset, $propertyPath, $actions);
                $offset++;
            }
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateBuilderInterface::buildUndefinedInDocument()
     */
    public function buildUndefinedInDocument($property, $prefix, $stateValue, InvokableChain $actions)
    {
        if (!is_array($stateValue) && !($stateValue instanceof Traversable)) {
            return;
        }

        $propertyPath = $this->prefixPropertyPath($property, $prefix);

        foreach ($stateValue as $key => $itemStateValue) {
            $this->itemBuilder->buildUndefinedInDocument($key, $propertyPath, $itemStateValue, $actions);
        }
    }

    /**
     * @param PersistedCollection $collection
     * @param unknown $property
     * @param unknown $prefix
     * @param unknown $stateValue
     * @param InvokableChain $actions
     */
    protected function buildCollectionUpdateDocument(PersistedCollection $collection, $property, $prefix, &$stateValue, InvokableChain $actions)
    {
        if ($this->indexed) {
            throw new LogicException('Cannot build an indexed update from a non-indexable persisted collection');
        }

        $updateDocument = [];
        $newState = [];

        $propertyPath = $this->prefixPropertyPath($property, $prefix);
        $itemBuilder = $this->itemBuilder;

        $removed = $collection->getItemsToRemove();
        $added = $collection->getItemsToAdd();
        $persisted = $collection->getPersistedItems();
        $offset = 0;

        if ($removed->count()) {
            $newState = [];

            foreach ($removed as $item) {
                $fromOffset = $removed[$item];
                $state = (isset($stateValue[$fromOffset]))? $stateValue[$fromOffset] : null;
                $itemBuilder->buildUndefinedInDocument($fromOffset, $propertyPath, $state, $actions);
            }

            foreach ($collection as $item) {
                $newState[$offset] = $itemBuilder->buildInsertDocument($item, $offset, $propertyPath, $actions);

                if ($added->contains($item)) {
                    $added->offsetSet($item, $offset);
                }

                $offset++;
            }

            $stateValue = $newState;
            $updateDocument['$set'][$propertyPath] = $newState;

            return $updateDocument;
        }

        if (!is_array($stateValue)) {
            $stateValue = [];
        }

        foreach ($persisted as $item) {
            $offset = $persisted[$item];
            $state = isset($stateValue[$offset])? $stateValue[$offset] : null;

            $updates = $itemBuilder->buildUpdateDocument($item, $offset, $propertyPath, $state, $actions);
            $updateDocument = array_merge_recursive($updateDocument, $updates);
            $stateValue[$offset] = $state;
        }

        if ($added->count()) {
            $updateDocument['$pushAll'][$propertyPath] = [];

            foreach ($added as $item) {
                $offset = count($stateValue);
                $added->offsetSet($item, $offset);

                $add = $itemBuilder->buildInsertDocument($item, $offset, $propertyPath, $actions);
                $stateValue[] = $add;
                $updateDocument['$pushAll'][$propertyPath][] = $add;
            }
        }

        return $updateDocument;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateBuilderInterface::buildUpdateDocument()
     */
    public function buildUpdateDocument($collection, $property, $prefix, &$stateValue, InvokableChain $actions)
    {
        if (!is_array($collection) && !($collection instanceof Traversable)) {
            throw new InvalidArgumentException('The collection to persist must be an array or traversable');
        }

        if ($collection instanceof ImmutablePersistedCollection) {
            return [];
        }

        if ($collection instanceof PersistedCollection) {
            return $this->buildCollectionUpdateDocument($collection, $property, $prefix, $stateValue, $actions);
        }

        // Collection is not tracked (therefore not existent or entirely replaced)
        $propertyPath = $this->prefixPropertyPath($property, $prefix);
        $new = $this->buildInsertDocument($collection, $property, $prefix, $actions);
        $stateValue = $new;
        $updateDocument = [
            '$set' => [
                $propertyPath => $stateValue
            ]
        ];

        return $updateDocument;
    }
}
