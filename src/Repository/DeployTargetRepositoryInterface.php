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

namespace Rampage\Nexus\Repository;

use Rampage\Nexus\Repository\RepositoryInterface;
use Rampage\Nexus\Entities\DeployTarget;
use Rampage\Nexus\Entities\Application;
use Rampage\Nexus\Entities\ApplicationPackage;

/**
 * Repository for deploy targets
 */
interface DeployTargetRepositoryInterface extends RepositoryInterface
{
    /**
     * Find deploy targets where the given application is deployed
     *
     * @param Application $application
     * @return DeployTarget[]
     */
    public function findByApplication(Application $application);

    /**
     * Find deploy targets where the given application package is (or was previously) deployed
     *
     * @param ApplicationPackage $application
     * @return DeployTarget[]
     */
    public function findByPackage(ApplicationPackage $application);

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::save()
     */
    public function save(DeployTarget $target);

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\RepositoryInterface::remove()
     */
    public function remove(DeployTarget $target);
}
