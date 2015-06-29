<?php
/**
 * Copyright (c) 2015 Axel Helmert
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
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\deployment;

use rampage\nexus\entities\ApplicationInstance;
use rampage\nexus\entities\DeployTarget;


/**
 * Interface for deployment target implementations
 */
interface DeployTargetInterface
{
    /**
     * Set the entity for this deploy target implementation
     *
     * @param DeployTarget $entity
     */
    public function setEntity(DeployTarget $entity);

    /**
     * Check whether the target can deploy or not
     *
     * @return bool
     */
    public function canDeploy();

    /**
     * Deploy the given application instance
     *
     * @param ApplicationInstance $instance
     * @return self Fluid Interface
     */
    public function deploy(ApplicationInstance $instance);

    /**
     * Remove the given application instance
     *
     * @param ApplicationInstance $instance
     * @return self Fluid Interface
     */
    public function remove(ApplicationInstance $instance);

    /**
     * Refresh the deployment target status
     *
     * @return self Fluid Interface
     */
    public function refreshStatus();
}
