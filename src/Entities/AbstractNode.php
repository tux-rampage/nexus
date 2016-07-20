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
use Rampage\Nexus\Deployment\DeployTargetInterface;

use Traversable;
use Rampage\Nexus\Entities\Api\ArrayExchangeInterface;
use Zend\Stdlib\Parameters;


/**
 * Implements the default node entity
 */
abstract class AbstractNode implements NodeInterface, ArrayExchangeInterface
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
     * The deploy target, this node is attached to
     *
     * @var DeployTargetInterface
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
     * Set the application states
     *
     * @param array|\Traversable $states
     */
    protected function setApplicationStates($states)
    {
        $this->applicationStates = [];

        if (!is_array($states) && !($states instanceof \Traversable)) {
            return;
        }

        foreach ($states as $appId => $state) {
            $this->applicationStates[$appId] = (string)$state;
        }
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
     * @return DeployTargetInterface
     */
    public function getDeployTarget()
    {
        return $this->deployTarget;
    }

    /**
     * @param DeployTargetInterface $deployTarget
     */
    public function attach(DeployTargetInterface $deployTarget)
    {
        $this->deployTarget = $deployTarget;
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
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::detach()
     */
    public function detach()
    {
        $this->deployTarget = null;
        return $this;
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
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::canSync()
     */
    public function canSync()
    {
        $invalidStates = [
            self::STATE_BUILDING,
            self::STATE_UNREACHABLE,
            self::STATE_SECURITY_VIOLATED
        ];

        return !in_array($this->getState(), $invalidStates);
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
