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

namespace Rampage\Nexus\Deployment;

use Rampage\Nexus\Entities\ApplicationInstance;
use Rampage\Nexus\Entities\VHost;
use Rampage\Nexus\Entities\Api\ArrayExchangeInterface;
use Rampage\Nexus\Exception\RuntimeException;
use Rampage\Nexus\Exception\LogicException;


/**
 * Interface for deployment target implementations
 */
interface DeployTargetInterface extends ArrayExchangeInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return bool
     */
    public function canManageVHosts();

    /**
     * Returns all defined vhosts
     *
     * @return VHost[]
     */
    public function getVHosts();

    /**
     * Adds a vhost definition
     *
     * @param VHost $host
     * @return self
     */
    public function addVHost(VHost $host);

    /**
     * Removes a vhost definition
     *
     * @param  VHost    $host   The vhost to remove
     * @return self
     *
     * @throws RuntimeException When there are applications using the vhost
     *                          or when the default vhost is attempted to be
     *                          removed
     */
    public function removeVHost(VHost $host);

    /**
     * Returns the vhost for the given id
     *
     * @param string $id
     * @return VHost
     */
    public function getVHost($id);

    /**
     * @return string
     */
    public function getName();

    /**
     * Returns all nodes in the deploy target
     *
     * @return NodeInterface[]
     */
    public function getNodes();

    /**
     * Returns all application instances for this target
     *
     * @return ApplicationInstance
     */
    public function getApplications();

    /**
     * Add an application instance
     *
     * @param ApplicationInstance $application
     * @return self
     */
    public function addApplication(ApplicationInstance $application);

    /**
     * Removes an application instance from this tartget
     *
     * @param ApplicationInstance $instance
     */
    public function removeApplication(ApplicationInstance $instance);

    /**
     * Find an application instance by its identifier
     *
     * @param string $id
     * @return ApplicationInstance|null
     */
    public function findApplication($id);

    /**
     * Refresh the deployment target status
     *
     * @return self Fluid Interface
     */
    public function refreshStatus();

    /**
     * Check if the target is syncable
     *
     * @return bool
     */
    public function canSync();

    /**
     * Sync the deploy target
     *
     * @throws  LogicException  When the target is not able to sync
     */
    public function sync();
}
