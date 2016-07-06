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

use Rampage\Nexus\Entities\ApplicationPackage;
use Rampage\Nexus\Entities\ApplicationInstance;
use Rampage\Nexus\Repository\PackageRepositoryInterface;
use Rampage\Nexus\MongoDB\AbstractRepository;
use Rampage\Nexus\MongoDB\Driver\DriverInterface;
use Rampage\Nexus\MongoDB\Driver\CollectionInterface as MongoCollectionInterface;
use Rampage\Nexus\MongoDB\UnitOfWork;
use Rampage\Nexus\MongoDB\PersistenceBuilder\DefaultPersistenceBuilder;
use Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateCollectionBuilder;
use Rampage\Nexus\MongoDB\PersistenceBuilder\EmbeddedBuilder;

/**
 * The default package repo
 */
class PackageRepository extends AbstractRepository implements PackageRepositoryInterface
{
    /**
     * @var MongoCollectionInterface
     */
    private $collection;

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\AbstractRepository::createHydrator()
     */
    protected function createHydrator()
    {
        // TODO Auto-generated method stub

    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\AbstractRepository::createPersistenceBuilder()
     */
    protected function createPersistenceBuilder()
    {
        // TODO Auto-generated method stub
        $builder = new DefaultPersistenceBuilder(ApplicationPackage::class, $this->uow, $this->hydrator, $this->getMongoCollection());
        $builder->setAggregatedProperty('parameters', new AggregateCollectionBuilder(new EmbeddedBuilder($this->uow, $paramHydrator)));

        return $builder;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\AbstractRepository::getEntityClass()
     */
    protected function getEntityClass()
    {
        return ApplicationPackage::class;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\AbstractRepository::getIdentifierStrategy()
     */
    protected function getIdentifierStrategy()
    {
        return $this->driver->getTypeHydrationStrategy('string');
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\AbstractRepository::getMongoCollection()
     */
    protected function getMongoCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->driver->getCollection('packages');
        }

        return $this->collection;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\AbstractRepository::newEntityInstance()
     */
    protected function newEntityInstance($class, $data)
    {
        return new ApplicationPackage();
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\PackageRepositoryInterface::findApplication()
     */
    public function findApplication($packageName)
    {
        // TODO Auto-generated method stub

    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\PackageRepositoryInterface::findByPackageName()
     */
    public function findByPackageName($packageName)
    {
        $data = $this->getMongoCollection()->findOne([ 'name' => $packageName ]);
        return $data? $this->getOrCreate(ApplicationInstance::class, $data) : null;
    }
}
