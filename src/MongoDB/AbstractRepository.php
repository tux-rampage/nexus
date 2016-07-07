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
use Rampage\Nexus\Exception\LogicException;


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
     * @var CollectionMapping
     */
    protected $collections = [];

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
    protected function getMongoCollection($class = null)
    {
        $class = $class? : $this->getEntityClass();

        if (!$this->collections[$class]) {
            throw new LogicException('This class has no collection: ' . $class);
        }

        if (is_string($this->collections[$class])) {
            $this->collections[$class] = $this->driver->getCollection($this->collections[$class]);
        }

        return $this->collections[$class];
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
     * @param unknown $class
     * @param array $query
     * @param unknown $limit
     * @param unknown $skip
     * @param unknown $order
     * @return Cursor
     */
    protected function doFind($class, array $query, $limit = null, $skip = null, array $order = null)
    {
        $result = $this->getMongoCollection($class)->find($query, [], $limit, $skip, $order);
        $hydrate = function($data) use ($class) {
            return $this->getOrCreate($class, $data);
        };

        return new Cursor($result, $hydrate);
    }

    /**
     * @param unknown $class
     * @param array $query
     * @param unknown $limit
     * @param unknown $skip
     * @param unknown $order
     * @return Cursor
     */
    protected function doFindOne($class, array $query)
    {
        $result = $this->getMongoCollection($class)->findOne($query, []);

        if (!is_array($result)) {
            return null;
        }

        return $this->getOrCreate($class, $result);
    }


    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::findAll()
     */
    public function findAll()
    {
        return $this->doFind($this->getEntityClass(), []);
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
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::persist()
     */
    protected function doPersist($object, PersistenceBuilderInterface $builder = null, $asClass = null)
    {
        $isAttached = $this->uow->isAttached($object);

        if (!$builder) {
            $builder = $this->persistenceBuilder;
        }

        if ($isAttached) {
            $state = $this->uow->getState($object);
        } else {
            $state = new EntityState(EntityState::STATE_NEW, null);
            $this->uow->attach($object, $state, $asClass);
        }

        $persist = $builder->buildPersist($object, $state);
        $persist();

        $this->uow->updateState($object, $state, $asClass);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::remove()
     */
    protected function doRemove($object, PersistenceBuilderInterface $builder = null, $asClass = null)
    {
        if (!$builder) {
            $builder = $this->persistenceBuilder;
        }

        if (!$this->uow->isAttached($object)) {
            throw new InvalidArgumentException(sprintf('Cannot remove an unattached entity'));
        }

        $state = $this->uow->getState($object);
        if ($state->getState() == EntityState::STATE_REMOVED) {
            return;
        }

        $remove = $builder->buildRemove($object, $state);
        $remove();

        $this->uow->updateState($object, $state, $asClass);
    }
}
