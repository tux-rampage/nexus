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

use Rampage\Nexus\Repository\PackageRepositoryInterface;
use Rampage\Nexus\Repository\PackageRepositorySubscriberInterface;
use Rampage\Nexus\Repository\ApplicationRepositoryInterface;

use Rampage\Nexus\Entities\Application;
use Rampage\Nexus\Package\PackageInterface;

use Rampage\Nexus\MongoDB\Driver\DriverInterface;
use Rampage\Nexus\MongoDB\Hydration\EntityHydrator\ApplicationHydrator;
use Rampage\Nexus\MongoDB\ImmutablePersistedCollection;

/**
 * Implements the application Repository
 */
final class ApplicationRepository extends AbstractRepository implements ApplicationRepositoryInterface, ReferenceProviderInterface, PackageRepositorySubscriberInterface
{
    use IdReferenceProviderTrait;

    /**
     * The collection name
     */
    const COLLECTION_NAME = 'applications';

    /**
     * @var PackageRepositoryInterface
     */
    private $packageRepository;

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\AbstractRepository::__construct()
     */
    public function __construct(DriverInterface $driver, PackageRepositoryInterface $packageRepository)
    {
        $this->packageRepository = $packageRepository;
        parent::__construct($driver, new ApplicationHydrator($packageRepository, $driver), self::COLLECTION_NAME);

        $packageRepository->addSubscriber($this);
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
    protected function newEntityInstance(array &$data)
    {
        return new Application();
    }

    /**
     * @param ApplicationPackage $package
     * @return string
     */
    protected function getObjectId(Application $application)
    {
        return $application->getId();
    }

    /**
     * Returns the tracked instance (if any)
     *
     * @param string $id
     * @return Application|null
     */
    private function getTrackedInstance($id)
    {
        if ($this->entityStates->hasInstanceByIdentifier($id)) {
            return $this->entityStates->getInstanceByIdentifier($id);
        }

        return null;
    }

    /**
     * @param Application $application
     */
    private function notifyPackageChanges(Application $application)
    {
        $collection = $application->getPackages();

        if ($collection instanceof ImmutablePersistedCollection) {
            $collection->reload();
        }
    }

    /**
     * Detect if a package is removed - Preform removal
     *
     * @param PackageInterface $package
     */
    public function onPackageRemove(PackageInterface $package)
    {
        $packageName = $package->getName();
        $application = $this->getTrackedInstance($packageName);
        $cursor = $this->packageRepository->findByPackageName($packageName);

        if ($application) {
            $this->notifyPackageChanges($application);
        }

        if ($cursor->count()) {
            return;
        }

        if ($application) {
            $this->doRemove($application);
        } else {
            $this->collection->remove(['_id' => $packageName]);
        }
    }

    /**
     * Notify persisting a package
     *
     * @param PackageInterface $package
     */
    public function onPackagePersist(PackageInterface $package)
    {
        $packageName = $package->getName();

        if ($this->collection->findOne(['_id' => $packageName])) {
            return;
        }

        $application = $this->getTrackedInstance($packageName)? : new Application();
        $this->hydrator->hydrate([
            '_id' => $package->getName(),
            'label' => $package->getName()
        ], $application);

        $this->doPersist($application);
        $this->notifyPackageChanges($application);
    }
}
