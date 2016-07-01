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
use Rampage\Nexus\MongoDB\PersistenceBuilder\PersistenceBuilderInterface;
use Zend\Hydrator\HydratorInterface;


abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * @var UnitOfWork
     */
    protected $uow;

    /**
     * @var PersistenceBuilderInterface
     */
    protected $persistenceBuilder;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @param UnitOfWork $unitOfWork
     */
    public function __construct(PersistenceBuilderInterface $persistenceBuilder, UnitOfWork $unitOfWork = null)
    {
        $this->uow = $unitOfWork? : new UnitOfWork();
        $this->persistenceBuilder = $persistenceBuilder;
        $this->hydrator = $this->createHydrator();
    }

    /**
     * @param string $class
     * @return HydratorInterface
     */
    abstract protected function createHydrator();

    /**
     * @param string $class
     * @param array $data
     */
    abstract protected function mapIdentifier($class, $data);

    /**
     * @param unknown $class
     */
    abstract protected function newEntityInstance($class);

    /**
     * Returns the entity class
     */
    abstract protected function getEntityClass();

    /**
     * @param string $class
     * @param array $data
     */
    protected function getOrCreate($class, $data)
    {
        $id = $this->mapIdentifier($class, $data);

        if ($this->uow->hasInstanceByIdentifier($class, $id)) {
            return $this->uow->getInstanceByIdentifier($class, $id);
        }

        $object = $this->newEntityInstance($class);

        $this->hydrator->hydrate($data, $object);
        $this->uow->attach($object, new EntityState(EntityState::STATE_PERSISTED, $data, $id));

        return $object;
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
        $result = $this->persistenceBuilder->find([]);
        $hydrate = function($data) {};

        return new Cursor($result, $hydrate);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::findOne()
     */
    public function findOne($id)
    {
        // FIXME
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::persist()
     */
    public function persist($object)
    {
        // TODO Auto-generated method stub

    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::remove()
     */
    public function remove($object)
    {
        // TODO Auto-generated method stub

    }


}
