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

use Rampage\Nexus\Deployment\NodeInterface;
use Rampage\Nexus\Deployment\NodeStrategyInterface;
use Rampage\Nexus\Deployment\NodeStrategyProviderInterface;

use Rampage\Nexus\Entities\Api\ArrayExchangeInterface;
use Rampage\Nexus\Exception\LogicException;

use Zend\Stdlib\Parameters;
use Traversable;


/**
 * Implements the default node entity
 */
class Node implements NodeInterface, ArrayExchangeInterface
{
    /**
     * The unique node identifier
     *
     * @var string
     */
    private $id = null;

    /**
     * Human readable node name
     *
     * @var string
     */
    protected $name = null;

    /**
     * Node type
     *
     * @var string
     */
    protected $type;

    /**
     * The deploy target, this node is attached to
     *
     * @var DeployTarget
     */
    protected $deployTarget = null;

    /**
     * The node's communication url
     *
     * @var string
     */
    protected $url = null;

    /**
     * The nodes deploy state
     *
     * @var string
     */
    protected $state = self::STATE_UNINITIALIZED;

    /**
     * The current application states
     *
     * @var string[]
     */
    protected $applicationStates = [];

    /**
     * The node's public key
     *
     * @var PublicK
     */
    protected $publicKey = null;

    /**
     * Server information
     *
     * @var array
     */
    protected $serverInfo = [];

    /**
     * @var array
     */
    private $flatServerInfo = null;

    /**
     * @var NodeStrategyProviderInterface
     */
    private $strategyProvider = null;

    /**
     * @var NodeStrategyInterface
     */
    private $strategy = null;

    /**
     * @param string $type
     * @return string
     */
    public function __construct($type)
    {
        $type = (string)$type;

        if (!$type) {
            throw new LogicException('The node type must not be empty');
        }

        $this->type = $type;
    }

    /**
     * Sets the node's general state
     *
     * @param string $state
     * @return self
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * Update application states
     *
     * @param array|\Traversable $states
     */
    public function updateApplicationStates($states)
    {
        if (!is_array($states) && !($states instanceof \Traversable)) {
            return;
        }

        foreach ($states as $appId => $state) {
            $this->applicationStates[$appId] = (string)$state;
        }
    }

    /**
     * Set the application states
     *
     * @param array|\Traversable $states
     */
    public function setApplicationStates($states)
    {
        $this->applicationStates = [];
        $this->updateApplicationStates($states);
    }

    /**
     * @param NodeStrategyInterface $provider
     * @return \Rampage\Nexus\Entities\Node
     */
    public function setStrategyProvider(NodeStrategyProviderInterface $provider)
    {
        $this->strategyProvider = $provider;
        return $this;
    }

    /**
     * Sets the node strategy
     *
     * @param NodeStrategyInterface $strategy
     */
    protected function setStrategy(NodeStrategyInterface $strategy)
    {
        $strategy->setEntity($this);
        $this->strategy = $strategy;
    }

    /**
     * @return \Rampage\Nexus\Deployment\NodeStrategyInterface
     */
    protected function getStrategy()
    {
        if (!$this->strategy) {
            if (!$this->strategyProvider || !$this->strategyProvider->has($this->type)) {
                throw new LogicException('Missing node strategy');
            }

            $this->setStrategy($this->strategyProvider->get($this->type));
        }

        return $this->strategy;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Rampage\Nexus\Entities\string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param \Rampage\Nexus\Entities\string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $state
     * @param array $applicationStates
     */
    public function updateState($state, $applicationStates = null)
    {
        $this->state = (string)$state;
        $this->setApplicationStates($applicationStates);

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::isAttached()
     */
    public function isAttached()
    {
        return ($this->deployTarget !== null);
    }

    /**
     * Returns the deploy target the node is attached to
     *
     * @return DeployTarget
     */
    public function getDeployTarget()
    {
        return $this->deployTarget;
    }

    /**
     * @param DeployTarget $deployTarget
     */
    public function attach(DeployTarget $deployTarget)
    {
        $this->deployTarget = $deployTarget;
        $this->getStrategy()->attach($deployTarget);

        return $this;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return ArrayConfig|mixed
     */
    public function getServerInfo($key = null)
    {
        if ($key === null) {
            return $this->serverInfo;
        }

        if ($this->flatServerInfo === null) {
            $this->flatServerInfo = [];

            foreach ($this->serverInfo as $key => $value) {
                $this->flatServerInfo[$key] = $value;
                $this->flattenCollection($value, $key, $this->serverInfo);
            }
        }

        if (!isset($this->flatServerInfo[$key])) {
            return null;
        }

        return $this->flatServerInfo[$key];
    }

    /**
     * Flattens an array or traversable into the context array
     *
     * @param array|Traversable $values
     * @param string $prefix
     * @param array $context
     */
    private function flattenCollection($values, $prefix, array &$context)
    {
        if (!is_array($values) && !($values instanceof Traversable)) {
            return;
        }

        foreach ($values as $key => $value) {
            $flattenedKey = $prefix . '.' . $key;
            $context[$flattenedKey] = $value;
            $this->flattenCollection($value, $flattenedKey, $context);
        }
    }

    /**
     * Sets the server info
     *
     * Nested array values will be flattened to dot-concatenated keys
     * while the original array will sty in place
     *
     * @param array $serverInfo
     * @return self
     */
    public function setServerInfo(array $serverInfo)
    {
        $this->serverInfo = $serverInfo;
        $this->flatServerInfo = null;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::acceptsClusterSibling()
     */
    public function acceptsClusterSibling(NodeInterface $node)
    {
        return $this->getStrategy()->acceptsClusterSibling($node);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::detach()
     */
    public function detach()
    {
        $this->getStrategy()->detach();
        $this->deployTarget = null;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::rebuild()
     */
    public function rebuild(ApplicationInstance $application = null)
    {
        $this->getStrategy()->rebuild($application);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::refresh()
     */
    public function refresh()
    {
        $this->getStrategy()->refresh();
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::sync()
     */
    public function sync()
    {
        $this->getStrategy()->sync();
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::getTypeId()
     */
    public function getTypeId()
    {
        return $this->getStrategy()->getTypeId();
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::getApplicationState()
     */
    public function getApplicationState(ApplicationInstance $application)
    {
        if (!isset($this->applicationStates[$application->getId()])) {
            return ApplicationInstance::STATE_UNKNOWN;
        }

        return $this->applicationStates[$application->getId()];
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::getPublicKey()
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::canSync()
     */
    public function canSync()
    {
        return $this->getStrategy()->canSync();
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExchangeInterface::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        $params = new Parameters($array);

        $this->name = $params->get('name', $this->name);
        $this->url = $params->get('url', $this->url);

        return $this;
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
            'url' => (string)$this->url,
            'serverInfo' => $this->serverInfo,
            'state' => $this->state,
            'isAttached' => $this->isAttached(),
        ];

        if ($this->isAttached()) {
            $array['deployTarget'] = $this->deployTarget->getId();
            $array['applicationStates'] = [];

            foreach ($this->deployTarget->getApplications() as $application) {
                $array['applicationStates'][$application->getId()] = $this->getApplicationState($application);
            }
        }

        return $array;
    }
}
