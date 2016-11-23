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

use Rampage\Nexus\Repository\DeployTargetRepositoryInterface;
use Rampage\Nexus\Entities\DeployTarget;
use Rampage\Nexus\Entities\Application;
use Rampage\Nexus\Entities\ApplicationPackage;

/**
 * Implements the deploy target repo
 */
class DeployTargetRepository extends AbstractRepository implements DeployTargetRepositoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\ODM\Repository\AbstractRepository::getEntityClass()
     */
    protected function getEntityClass()
    {
        return DeployTarget::class;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\DeployTargetRepositoryInterface::remove()
     */
    public function remove(DeployTarget $target)
    {
        $this->removeAndFlush($target);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\DeployTargetRepositoryInterface::save()
     */
    public function save(DeployTarget $target)
    {
        $this->persistAndFlush($target);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\DeployTargetRepositoryInterface::findByApplication()
     */
    public function findByApplication(Application $application)
    {
        $qb = $this->getEntityRepository()->createQueryBuilder();

        $qb->field('applications.application')
            ->equals($application->getId());

        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\DeployTargetRepositoryInterface::findByPackage()
     */
    public function findByPackage(ApplicationPackage $package)
    {
        $qb = $this->getEntityRepository()->createQueryBuilder();

        return $qb->addOr($qb->expr()->field('applications.package')->equals($package->getId()))
            ->addOr($qb->expr()->field('applications.previousPackage')->equals($package->getId()))
            ->getQuery()
            ->execute();
    }
}
