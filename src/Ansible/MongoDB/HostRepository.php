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

use Rampage\Nexus\Ansible\Repository\HostRepositoryInterface;
use Rampage\Nexus\Ansible\Entities\Host;
use Rampage\Nexus\Ansible\Entities\Group;

use Rampage\Nexus\MongoDB\Repository\AbstractRepository;
use Rampage\Nexus\MongoDB\Driver\DriverInterface;
use Rampage\Nexus\MongoDB\Repository\ReferenceProviderInterface;
use Rampage\Nexus\MongoDB\Hydration\EntityHydrator\NodeHydrator;
use Rampage\Nexus\MongoDB\Cursor;

use Rampage\Nexus\Repository\DeployTargetRepositoryInterface;
use Rampage\Nexus\Repository\NodeRepositoryInterface;

use Rampage\Nexus\Deployment\NodeInterface;
use Rampage\Nexus\Exception\LogicException;

use Zend\Hydrator\HydratorInterface;


/**
 * Host repo implementation
 */
class HostRepository extends AbstractRepository implements HostRepositoryInterface
{
    /**
     * @var Host
     */
    private $prototype = null;

    /**
     * @var NodeRepositoryInterface
     */
    private $nodeRepository = null;

    /**
     * @var HydratorInterface
     */
    private $nodeHydrator = null;

    /**
     * @param DriverInterface               $driver             The MongoDB driver
     * @param ReferenceProviderInterface    $groupRepository    The group repository
     * @param NodeRepositoryInterface       $nodeRepository     The original node reposiotry
     * @param DeployTargetRepositoryInterface $targetRepository The deploy target repository
     */
    public function __construct(DriverInterface $driver, ReferenceProviderInterface $groupRepository, NodeRepositoryInterface $nodeRepository, DeployTargetRepositoryInterface $targetRepository)
    {
        parent::__construct($driver, new Hydration\HostHydrator($driver, $groupRepository, $nodeRepository, $targetRepository), 'ansible_hosts');

        $this->idStrategy = $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_STRING);
        $this->nodeHydrator = new NodeHydrator($driver, $targetRepository);
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Repository\AbstractRepository::newEntityInstance()
     */
    protected function newEntityInstance(array &$data)
    {
        if (!$this->prototype) {
            $this->prototype = (new \ReflectionClass(Host::class))->newInstanceWithoutConstructor();
        }

        return $this->prototype;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Ansible\Repository\HostRepositoryInterface::findByGroup()
     */
    public function findByGroup(Group $group)
    {
        return $this->doFind([
            'groups' => $group->getId()
        ]);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Ansible\Repository\HostRepositoryInterface::remove()
     */
    public function remove(Host $host)
    {
        if ($host->getNode() && $host->getNode()->getDeployTarget()) {
            throw new LogicException('Cannot remove a host which is currently attached to a deploy target');
        }

        $this->doRemove($host);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Ansible\Repository\HostRepositoryInterface::save()
     */
    public function save(Host $host)
    {
        $this->doPersist($host);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Ansible\Repository\HostRepositoryInterface::findDeployableHosts()
     */
    public function findDeployableHosts()
    {
        $result = $this->collection->find([]);
        $cursor = new Cursor($result, function($data) {
            /* @var $host Host */
            /* @var $node \Rampage\Nexus\Entities\AbstractNode */
            $host = $this->getOrCreate($data);

            if (!$host->getNode() && $host->getDefaultNodeType()) {
                $node = $this->nodeRepository->getPrototypeByData([
                    'type' => $host->getDefaultNodeType()
                ]);

                if ($node) {
                    $hostname = $host->getName();
                    $node->exchangeArray([
                        'name' => $hostname,
                        'url' => sprintf('https://%s:20080/', $hostname),
                    ]);

                    $this->nodeRepository->save($node);
                    $host->setNode($node);
                    $this->save($host);
                }
            }

            return $host;
        });

        return new \CallbackFilterIterator($cursor, function(Host $host) {
            return ($host->getNode() !== null);
        });
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Ansible\Repository\HostRepositoryInterface::isNodeAttached()
     */
    public function isNodeAttached(NodeInterface $node)
    {
        $nodeId = $this->driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_ID)->extract($node->getId());
        $result = $this->collection->find(['node' => $nodeId], ['_id' => true], 1);

        return ($result->count() > 0);
    }
}