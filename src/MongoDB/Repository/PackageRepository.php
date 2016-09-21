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
use Rampage\Nexus\Repository\PackageRepositoryInterface;
use Rampage\Nexus\Repository\PackageRepositorySubscriberInterface;

use Rampage\Nexus\MongoDB\Driver\DriverInterface;
use Rampage\Nexus\MongoDB\Hydration\EntityHydrator\PackageHydrator;

use SplObjectStorage;

/**
 * The default package repo
 */
final class PackageRepository extends AbstractRepository implements PackageRepositoryInterface, ReferenceProviderInterface
{
    use IdReferenceProviderTrait;

    const COLLECTION_NAME = 'packages';

    /**
     * @var SplObjectStorage|PackageRepositorySubscriberInterface[]
     */
    private $subscribers;

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\AbstractRepository::__construct()
     */
    public function __construct(DriverInterface $driver)
    {
        $this->subscribers = new SplObjectStorage();
        parent::__construct($driver, new PackageHydrator($driver), self::COLLECTION_NAME);
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
        return new ApplicationPackage();
    }

    /**
     * @param ApplicationPackage $package
     * @return string
     */
    protected function getObjectId(ApplicationPackage $package)
    {
        return $package->getId();
    }

    /**
     * @param ApplicationPackage $package
     */
    public function save(ApplicationPackage $package)
    {
        $this->doPersist($package);

        foreach ($this->subscribers as $subscriber) {
            $subscriber->onPackagePersist($package);
        }
    }

    /**
     * @param ApplicationPackage $package
     */
    public function remove(ApplicationPackage $package)
    {
        $this->doRemove($package);

        foreach ($this->subscribers as $subscriber) {
            $subscriber->onPackageRemove($package);
        }
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\PackageRepositoryInterface::findByPackageName()
     */
    public function findByPackageName($packageName)
    {
        return $this->doFind(['name' => $packageName]);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\PackageRepositoryInterface::addSubscriber()
     */
    public function addSubscriber(PackageRepositorySubscriberInterface $subscriber)
    {
        $this->subscribers->attach($subscriber);
        return $this;
    }
}
