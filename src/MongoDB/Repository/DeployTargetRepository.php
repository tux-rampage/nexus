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

use Rampage\Nexus\Repository\DeployTargetRepositoryInterface;
use Rampage\Nexus\Deployment\DeployTargetInterface;
use Rampage\Nexus\Entities\DeployTarget;

use Rampage\Nexus\MongoDB\Hydration\EntityHydrator\DeployTargetHydrator;
use Rampage\Nexus\MongoDB\Driver\DriverInterface;


/**
 * Implements the repository for deploy targets
 */
final class DeployTargetRepository extends AbstractRepository implements DeployTargetRepositoryInterface, ReferenceProviderInterface
{
    /**
     * The collection name
     */
    const COLLECTION_NAME = 'deployTargets';

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Repository\AbstractRepository::__construct()
     */
    public function __construct(DriverInterface $driver)
    {
        parent::__construct($driver, new DeployTargetHydrator($driver), self::COLLECTION_NAME);
    }

    /**
     * Sets the node repository
     *
     * @param NodeRepository $repository
     * @return self
     */
    public function setNodeRepository(NodeRepository $repository)
    {
        $this->hydrator->setNodeRepository($repository);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Repository\AbstractRepository::newEntityInstance()
     */
    protected function newEntityInstance(array &$data)
    {
        return new DeployTarget();
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Repository\ReferenceProviderInterface::findByReference()
     */
    public function findByReference($reference)
    {
        if (is_string($reference)) {
            $reference = $this->getIdentifierStrategy()->extract($reference);
        }

        return $this->doFindOne(['_id' => $reference]);
    }

    /**
     * @param DeployTargetInterface $target
     * @return mixed
     */
    protected function extractIdFromObject(DeployTargetInterface $target)
    {
        return $this->getIdentifierStrategy()->extract($target->getId());
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Repository\ReferenceProviderInterface::getReference()
     */
    public function getReference($object)
    {
        return $this->extractIdFromObject($object);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\DeployTargetRepositoryInterface::remove()
     */
    public function remove(DeployTarget $target)
    {
        $this->doRemove($target);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\DeployTargetRepositoryInterface::save()
     */
    public function save(DeployTarget $target)
    {
        $this->doPersist($target);
    }
}
