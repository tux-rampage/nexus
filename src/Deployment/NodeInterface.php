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
use Rampage\Nexus\Entities\DeployTarget;
use Rampage\Nexus\Exception\LogicException;


/**
 * Defines the interface for deployment nodes
 */
interface NodeInterface
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
     * The node's communication channel violeates the security parameters
     */
    const STATE_SECURITY_VIOLATED = 'securityViolated';

    /**
     * Returns the node's type id
     *
     * @return string
     */
    public function getTypeId();

    /**
     * Attaches the node to the given teploy target
     *
     * @param   DeployTarget    $deployTarget
     * @return  self
     */
    public function attach(DeployTarget $deployTarget);

    /**
     * Removes the node from its deploy target
     *
     * @return self
     */
    public function detach();

    /**
     * Trigger a rebuild
     *
     * This causes the node to rebuild a single application instance or
     * the whole node.
     *
     * @param   ApplicationInstance $application    If provided, only rebuild this application instance
     * @throws  LogicException                      When the node or application cannot be rebuild
     */
    public function rebuild(ApplicationInstance $application = null);

    /**
     * Check if this node accepts the given node as sibling in a cluster
     *
     * @param NodeInterface $node
     * @return bool
     */
    public function acceptsClusterSibling(NodeInterface $node);

    /**
     * Checks whether the node can perform a sync or not
     *
     * The caller may perform a `refresh()` to ensure up to date information
     *
     * @return bool
     */
    public function canSync();

    /**
     * Sync changes to the node
     *
     * The node's ability to sync must be checked before calling this method.
     *
     * @throws  LogicException  When the node is not syncable
     */
    public function sync();

    /**
     * Updates state information from the node
     *
     * @return self
     */
    public function refresh();
}
