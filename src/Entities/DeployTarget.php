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

namespace Rampage\Nexus\Entities;

use Rampage\Nexus\Deployment\DeployTargetInterface;
use Rampage\Nexus\Deployment\NodeInterface;
use Zend\Stdlib\Parameters;
use Rampage\Nexus\Exception\LogicException;

/**
 * Persistable deploy target
 */
class DeployTarget implements DeployTargetInterface
{
    /**
     * Target identifier
     *
     * @var string
     */
    private $id = null;

    /**
     * Displayable name
     *
     * @var string
     */
    protected $name = null;

    /**
     * Collection of VHosts
     *
     * @var VHost[]
     */
    protected $vhosts = [];

    /**
     * @var VHost
     */
    protected $defaultVhost;

    /**
     * Collection of attached deployment nodes
     *
     * @var NodeInterface[]
     */
    protected $nodes;

    /**
     * Collection of applications for this target
     *
     * @var ApplicationInstance[]
     */
    protected $applications = [];

    /**
     * Maps the application status levels
     *
     * @var int[]
     */
    protected $statusAggregationLevels = [
        ApplicationInstance::STATE_PENDING => 1,
        ApplicationInstance::STATE_INACTIVE => 2,
        'working' => 3,
        ApplicationInstance::STATE_REMOVED => 4,
        ApplicationInstance::STATE_DEPLOYED => 5,
        ApplicationInstance::STATE_ERROR => 6,
    ];

    /**
     * Maps working states
     *
     * @var string[]
     */
    protected $workingStates = [
        ApplicationInstance::STATE_ACTIVATING,
        ApplicationInstance::STATE_DEACTIVATING,
        ApplicationInstance::STATE_REMOVING,
        ApplicationInstance::STATE_STAGING,
    ];

    /**
     * @param string $type
     */
    public function __construct()
    {
        $this->nodes = new ArrayCollection();
        $this->defaultVhost = new VHost(VHost::DEFAULT_VHOST);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = (string)$name;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\DeployTargetInterface::addVHost()
     */
    public function addVHost(VHost $host)
    {
        if ($host->getName() == VHost::DEFAULT_VHOST) {
            throw new LogicException('Cannot add another default VHost');
        }

        $this->vhosts[$host->getName()] = $host;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\DeployTargetInterface::getVHost()
     */
    public function getVHost($id)
    {
        if ($id == VHost::DEFAULT_VHOST) {
            return $this->defaultVhost;
        }

        if (isset($this->vhosts[$id])) {
            return $this->vhosts[$id];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\DeployTargetInterface::getVHosts()
     */
    public function getVHosts()
    {
        return $this->vhosts;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\DeployTargetInterface::removeVHost()
     */
    public function removeVHost(VHost $host)
    {
        if ($host->getName() == VHost::DEFAULT_VHOST) {
            throw new LogicException('Cannot remove the default vhost');
        }

        unset($this->vhosts[$host->getName()]);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\DeployTargetInterface::canManageVHosts()
     */
    public function canManageVHosts()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\DeployTargetInterface::getNodes()
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * @param string $state
     */
    protected function mapState($state)
    {
        if (isset($this->workingStates[$state])) {
            return 'working';
        }

        return $state;
    }

    /**
     * Maps the state aggregation level
     *
     * @param string $state
     * @return int
     */
    protected function mapStateAggregationLevel($state)
    {
        if (isset($this->statusAggregationLevels[$state])) {
            return $this->statusAggregationLevels[$state];
        }

        return 0;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\DeployTargetInterface::refreshStatus()
     */
    public function refreshStatus()
    {
        foreach ($this->nodes as $node) {
            $node->refreshStatus();
        }

        foreach ($this->applications as $application) {
            $state = ApplicationInstance::STATE_UNKNOWN;
            $level = 0;

            foreach ($this->nodes as $node) {
                $nodeState = $node->getApplicationState($application);
                $mappedState = $this->mapState($nodeState);
                $nodeLevel = $this->mapStateAggregationLevel($mappedState);

                if ($level < $nodeLevel) {
                    $state = $mappedState;
                    $level = $nodeLevel;
                }
            }

            $application->setState($state);
        }
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\DeployTargetInterface::addApplication()
     */
    public function addApplication(ApplicationInstance $application)
    {
        $this->applications[$application->getId()] = $application;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\DeployTargetInterface::findApplication()
     */
    public function findApplication($id)
    {
        if (!isset($this->applications[$id])) {
            return null;
        }

        return $this->applications[$id];
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\DeployTargetInterface::getApplications()
     */
    public function getApplications()
    {
        return $this->applications;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\DeployTargetInterface::removeApplication()
     */
    public function removeApplication(ApplicationInstance $instance)
    {
        unset($this->applications[$instance->getId()]);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExchangeInterface::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        $data = new Parameters($array);
        $this->name = $data->get('name');
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExportableInterface::toArray()
     */
    public function toArray()
    {
        $array = [
            'id' => $this->id,
            'name' => $this->name,
            'vhosts' => [],
            'nodes' => [],
            'applications' => [],
        ];

        foreach ($this->vhosts as $vhost) {
            $array['vhosts'][] = $vhost->toArray();
        }

        foreach ($this->nodes as $node) {
            $array['nodes'][] = $node->toArray();
        }

        foreach ($this->applications as $application) {
            $array['applications'][] = $application->toArray();
        }

        return $array;
    }
}
