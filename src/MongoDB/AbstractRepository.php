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

use Rampage\Nexus\Repository\RepositoryInterface;
use Zend\Hydrator\HydratorInterface;
use Rampage\Nexus\MongoDB\Driver\DriverInterface;
use Rampage\Nexus\Exception\InvalidArgumentException;
use Rampage\Nexus\MongoDB\PersistenceBuilder\PersistenceBuilderInterface;


abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * @var UnitOfWork
     */
    protected $uow;

    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @var PersistenceBuilderInterface
     */
    protected $persistenceBuilder;

    /**
     * @param UnitOfWork $unitOfWork
     */
    public function __construct(DriverInterface $driver, UnitOfWork $unitOfWork = null)
    {
        $this->uow = $unitOfWork? : new UnitOfWork();
        $this->driver = $driver;
        $this->hydrator = $this->createHydrator();
        $this->persistenceBuilder = $this->createPersistenceBuilder();
    }

    /**
     * @param string $class
     * @return HydratorInterface
     */
    abstract protected function createHydrator();

    /**
     * @return PersistenceBuilderInterface
     */
    abstract protected function createPersistenceBuilder();

    /**
     * @param unknown $class
     */
    abstract protected function newEntityInstance($class, $data);

    /**
     * Returns the entity class
     */
    abstract protected function getEntityClass();

    /**
     * @return \Zend\Hydrator\Strategy\StrategyInterface
     */
    abstract protected function getIdentifierStrategy();

    /**
     * Returns the underlying mongo collection
     *
     * @return Driver\CollectionInterface
     */
    abstract protected function getMongoCollection();

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
     * @param string $class
     * @param array $data
     * @return object
     */
    protected function getOrCreate($class, array $data)
    {
        $id = $this->extractIdentifier($data);
        $state = new EntityState(EntityState::STATE_PERSISTED, $data, $id);

        return $this->uow->getOrCreate($class, $state, function() use ($class, $data) {
            $entity = $this->newEntityInstance($class, $data);
            $this->hydrator->hydrate($data, $entity);

            return $entity;
        });
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::clear()
     */
    public function clear()
    {
        $this->uow->clear();
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::detatch()
     */
    public function detatch($object)
    {
        $this->uow->detatch($object);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::findAll()
     */
    public function findAll()
    {
        $result = $this->driver->find([]);
        $hydrate = function($data) {
            return $this->getOrCreate($this->getEntityClass(), $data);
        };

        return new Cursor($result, $hydrate);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::findOne()
     */
    public function findOne($id)
    {
        $mongoId = $this->getIdentifierStrategy()->extract($id);
        $result = new \IteratorIterator($this->getMongoCollection()->find(['_id' => $mongoId], null, 1));

        $result->rewind();

        if (!$result->valid()) {
            return null;
        }

        return $this->getOrCreate($this->getEntityClass(), $result->current());
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::persist()
     */
    protected function doPersist($object, $asClass = null)
    {
        $isAttached = $this->uow->isAttached($object);

        if ($isAttached) {
            $state = $this->uow->getState($object);
        } else {
            $state = new EntityState(EntityState::STATE_NEW, null);
            $this->uow->attach($object, $state, $asClass);
        }

        $persist = $this->persistenceBuilder->buildPersist($object, $state);
        $persist();

        $this->uow->updateState($object, new EntityState(EntityState::STATE_REMOVED, null, $state->getId()), $asClass);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::remove()
     */
    protected function doRemove($object, $asClass = null)
    {
        if (!$this->uow->isAttached($object)) {
            throw new InvalidArgumentException(sprintf('Cannot remove an unattached entity'));
        }

        $state = $this->uow->getState($object);
        if ($state->getState() == EntityState::STATE_REMOVED) {
            return;
        }

        $remove = $this->persistenceBuilder->buildRemove($object);
        $remove();

        $this->uow->updateState($object, new EntityState(EntityState::STATE_REMOVED, null, $state->getId()), $asClass);
    }
}
