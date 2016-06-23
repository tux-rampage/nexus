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

namespace Rampage\Nexus\Deployment;

use Rampage\Nexus\Entities\ApplicationInstance;
use Rampage\Nexus\Entities\Api\ArrayExportableInterface;


/**
 * Defines the interface for deployment nodes
 */
interface NodeInterface extends ArrayExportableInterface
{
    /**
     * Node is in failure state
     */
    const STATE_FAILURE = 'failure';

    /**
     * Node is currently building
     */
    const STATE_BUILDING = 'building';

    /**
     * Node is ready
     */
    const STATE_READY = 'ready';

    /**
     * Node is not yet initialized
     */
    const STATE_UNINITIALIZED = 'uninitialized';

    /**
     * Node is not reachable
     */
    const STATE_UNREACHABLE = 'unreachable';


    /**
     * The unique identifier of this deployment node
     */
    public function getId();

    /**
     * The node name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the deploy target, this node is attached to
     *
     * @return DeployTargetInterface
     */
    public function getDeployTarget();

    /**
     * Checks whether the node is attached to a deploy target or not
     *
     * @return bool
     */
    public function isAttached();

    /**
     * Attaches the node to the given teploy target
     *
     * @param   DeployTarget    $deployTarget
     * @return  self
     */
    public function attach(DeployTargetInterface $deployTarget);

    /**
     * Removes the node from its deploy target
     *
     * @return self
     */
    public function detach();

    /**
     * Returns the nodes state
     *
     * @return string
     */
    public function getState();

    /**
     * Returns the node's state of the given application
     *
     * @param ApplicationInstance $application
     * @return string
     */
    public function getApplicationState(ApplicationInstance $application);

    /**
     * Returns the node's server info
     *
     * @return array
     */
    public function getServerInfo($key = null);

    /**
     * The public RSA key
     *
     * @return string
     */
    public function getPublicKey();

    /**
     * Check if this node accepts the given node as sibling in a cluster
     *
     * @param NodeInterface $node
     * @return bool
     */
    public function acceptsClusterSibling(NodeInterface $node);

    /**
     * Sync changes to the node
     */
    public function sync();

    /**
     * Updates state information from the node
     *
     * @return self
     */
    public function refreshStatus();
}
