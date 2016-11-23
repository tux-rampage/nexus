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

use Rampage\Nexus\Exception\LogicException;
use Rampage\Nexus\Deployment\NodeInterface;

use Doctrine\Common\Collections\ArrayCollection;
use Zend\Stdlib\Parameters;

/**
 * Persistable deploy target
 */
class DeployTarget
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
     * @var VHost[]|ArrayCollection
     */
    protected $vhosts;

    /**
     * @var VHost
     */
    protected $defaultVhost;

    /**
     * Collection of attached deployment nodes
     *
     * @var NodeInterface[]|ArrayCollection
     */
    protected $nodes;

    /**
     * Collection of applications for this target
     *
     * @var ApplicationInstance[]|ArrayCollection
     */
    protected $applications;

    /**
     * Maps the application status levels
     *
     * @var int[]
     */
    protected $statusAggregationLevels = [
        ApplicationInstance::STATE_PENDING => 1,
        ApplicationInstance::STATE_REMOVED => 2,
        ApplicationInstance::STATE_INACTIVE => 4,
        ApplicationInstance::STATE_DEPLOYED => 8,
        ApplicationInstance::STATE_ERROR => 16,
        'working' => 32,
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
        $this->vhosts = new ArrayCollection();
        $this->applications = new ArrayCollection();
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
     * @param VHost $host
     * @throws LogicException
     * @return self
     */
    public function addVHost(VHost $host)
    {
        if ($host->isDefault()) {
            throw new LogicException('Cannot add another default VHost');
        }

        $this->vhosts[$host->getId()] = $host;
        return $this;
    }

    /**
     * @param string|null $id
     * @return \Rampage\Nexus\Entities\VHost|NULL
     */
    public function getVHost($id)
    {
        if (!$id || ($id == VHost::DEFAULT_VHOST)) {
            return $this->defaultVhost;
        }

        if (isset($this->vhosts[$id])) {
            return $this->vhosts[$id];
        }

        return null;
    }

    /**
     * @return \Rampage\Nexus\Entities\VHost[]
     */
    public function getVHosts()
    {
        return $this->vhosts->toArray();
    }

    /**
     * @param VHost $host
     * @throws LogicException
     * @return \Rampage\Nexus\Entities\DeployTarget
     */
    public function removeVHost(VHost $host)
    {
        if ($host->isDefault()) {
            throw new LogicException('Cannot remove the default vhost');
        }

        unset($this->vhosts[$host->getId()]);
        return $this;
    }

    /**
     * @return boolean
     */
    public function canManageVHosts()
    {
        // TODO: Implement actual check for managable vhost configs
        return true;
    }

    /**
     * @return \Rampage\Nexus\Deployment\NodeInterface[]
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
     * Refresh the status
     */
    public function refreshStatus()
    {
        foreach ($this->nodes as $node) {
            $node->refresh();
        }

        $this->updateApplicationStates();
    }

    /**
     * Updates all application states from nodes
     */
    public function updateApplicationStates()
    {
        foreach ($this->applications as $application) {
            $this->updateApplicationState($application);
        }
    }

    /**
     * Update the state of a single application
     *
     * @param string|ApplicationInstance $application
     * @return self
     */
    public function updateApplicationState($application)
    {
        if (!$application instanceof ApplicationInstance) {
            $application = $this->findApplication($application);

            if (!$application) {
                return $this;
            }
        }

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

        if ($application->isRemoved() && ($application->getState() == ApplicationInstance::STATE_REMOVED)) {
            $key = (string)$application->getId();
            unset($this->applications[$key]);
        }

        return $this;
    }

    /**
     * @param ApplicationInstance $application
     */
    public function addApplication(ApplicationInstance $application)
    {
        $id = (string)$application->getId();
        $this->applications[$id] = $application;
    }

    /**
     * @param unknown $id
     * @return NULL|\Rampage\Nexus\Entities\ApplicationInstance
     */
    public function findApplicationInstance($id)
    {
        $predicate = function(ApplicationInstance $item) use ($id) {
            return ($item->getId() == $id);
        };

        return $this->applications->filter($predicate)->first();
    }

    /**
     * @param Application $application
     * @return ApplicationInstance[]
     */
    public function findInstanceByApplication(Application $application)
    {
        $predicate = function(ApplicationInstance $instance) use ($application) {
            return ($instance->getApplication()->getId() == $application->getId());
        };

        return $this->applications->filter($predicate)->toArray();
    }

    /**
     * @return \Rampage\Nexus\Entities\ApplicationInstance[]
     */
    public function getApplications()
    {
        return $this->applications->toArray();
    }

    /**
     * @param ApplicationInstance $instance
     * @return \Rampage\Nexus\Entities\DeployTarget
     */
    public function removeApplication(ApplicationInstance $instance)
    {
        $instance->remove();
        return $this;
    }

    /**
     * @return boolean
     */
    public function canSync()
    {
        foreach ($this->nodes as $node) {
            if ($node->canSync()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws LogicException
     */
    public function sync()
    {
        if (!$this->canSync()) {
            throw new LogicException('Target is not syncable');
        }

        foreach ($this->nodes as $node) {
            if ($node->canSync()) {
                $node->sync();
            }
        }
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
            'canManageVHosts' => $this->canManageVHosts(),
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
