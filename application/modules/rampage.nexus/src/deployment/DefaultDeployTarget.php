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

use rampage\nexus\exceptions;
use rampage\nexus\entities\Node;
use rampage\nexus\entities\DeployTarget;
use rampage\nexus\entities\ApplicationInstance;


class DefaultDeployTarget implements ClusterTargetInterface
{
    /**
     * @var NodeApi
     */
    protected $api;

    /**
     * @var DeployTarget
     */
    protected $entity = null;

    /**
     * @param NodeApi $api
     */
    public function __construct(NodeApi $api)
    {
        $this->api = $api? : new NodeApi();
    }

    /**
     * @see \rampage\nexus\deployment\DeployTargetInterface::setEntity()
     */
    public function setEntity(DeployTarget $entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @see \rampage\nexus\deployment\ClusterTargetInterface::addNode()
     */
    public function addNode(Node $node)
    {
        if ($node->getDeployTarget()) {
            throw new exceptions\InvalidArgumentException(sprintf(
                'The node %s is already assigned to a deployment taget',
                $node->getName()
            ));
        }

        // TODO: trigger add node api
        $node->setDeployTarget($this->entity);

        return $this;
    }

    /**
     * @see \rampage\nexus\deployment\ClusterTargetInterface::removeNode()
     */
    public function removeNode(Node $node)
    {
        // TODO: trigger detatch via node api
        $node->setDeployTarget(null);
    }

    /**
     * @see \rampage\nexus\deployment\DeployTargetInterface::deploy()
     */
    public function deploy(ApplicationInstance $instance)
    {
        foreach ($this->entity->getNodes() as $node) {
            $this->api->requestDeploy($node, $instance);
        }

        return $this;
    }

    /**
     * @see \rampage\nexus\deployment\DeployTargetInterface::refreshStatus()
     */
    public function refreshStatus()
    {
        foreach ($this->entity->getNodes() as $node) {
            $this->api->update($node);
        }

        return $this;
    }
}
