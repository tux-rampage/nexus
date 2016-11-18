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

namespace Rampage\Nexus\ODM\Repository;

use Rampage\Nexus\Entities\ApplicationPackage;

use Rampage\Nexus\Repository\PackageRepositoryInterface;
use Rampage\Nexus\Repository\Package\SubscriberInterface as PackageRepositorySubscriberInterface;
use Rampage\Nexus\Repository\Package\SubscriberAggregate;

use Doctrine\Common\Persistence\ObjectManager;


/**
 * Package repository
 */
class PackageRepository extends AbstractRepository implements PackageRepositoryInterface
{
    /**
     * @var SubscriberAggregate
     */
    private $subscribers;

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\ODM\Repository\AbstractRepository::__construct()
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->subscribers = new SubscriberAggregate();
        parent::__construct($objectManager);
    }

    /**
     * @return \Rampage\Nexus\Repository\Package\SubscriberInterface
     */
    public function getSubscribers()
    {
        return $this->subscribers;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\ODM\Repository\AbstractRepository::getEntityClass()
     */
    protected function getEntityClass()
    {
        return ApplicationPackage::class;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\PackageRepository::addSubscriber()
     */
    public function addSubscriber(PackageRepositorySubscriberInterface $subscriber)
    {
        $this->subscribers->add($subscriber);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\PackageRepositoryInterface::findByPackageName()
     */
    public function findByPackageName($packageName)
    {
        return $this->getEntityRepository()->findBy(['name' => $packageName]);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\PackageRepositoryInterface::remove()
     */
    public function remove(ApplicationPackage $package)
    {
        $this->removeAndFlush($package);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\PackageRepositoryInterface::save()
     */
    public function save(ApplicationPackage $package)
    {
        $this->persistAndFlush($package);
    }
}
