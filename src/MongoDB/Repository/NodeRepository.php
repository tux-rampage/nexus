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

use Rampage\Nexus\Repository\NodeRepositoryInterface;
use Rampage\Nexus\MongoDB\Driver\DriverInterface;
use Rampage\Nexus\Deployment\DeployTargetInterface;
use Rampage\Nexus\Entities\Node;
use Rampage\Nexus\Deployment\NodeStrategyProviderInterface;
use Rampage\Nexus\Exception\LogicException;
use Rampage\Nexus\MongoDB\Hydration\EntityHydrator\NodeHydrator;
use Rampage\Nexus\Repository\DeployTargetRepositoryInterface;
use Rampage\Nexus\Exception\InvalidArgumentException;

final class NodeRepository extends AbstractRepository implements NodeRepositoryInterface, ReferenceProviderInterface
{
    const COLLECTION_NAME = 'nodes';

    /**
     * @var NodeProviderInterface
     */
    private $nodeProvider;

    /**
     * @var DeployTargetRepositoryInterface|ReferenceProviderInterface
     */
    private $deployTargetRepository;

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\AbstractRepository::__construct()
     */
    public function __construct(DriverInterface $driver, NodeStrategyProviderInterface $nodeProvider, DeployTargetRepositoryInterface $deployTargetRepository)
    {
        if (!$deployTargetRepository instanceof ReferenceProviderInterface) {
            throw new InvalidArgumentException('The deployment target repository must implement the reference provider interface');
        }

        $this->deployTargetRepository = $deployTargetRepository;
        $this->nodeProvider = $nodeProvider;

        parent::__construct($driver, new NodeHydrator($driver, $deployTargetRepository), self::COLLECTION_NAME);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\AbstractRepository::getIdentifierStrategy()
     */
    protected function getIdentifierStrategy()
    {
        return $this->driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_ID);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\AbstractRepository::newEntityInstance()
     */
    protected function newEntityInstance(array &$data)
    {
        if (!isset($data['type'])) {
            throw new LogicException('Missing node type');
        }

        return $this->nodeProvider->get($data['type']);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\NodeRepositoryInterface::findByTarget()
     */
    public function findByTarget(DeployTargetInterface $target)
    {
        return $this->doFindOne(['deployTarget' => $this->getIdentifierStrategy()->extract($target->getId())]);
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
     * @param Node $object
     */
    public function getReference($object)
    {
        return $this->getIdentifierStrategy()->hydrate($object->getId());
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\NodeRepositoryInterface::remove()
     */
    public function remove(Node $node)
    {
        $this->doRemove($node);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\NodeRepositoryInterface::save()
     */
    public function save(Node $node)
    {
        $this->doPersist($node);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\PrototypeProviderInterface::getPrototypeByData()
     */
    public function getPrototypeByData($data)
    {
        return $this->newEntityInstance($data);
    }
}
