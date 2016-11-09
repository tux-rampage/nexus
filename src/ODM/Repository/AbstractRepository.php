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

use Rampage\Nexus\Repository\RepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Abstract repository implementation
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
        $this->entityClass = $this->getEntityClass();
    }

    /**
     * Must return the entity class for this repo
     *
     * @return string
     */
    abstract protected function getEntityClass();

    /**
     * Returns the underlying entity repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getEntityRepository()
    {
        return $this->objectManager->getRepository($this->entityClass);
    }

    /**
     * @param object $object
     * @return \Rampage\Nexus\ODM\Repository\AbstractRepository
     */
    protected function removeAndFlush($object)
    {
        $this->objectManager->remove($object);
        $this->objectManager->flush($object);

        return $this;
    }

    /**
     * @param object $object
     * @return \Rampage\Nexus\ODM\Repository\AbstractRepository
     */
    protected function persistAndFlush($object)
    {
        $this->objectManager->persist($object);
        $this->objectManager->flush($object);

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::findAll()
     */
    public function findAll()
    {
        return $this->objectManager->getRepository($this->entityClass)->findAll();
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::findOne()
     */
    public function findOne($id)
    {
        return $this->objectManager->find($this->entityClass, $id);
    }
}
