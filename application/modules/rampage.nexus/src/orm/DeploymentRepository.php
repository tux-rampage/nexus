<?php
/**
 * This is part of rampage-nexus
 * Copyright (c) 2014 Axel Helmert
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
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\orm;

use rampage\nexus\DeploymentConfig;

use rampage\nexus\entities\ApplicationInstance;
use rampage\nexus\entities\VirtualHost;
use rampage\nexus\entities\Cluster;
use rampage\nexus\entities\ConfigTemplate;

use Doctrine\ORM\EntityManager;


class DeploymentRepository
{
    /**
     * @var EntityManager
     */
    protected $entityManager = null;

    /**
     * @var DeploymentConfig
     */
    protected $config = null;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, DeploymentConfig $config)
    {
        $this->entityManager = $entityManager;
        $this->config = $config;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param string $class
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getEntityRepository($class)
    {
        return $this->entityManager->getRepository($class);
    }

    /**
     * @param string $class
     * @param mixed $id
     * @return ApplicationInstance|VirtualHost|ConfigTemplate
     */
    public function find($class, $id)
    {
        return $this->getEntityRepository($class)->find($id);
    }

    /**
     * @return Cluster[]
     */
    public function findClusters()
    {
        return $this->getEntityRepository(Cluster::class)->findAll();
    }

    /**
     * @param string $name
     * @return ApplicationInstance
     */
    public function findApplicationByName($name)
    {
        return $this->getEntityRepository(ApplicationInstance::class)->findOneBy(array('name' => $name));
    }

    /**
     * @param int $id
     * @return rampage\nexus\entities\ApplicationInstance|null
     */
    public function findApplicationById($id)
    {
        return $this->find(ApplicationInstance::class, $id);
    }

    /**
     * @param null|object|array $entity
     * @return self
     */
    public function flush($entity = null)
    {
        $this->entityManager->flush($entity);
        return $this;
    }

    /**
     * @param object $object
     * @param bool $flush
     * @return self
     */
    public function persist($object, $flush = false)
    {
        $this->entityManager->persist($object);

        if ($flush) {
            $this->flush($object);
        }

        return $this;
    }
}
