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

namespace rampage\nexus\api;

use rampage\nexus\entities\ApplicationInstance;
use rampage\nexus\entities\Server;
use rampage\nexus\cluster\ClusterManagerInterface;

/**
 * Server API
 */
interface ServerApiInterface
{
    /**
     * @return string
     */
    public function getServerName(Server $server);

    /**
     * @param Server $server
     * @return bool
     */
    public function isClusterSupported(ClusterManagerInterface $clusterManager);

    /**
     * Stage the given application's current version
     *
     * @param ApplicationInstance $instance
     */
    public function stage(Server $server, ApplicationInstance $application);

    /**
     * Remove the application from the given server
     *
     * @param ApplicationInstance $aplication
     */
    public function remove(Server $server, ApplicationInstance $application);

    /**
     * Activate the current version of the given application
     *
     * @param ApplicationInstance $instance
     */
    public function activate(Server $server, ApplicationInstance $application);

    /**
     * Deactivate the current version of the given application
     *
     * @param ApplicationInstance $instance
     */
    public function deactivate(Server $server, ApplicationInstance $application);

    /**
     * Fetch the deployment status
     *
     * @param ApplicationInstance $application
     * @return string
     */
    public function status(Server $server, ApplicationInstance $application);

    /**
     * Detatch server from this master
     */
    public function attach(Server $server);

    /**
     * Attach server to this master
     */
    public function detach(Server $server);
}
