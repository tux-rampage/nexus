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

use Rampage\Nexus\MongoDB\UnitOfWork;
use Rampage\Nexus\MongoDB\Driver;
use Zend\Hydrator\HydratorInterface;
use Rampage\Nexus\MongoDB\EntityState;
use Rampage\Nexus\MongoDB\InvokableChain;

/**
 * The default persistence builder
 */
class DefaultPersistenceBuilder implements PersistenceBuilderInterface
{
    use PersistenceBuilderTrait;

    /**
     * @param UnitOfWork $unitOfWork
     * @param HydratorInterface $hydrator
     */
    public function __construct(UnitOfWork $unitOfWork, HydratorInterface $hydrator, Driver\CollectionInterface $collection)
    {
        $this->hydrator = $hydrator;
        $this->unitOfWork = $unitOfWork;
        $this->collection = $collection;
    }

    /**
     * @param object $object
     * @param array $callbacks
     */
    protected function buildInsertDocument($object, array $document, array &$callbacks)
    {
        foreach ($this->mappedRefProperties as $property) {
            unset($document[$property]);
        }

        foreach ($this->aggregationProperties as $property => $persister) {
            if (!isset($document[$property])) {
                continue;
            }

            $aggregatedObject = $document[$property];
            unset($document[$property]);
            $callback = $persister->buildInsertDocument($aggregatedObject, $document, $property, null, $document);

            if ($callback) {
                $callbacks[] = $callback;
            }
        }

        return $document;
    }

    /**
     * @param object $object
     * @param array $callbacks
     */
    protected function buildUpdateDocument($object, array $extractedData, EntityState $state, array &$callbacks)
    {
        $stateData = $state->getData();
        $document = null;
        $newStateData = [];

        if (!is_array($stateData)) {
            $stateData = [];
        }

        foreach ($extractedData as $property => $value) {
            if ($this->isExcludedFromDiff($property)) {
                continue;
            }

            $newStateData[$property] = $value;

            // Not changed?
            if (array_key_exists($property, $stateData) && ($stateData[$property] == $value)) {
                continue;
            }

            $document['$set'][$property] = $value;
        }

        foreach ($this->aggregationProperties as $property => $persister) {
            if (!isset($extractedData[$property])) {
                $callback = $persister->buildUndefinedInDocument($property, null, $document, $state);
            } else {
                $aggregatedObject = $extractedData[$property];
                $newStateData[$property] = isset($stateData[$property])? $stateData[$property] : null;
                $callback = $persister->buildUpdateDocument($aggregatedObject, $newStateData, $property, null, $document, $state);
            }

            if ($callback) {
                $callbacks[] = $callback;
            }
        }

        return $document;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\PersistenceBuilderInterface::buildPersist()
     */
    public function buildPersist($object, EntityState &$state)
    {
        $callbacks = [];
        $data = $this->hydrator->extract($object);
        $id = isset($data['_id'])? $data['_id'] : null;

        if ($this->discriminatorField) {
            $data[$this->discriminatorField] = $this->mapTypeToDiscriminator($object);
        }

        if (!$id || ($state->getState() != EntityState::STATE_PERSISTED)) {
            $document = $this->buildInsertDocument($object, $data, $callbacks);
            $upsert = true;

            if (!$id) {
                $upsert = false;
                $document['_id'] = $this->createIdentity();
            }

            $state = new EntityState(EntityState::STATE_PERSISTED, $document, $document['_id']);
            return (new InvokableChain($callbacks))->prepend(function() use ($document, $upsert) {
                $this->collection->insert($document, $upsert);
            });
        }

        $document = $this->buildUpdateDocument($object, $data, $state, $callbacks);
        $state = new EntityState(EntityState::STATE_PERSISTED, $document, $document['_id']);

        return (new InvokableChain($callbacks))->prepend(function() use ($document, $id) {
            $this->collection->update(['_id' => $id], $document);
        });
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\PersistenceBuilderInterface::buildRemove()
     */
    public function buildRemove($object)
    {
        $data = $this->hydrator->extract($object);

        if (!isset($data['_id'])) {
            return new InvokableChain();
        }

        $id = $data['_id'];

        return function() use ($id) {
            $this->collection->remove(['_id' => $id]);
        };
    }
}
