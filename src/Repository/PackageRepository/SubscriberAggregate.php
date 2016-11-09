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

namespace Rampage\Nexus\Repository\Package;

use Rampage\Nexus\Package\PackageInterface;
use SplObjectStorage;


/**
 * Aggregator for package repo subscribers
 */
final class SubscriberAggregate implements SubscriberInterface
{
    /**
     * @var SplObjectStorage|SubscriberInterface[]
     */
    private $subscribers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->subscribers = new SplObjectStorage();
    }

    /**
     * @param SubscriberInterface $subscriber
     * @return \Rampage\Nexus\Repository\Package\SubscriberAggregate
     */
    public function add(SubscriberInterface $subscriber)
    {
        $this->subscribers->attach($subscriber);
        return $this;
    }

    /**
     *  @param SubscriberInterface $subscriber
     * @return \Rampage\Nexus\Repository\Package\SubscriberAggregate
     */
    public function remove(SubscriberInterface $subscriber)
    {
        $this->subscribers->detach($subscriber);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\Package\SubscriberInterface::onPackagePersist()
     */
    public function onPackagePersist(PackageInterface $package)
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->onPackagePersist($package);
        }
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\Package\SubscriberInterface::onPackageRemove()
     */
    public function onPackageRemove(PackageInterface $package)
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->onPackageRemove($package);
        }
    }
}
