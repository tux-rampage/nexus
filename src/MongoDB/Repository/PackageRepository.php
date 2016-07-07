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
use Rampage\Nexus\Entities\Application;
use Rampage\Nexus\Entities\PackageParameter;

use Rampage\Nexus\Repository\PackageRepositoryInterface;

use Rampage\Nexus\MongoDB\AbstractRepository;
use Rampage\Nexus\MongoDB\UnitOfWork;
use Rampage\Nexus\MongoDB\ImmutablePersistedCollection;

use Rampage\Nexus\MongoDB\Driver\DriverInterface;
use Rampage\Nexus\MongoDB\Driver\CollectionInterface as MongoCollectionInterface;

use Rampage\Nexus\MongoDB\PersistenceBuilder\DefaultPersistenceBuilder;
use Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateCollectionBuilder;
use Rampage\Nexus\MongoDB\PersistenceBuilder\EmbeddedBuilder;
use Rampage\Nexus\MongoDB\PersistenceBuilder\PersistenceBuilderInterface;

use Rampage\Nexus\MongoDB\Hydration\ReflectionHydrator;
use Rampage\Nexus\MongoDB\Hydration\CollectionStrategy;
use Rampage\Nexus\MongoDB\Hydration\EmbeddedStrategy;

use Zend\Hydrator\HydratorInterface;
use Rampage\Nexus\Exception\LogicException;

/**
 * The default package repo
 */
class PackageRepository extends AbstractRepository implements PackageRepositoryInterface
{
    /**
     * @var MongoCollectionInterface
     */
    private $collections = [
        ApplicationPackage::class => 'packages',
        Application::class => 'applications',
    ];

    /**
     * @var HydratorInterface
     */
    private $paramHydrator = null;

    /**
     * @var HydratorInterface
     */
    private $groupHydrator;

    /**
     * @var PersistenceBuilderInterface
     */
    private $groupPersistenceBuilder;

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\AbstractRepository::__construct()
     */
    public function __construct(DriverInterface $driver, UnitOfWork $unitOfWork = null)
    {
        parent::__construct($driver, $unitOfWork);
        $this->groupCollection = $driver->getCollection('applications');
        $this->groupHydrator = $this->createGroupHydrator();
        $this->groupPersistenceBuilder = $this->createGroupBuilder();
    }

    /**
     * @return HydratorInterface
     */
    protected function getParamHydrator()
    {
        if (!$this->paramHydrator) {
            $stringStrategy = $this->driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_STRING);
            $hydrator = new ReflectionHydrator();
            $hydrator->addStrategy('name', $stringStrategy);
            $hydrator->addStrategy('label', $stringStrategy);
            $hydrator->addStrategy('type', $stringStrategy);
        }

        return $this->paramHydrator;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\AbstractRepository::createHydrator()
     */
    protected function createHydrator()
    {
        $paramHydrator = $this->getParamHydrator();
        $hydrator = new ReflectionHydrator([
            'id' => '_id',
            'archive',
            'name',
            'version',
            'type',
            'documentRoot',
            'parameters',
            'extra'
        ]);

        $hydrator->addStrategy('id', $this->getIdentifierStrategy());
        $hydrator->addStrategy('extra', $this->driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_DYNAMIC));
        $hydrator->addStrategy('*', $this->driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_STRING));
        $hydrator->addStrategy('parameters', new CollectionStrategy(new EmbeddedStrategy(new PackageParameter(), $paramHydrator), true, PackageParameter::class));

        return $hydrator;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\AbstractRepository::createPersistenceBuilder()
     */
    protected function createPersistenceBuilder()
    {
        $builder = new DefaultPersistenceBuilder(ApplicationPackage::class, $this->uow, $this->hydrator, $this->getMongoCollection());
        $paramsBuilder = new AggregateCollectionBuilder(new EmbeddedBuilder($this->uow, $this->getParamHydrator()));

        $paramsBuilder->setIsIndexed(true);
        $builder->setAggregatedProperty('parameters', $paramsBuilder);

        return $builder;
    }

    /**
     * @return \Rampage\Nexus\MongoDB\Hydration\ReflectionHydrator
     */
    protected function createGroupHydrator()
    {
        $hydrator = new ReflectionHydrator([
            'id' => '_id',
            'label',
            'icon',
            'packages'
        ]);

        $hydrator->addStrategy('id', $this->driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_STRING));
        $hydrator->addHydrationInterceptor(function(&$data) {
            $id = $data['_id'];
            $data['packages'] = new ImmutablePersistedCollection(function() use ($id) {
                return $this->findByPackageName($id);
            });
        });

        return $hydrator;
    }

    /**
     * @return PersistenceBuilderInterface
     */
    protected function createGroupBuilder()
    {
        $builder = new DefaultPersistenceBuilder(Application::class, $this->uow, $this->groupHydrator, $this->getMongoCollection(Application::class));
        $builder->addMappedRefProperty('packages');

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
        return $this->driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_STRING);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\AbstractRepository::newEntityInstance()
     */
    protected function newEntityInstance($class, $data)
    {
        if ($class == Application::class) {
            return new Application();
        }

        if ($class != ApplicationPackage::class) {
            throw new LogicException('Unsupported entity class: ' . $class);
        }

        return new ApplicationPackage();
    }

    /**
     * @param ApplicationPackage $package
     */
    public function persist(ApplicationPackage $package)
    {
        $this->doPersist($package);

        if (!$this->findApplication($package->getName())) {
            $app = new Application();
            $this->groupHydrator->hydrate([
                '_id' => $package->getName(),
                'label' => $package->getName()
            ], $app);

            $this->doPersist($app, $this->groupPersistenceBuilder, Application::class);
        }
    }

    /**
     * @param ApplicationPackage $package
     */
    private function checkApplicationRemoval(ApplicationPackage $package)
    {
        $cursor = $this->getMongoCollection()->find(['name' => $package->getName()]);
        if ($cursor->count()) {
            return;
        }

        $app = $this->findApplication($package->getName());
        $this->doRemove($app, $this->groupPersistenceBuilder, Application::class);
    }

    /**
     * @param ApplicationPackage $package
     */
    public function remove(ApplicationPackage $package)
    {
        $this->doRemove($package);
        $this->checkApplicationRemoval($package);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\PackageRepositoryInterface::findApplication()
     */
    public function findApplication($packageName)
    {
        return $this->doFindOne(Application::class, ['id' => $packageName]);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\PackageRepositoryInterface::findByPackageName()
     */
    public function findByPackageName($packageName)
    {
        return $this->doFind(ApplicationPackage::class, ['name' => $packageName]);
    }
}
