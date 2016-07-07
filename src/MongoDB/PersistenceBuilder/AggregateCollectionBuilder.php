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

use Rampage\Nexus\Exception\InvalidArgumentException;
use Rampage\Nexus\Exception\LogicException;

use Rampage\Nexus\MongoDB\Exception\NestedBuilderException;
use Rampage\Nexus\MongoDB\InvokableChain;
use Rampage\Nexus\MongoDB\ImmutablePersistedCollection;
use Rampage\Nexus\MongoDB\PersistedCollection;
use Rampage\Nexus\MongoDB\PersistedIndexableCollection;

use ArrayIterator;
use Traversable;
use Throwable;
use MultipleIterator;
use Rampage\Nexus\Entities\IndexableCollectionInterface;


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
     * @param AggregateBuilderInterface $itemBuilder
     */
    public function __construct(AggregateBuilderInterface $itemBuilder)
    {
        $this->itemBuilder = $itemBuilder;
    }

    /**
     * @param bool $flag
     * @return self
     */
    public function setIsIndexed($flag)
    {
        $this->indexed = (bool)$flag;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isIndexed()
    {
        return $this->indexed;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateBuilderInterface::buildInsertDocument()
     */
    public function buildInsertDocument($object, InvokableChain $actions)
    {
        if (!is_array($object) && !($object instanceof Traversable)) {
            throw new InvalidArgumentException('The collection to persist must be an arrayor traversable');
        }

        $itemBuilder = $this->itemBuilder;
        $collection = [];
        $offset = 0;

        foreach ($object as $key => $item) {
            try {
                if ($this->indexed) {
                    $collection[$key] = $itemBuilder->buildInsertDocument($item, $actions);
                } else {
                    $collection[] = $itemBuilder->buildInsertDocument($item, $actions);
                    $offset++;
                }
            } catch (Throwable $e) {
                throw new NestedBuilderException($e, $this->indexed? $key : $offset);
            }
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateBuilderInterface::buildUndefinedInDocument()
     */
    public function buildUndefinedInDocument($stateValue, InvokableChain $actions)
    {
        if (!is_array($stateValue) && !($stateValue instanceof Traversable)) {
            return;
        }

        $itemBuilder = $this->itemBuilder;

        foreach ($stateValue as $key => $itemStateValue) {
            try {
                $itemBuilder->buildUndefinedInDocument($itemStateValue, $actions);
            } catch (Throwable $e) {
                throw new NestedBuilderException($e, $key);
            }
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

                try {
                    $itemBuilder->buildUndefinedInDocument($state, $actions);
                } catch (Throwable $e) {
                    throw new NestedBuilderException($e, '$');
                }
            }

            foreach ($collection as $item) {
                try {
                    $newState[$offset] = $itemBuilder->buildInsertDocument($item, $actions);
                } catch (Throwable $e) {
                    throw new NestedBuilderException($e, $offset);
                }


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

            try {
                $updates = $itemBuilder->buildUpdateDocument($item, $offset, $propertyPath, $state, $actions);
                $updateDocument = array_merge_recursive($updateDocument, $updates);
                $stateValue[$offset] = $state;
            } catch (Throwable $e) {
                throw new NestedBuilderException($e, $offset);
            }
        }

        if ($added->count()) {
            $updateDocument['$pushAll'][$propertyPath] = [];

            foreach ($added as $item) {
                $offset = count($stateValue);
                $added->offsetSet($item, $offset);

                try {
                    $add = $itemBuilder->buildInsertDocument($item, $actions);
                    $stateValue[] = $add;
                    $updateDocument['$pushAll'][$propertyPath][] = $add;
                } catch (Throwable $e) {
                    throw new NestedBuilderException($e, '[]');
                }
            }
        }

        return $updateDocument;
    }

    /**
     * @param PersistedIndexableCollection $collection
     * @param unknown $property
     * @param unknown $prefix
     * @param unknown $stateValue
     * @param InvokableChain $actions
     * @throws NestedBuilderException
     */
    protected function buildIndexedCollectionUpdateDocument(PersistedIndexableCollection $collection, $property, $prefix, &$stateValue, InvokableChain $actions)
    {
        if (!$this->indexed) {
            throw new LogicException('Cannot build non-indexed persistence for indexed collections');
        }

        $append = $collection->getItemsToAdd();
        $itemBuilder = $this->itemBuilder;
        $updateDocument = [];
        $propertyPath = $this->prefixPropertyPath($property, $prefix);

        if ($append->count()) {
            throw new NestedBuilderException('Persisting indexed collections with appended items is not supported');
        }

        if (!is_array($stateValue)) {
            $stateValue = [];
        }

        foreach ($collection->getRemovedOffsets() as $offset) {
            if (!isset($stateValue[$offset])) {
                continue;
            }

            try {
                $itemBuilder->buildUndefinedInDocument($stateValue[$offset], $actions);
                unset($stateValue[$offset]);
            } catch (Throwable $e) {
                throw new NestedBuilderException($e, $offset);
            }
        }

        $stateKeys = array_keys($stateValue);
        $stateKeys = array_combine($stateKeys, $stateKeys);

        $populate = new MultipleIterator(MultipleIterator::MIT_NEED_ANY | MultipleIterator::MIT_KEYS_ASSOC);
        $populate->attachIterator(new ArrayIterator($collection->getModifiedOffsets()));
        $populate->attachIterator(new ArrayIterator($collection->getAddedOffsets()));

        foreach ($populate as $offset) {
            unset($stateKeys[$offset]);
            $setPath = $this->prefixPropertyPath($offset, $propertyPath);

            try {
                $document = $itemBuilder->buildInsertDocument($collection[$offset], $actions);
                $stateKeys[$offset] = $document;
                $updateDocument['$set'][$setPath] = $document;
            } catch (Throwable $e) {
                throw new NestedBuilderException($e, $offset);
            }
        }

        foreach ($stateKeys as $offset) {
            try {
                $updates = $itemBuilder->buildUpdateDocument($collection[$offset], $offset, $propertyPath, $stateValue[$offset], $actions);
                $updateDocument = array_merge_recursive($updateDocument, $updates);
            } catch (Throwable $e) {
                throw new NestedBuilderException($e, $offset);
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

        if ($collection instanceof IndexableCollectionInterface) {
            return $this->buildIndexedCollectionUpdateDocument($collection, $property, $prefix, $stateValue, $actions);
        } else if ($collection instanceof PersistedCollection) {
            return $this->buildCollectionUpdateDocument($collection, $property, $prefix, $stateValue, $actions);
        }

        // Collection is not tracked (therefore not existent or entirely replaced)
        $propertyPath = $this->prefixPropertyPath($property, $prefix);
        $new = $this->buildInsertDocument($collection, $actions);
        $stateValue = $new;
        $updateDocument = [
            '$set' => [
                $propertyPath => $stateValue
            ]
        ];

        return $updateDocument;
    }
}
