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
use Rampage\Nexus\MongoDB\EntityState;
use Rampage\Nexus\MongoDB\InvokableChain;
use Rampage\Nexus\MongoDB\Exception\PersistenceBuilderException;

use Zend\Hydrator\HydratorInterface;
use Throwable;

/**
 * The default persistence builder
 */
class DefaultPersistenceBuilder implements PersistenceBuilderInterface
{
    use PersistenceBuilderTrait;

    /**
     * The class this builder is responsible for
     * @var string
     */
    protected $class;

    /**
     * @param string                        $class
     * @param UnitOfWork                    $unitOfWork
     * @param HydratorInterface             $hydrator
     * @param Driver\CollectionInterface    $collection
     */
    public function __construct($class, UnitOfWork $unitOfWork, HydratorInterface $hydrator, Driver\CollectionInterface $collection)
    {
        $this->hydrator = $hydrator;
        $this->unitOfWork = $unitOfWork;
        $this->collection = $collection;
        $this->class = $class;
    }

    /**
     * @param unknown $object
     * @param array $document
     * @param InvokableChain $actions
     * @throws PersistenceBuilderException
     * @return array
     */
    protected function buildInsertDocument($object, array $document, InvokableChain $actions)
    {
        foreach ($this->mappedRefProperties as $property) {
            unset($document[$property]);
        }

        foreach ($this->aggregationProperties as $property => $persister) {
            if (!isset($document[$property])) {
                continue;
            }

            try {
                $aggregatedObject = $document[$property];
                $document[$property] = $persister->buildInsertDocument($aggregatedObject, $actions);
            } catch (Throwable $e) {
                throw new PersistenceBuilderException($this->class, $e, $property);
            }
        }

        return $document;
    }

    /**
     * @param object $object
     * @param array $actions
     *
     */
    protected function buildUpdateDocument($object, array $extractedData, EntityState &$state, InvokableChain $actions)
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
            try {
                $stateValue = (isset($stateData[$property]))? $stateData[$property] : null;

                if (!isset($extractedData[$property])) {
                    $persister->buildUndefinedInDocument($stateValue, $actions);
                } else {
                    $aggregatedObject = $extractedData[$property];
                    $updates = $persister->buildUpdateDocument($aggregatedObject, $property, null, $stateValue, $actions);

                    $document = array_merge_recursive($document, $updates);
                    $newStateData[$property] = $stateValue;
                }
            } catch (Throwable $e) {
                throw new PersistenceBuilderException($this->class, $e, $property);
            }
        }

        $state = new EntityState(EntityState::STATE_PERSISTED, $newStateData, $extractedData['_id']);

        return $document;
    }

    /**
     * Creates the update callbacks in the invocation chain
     *
     * @param   mixed           $id         The document identifier
     * @param   array           $document   The update document
     * @param   InvokableChain  $actions    The action chain to add the actions to
     */
    private function createUpdateActions($id, array $document, InvokableChain $actions)
    {
        $actions->prepend(function() use ($id, $document) {
            $this->collection->update(['_id' => $id], $document);
        });
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\PersistenceBuilderInterface::getClass()
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\PersistenceBuilderInterface::buildPersist()
     */
    public function buildPersist($object, EntityState &$state)
    {
        $actions = new InvokableChain();
        $data = $this->hydrator->extract($object);
        $id = isset($data['_id'])? $data['_id'] : null;

        if ($this->discriminatorField) {
            $data[$this->discriminatorField] = $this->mapTypeToDiscriminator($object);
        }

        if (!$id || ($state->getState() != EntityState::STATE_PERSISTED)) {
            $document = $this->buildInsertDocument($object, $data, $actions);
            $upsert = true;

            if (!$id) {
                $upsert = false;
                $document['_id'] = $this->createIdentity();
            }

            $state = new EntityState(EntityState::STATE_PERSISTED, $document, $document['_id']);
            $actions->prepend(function() use ($document, $upsert) {
                $this->collection->insert($document, $upsert);
            });
        } else {
            $document = $this->buildUpdateDocument($object, $data, $state, $actions);
            $this->createUpdateActions($id, $document, $actions);
        }

        return $actions;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\PersistenceBuilderInterface::buildRemove()
     */
    public function buildRemove($object, EntityState &$state)
    {
        $id = $state->getId();
        $actions = new InvokableChain();
        $stateData = $state->getData();

        if (!$id || ($state->getState() == EntityState::STATE_REMOVED)) {
            return $actions;
        }

        foreach ($this->aggregationProperties as $property => $persister) {
            if (!isset($stateData[$property])) {
                continue;
            }

            try {
                $persister->buildUndefinedInDocument($stateData[$property], $actions);
            } catch (Throwable $e) {
                throw new PersistenceBuilderException($this->class, $e, $property);
            }
        }

        return $actions;
    }
}
