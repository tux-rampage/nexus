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

namespace Rampage\Nexus\Ansible\MongoDB;

use Rampage\Nexus\Ansible\Entities\Group;
use Rampage\Nexus\Ansible\Repository\GroupRepositoryInterface;
use Rampage\Nexus\Exception\InvalidArgumentException;
use Rampage\Nexus\MongoDB\Repository\ReferenceProviderInterface;
use Rampage\Nexus\MongoDB\Repository\AbstractRepository;
use Rampage\Nexus\MongoDB\Driver\DriverInterface;


/**
 * Implements the group repository
 */
class GroupRepository extends AbstractRepository implements GroupRepositoryInterface, ReferenceProviderInterface
{
    /**
     * @var Group
     */
    private $prototype = null;

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Repository\AbstractRepository::__construct()
     */
    public function __construct(DriverInterface $driver)
    {
        parent::__construct($driver, new Hydration\GroupHydrator($driver, $this), 'ansible_groups');
        $this->idStrategy = $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_ID);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Repository\ReferenceProviderInterface::findByReference()
     */
    public function findByReference($reference)
    {
        return $this->doFindOne(['_id' => $reference]);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Repository\ReferenceProviderInterface::getReference()
     */
    public function getReference($object)
    {
        if (!$object instanceof Group) {
            throw new InvalidArgumentException('This repository can only provide references for ' . Group::class);
        }

        return $this->idStrategy->extract($object->getId());
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Repository\AbstractRepository::newEntityInstance()
     */
    protected function newEntityInstance(array &$data)
    {
        if (!$this->prototype) {
            $this->prototype = (new \ReflectionClass(Group::class))->newInstanceWithoutConstructor();
        }

        return clone $this->prototype;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Ansible\Repository\GroupRepositoryInterface::remove()
     */
    public function remove(Group $group)
    {
        $this->doRemove($group);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Ansible\Repository\GroupRepositoryInterface::save()
     */
    public function save(Group $group)
    {
        $this->doPersist($group);
    }
}
