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

use Rampage\Nexus\Repository\NodeRepositoryInterface;
use Rampage\Nexus\Deployment\NodeStrategyProviderInterface;
use Rampage\Nexus\Exception\InvalidArgumentException;

use Rampage\Nexus\Entities\Node;
use Rampage\Nexus\Entities\DeployTarget;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Implements the node repository
 */
class NodeRepository extends AbstractRepository implements NodeRepositoryInterface
{
    /**
     * @var NodeStrategyProviderInterface
     */
    private $nodeStrategyProvider;

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\ODM\Repository\AbstractRepository::__construct()
     */
    public function __construct(ObjectManager $objectManager, NodeStrategyProviderInterface $nodeStrategyProvider)
    {
        parent::__construct($objectManager);
        $this->nodeStrategyProvider = $nodeStrategyProvider;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\ODM\Repository\AbstractRepository::getEntityClass()
     */
    protected function getEntityClass()
    {
        return Node::class;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\NodeRepositoryInterface::findByTarget()
     */
    public function findByTarget(DeployTarget $target)
    {
        $repo = $this->objectManager->getRepository($this->entityClass);

        return $repo->findBy([
            'deployTarget' => $target->getId()
        ]);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\NodeRepositoryInterface::remove()
     */
    public function remove(Node $node)
    {
        $this->removeAndFlush($node);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\NodeRepositoryInterface::save()
     */
    public function save(Node $node)
    {
        $this->persistAndFlush($node);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\PrototypeProviderInterface::getPrototypeByData()
     */
    public function getPrototypeByData($data)
    {
        if (!isset($data['type'])) {
            throw new InvalidArgumentException('Invalid node data: A type id must be set');
        }

        $node = new Node($data['type']);
        $node->setStrategyProvider($this->nodeStrategyProvider);

        return $node;
    }
}
