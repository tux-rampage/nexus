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

namespace Rampage\Nexus\MongoDB\Repository;

use Rampage\Nexus\Repository\RepositoryInterface;
use Rampage\Nexus\MongoDB\Driver\DriverInterface;
use Rampage\Nexus\MongoDB\Driver\CollectionInterface;
use Rampage\Nexus\Exception\InvalidArgumentException;

use Zend\Hydrator\HydratorInterface;
use Rampage\Nexus\MongoDB\EntityStateContainer;
use Rampage\Nexus\MongoDB\EntityState;
use Rampage\Nexus\MongoDB\Cursor;
use Zend\Hydrator\Strategy\StrategyInterface;
use Rampage\Nexus\MongoDB\CalculateUpdateStrategy;


/**
 * Abstract Reposiotry impplementation
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * @var EntityStateContainer
     */
    protected $entityStates;

    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @var CollectionInterface
     */
    protected $collection;

    /**
     * @var StrategyInterface
     */
    protected $idStrategy;

    /**
     * @param UnitOfWork $unitOfWork
     */
    public function __construct(DriverInterface $driver, HydratorInterface $hydrator, $collectionName)
    {
        $this->entityStates = new EntityStateContainer();
        $this->driver = $driver;
        $this->hydrator = $hydrator;
        $this->collection = $driver->getCollection($collectionName);
        $this->idStrategy = $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_ID);
    }

    /**
     * Creates a new entity matching the instance data
     *
     * @param array $data
     * @return object
     */
    abstract protected function newEntityInstance(array &$data);

    /**
     * @return \Zend\Hydrator\Strategy\StrategyInterface
     */
    protected function getIdentifierStrategy()
    {
        return $this->idStrategy;
    }

    /**
     * @param array $data
     * @return mixed
     */
    protected function extractIdentifier(array $data)
    {
        if (!isset($data['_id'])) {
            return null;
        }

        return $this->getIdentifierStrategy()->hydrate($data['_id']);
    }

    /**
     * Returns the existing entity or creates a new one
     *
     * @param array $data
     * @return object
     */
    protected function getOrCreate(array $data)
    {
        $id = $this->extractIdentifier($data);
        $state = new EntityState(EntityState::STATE_PERSISTED, $data, $id);

        return $this->entityStates->getOrCreate($state, function() use ($data, $state) {
            $entity = $this->newEntityInstance($data);
            $this->entityStates->attach($entity, $state);
            $this->hydrator->hydrate($data, $entity);

            return $entity;
        });
    }

    /**
     * @param unknown $class
     * @param array $query
     * @param unknown $limit
     * @param unknown $skip
     * @param unknown $order
     * @return Cursor
     */
    protected function doFindOne(array $query)
    {
        $result = $this->collection->findOne($query);

        if (!is_array($result)) {
            return null;
        }

        return $this->getOrCreate($result);
    }

    /**
     * Find items
     *
     * @param array $query
     * @param unknown $limit
     * @param unknown $skip
     * @param array $order
     * @return \Rampage\Nexus\MongoDB\Cursor
     */
    protected function doFind(array $query, $limit = null, $skip = null, array $order = null)
    {
        $result = $this->collection->find($query, null, $limit, $skip, $order);
        $hydrate = function($data) {
            return $this->getOrCreate($data);
        };

        return new Cursor($result, $hydrate);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::findAll()
     */
    public function findAll()
    {
        return $this->doFind([]);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::findOne()
     */
    public function findOne($id)
    {
        $mongoId = $this->getIdentifierStrategy()->extract($id);
        return $this->doFindOne($this->getEntityClass(), ['_id' => $mongoId]);
    }

    /**
     * @param object $object
     * @param array $data
     */
    protected function prePersist($object, array &$data)
    {
    }

    /**
     * @param object $object
     * @param array $data
     */
    protected function postPersist($object, array &$data)
    {
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::persist()
     */
    protected function doPersist($object)
    {
        $isAttached = $this->entityStates->isAttached($object);
        $data = $this->hydrator->extract($object);
        $id = $this->extractIdentifier($data, $object);

        if ($isAttached) {
            $state = $this->entityStates->getState($object);
        } else {
            $state = new EntityState(EntityState::STATE_NEW, null, $id);
            $this->entityStates->attach($object, $state);
        }

        $this->prePersist($object, $data);

        if (!$id) {
            $id = $this->collection->insert($data);
            $this->hydrator->hydrate(['_id' => $id], $object);
        } else {
            $previousData = $state->getData();

            if (is_array($previousData)) {
                $updateId = $state->getId();
                $updates = new CalculateUpdateStrategy($previousData);
                $updates->calculate($data);

                foreach ($updates->getOrderedInstructions() as $update) {
                    $this->collection->update(['_id' => $updateId], $update);
                    $updateId = $id; // The first update may have changed the identifier
                }
            } else {
                $this->collection->update(['_id' => $state->getId()], [ '$set' => $data ], false, ($state->getState() != EntityState::STATE_PERSISTED));
            }
        }

        $this->entityStates->updateState($object, new EntityState(EntityState::STATE_PERSISTED, $data, $id));
        $this->postPersist($object, $data);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::remove()
     */
    protected function doRemove($object)
    {
        if (!$this->entityStates->isAttached($object)) {
            throw new InvalidArgumentException(sprintf('Cannot remove an unattached entity'));
        }

        $state = $this->entityStates->getState($object);

        if ($state->getState() == EntityState::STATE_REMOVED) {
            return;
        }

        $this->collection->remove(['_id' => $state->getId()]);
        $this->entityStates->updateState($object, new EntityState(EntityState::STATE_REMOVED, $state->getData(), $state->getId()));
    }
}
