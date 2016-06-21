<?php
/**
 * This is part of rampage-nexus
 * Copyright (c) 2013 Axel Helmert
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
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\Entities;

use Zend\Stdlib\Guard\ArrayOrTraversableGuardTrait;
use Rampage\Nexus\Package\PackageInterface;
use Rampage\Nexus\Deployment\DeployTargetInterface;


/**
 * Defines a deployed application instance
 */
class ApplicationInstance implements Api\ArrayExchangeInterface
{
    const STATE_DEPLOYED = 'deployed';
    const STATE_ERROR = 'deployed';
    const STATE_PENDING = 'pending';
    const STATE_STAGING = 'staging';
    const STATE_ACTIVATING = 'activating';
    const STATE_REMOVING = 'removing';
    const STATE_DEACTIVATING = 'deactivating';
    const STATE_REMOVED = 'removed';
    const STATE_INACTIVE = 'inactive';
    const STATE_UNKNOWN = 'unknown';

    use ArrayOrTraversableGuardTrait;

    /**
     * Internal application identifier
     *
     * @var string
     */
    private $id = null;

    /**
     * The human readable label of this instance
     *
     * @var string
     */
    protected $label = null;

    /**
     * The current application state
     *
     * This might be computed across all nodes
     *
     * @var string
     */
    protected $state = self::STATE_PENDING;

    /**
     * The application that is deployed with this instance
     *
     * @var Application
     */
    protected $application = null;

    /**
     * The currently deployed package
     *
     * @var PackageInterface
     */
    private $package = null;

    /**
     * The previously deployed application package
     *
     * @var PackageInterface
     */
    private $previousPackage = null;

    /**
     *
     * @var DeployTargetInterface
     */
    protected $target = null;

    /**
     * The vhost within the teploy target
     *
     * @var string
     */
    private $vhost = null;

    /**
     * The target path within the vhost
     *
     * @var string
     */
    protected $path = null;

    /**
     * The application flavor used by the deploy strategy to optimize the created config
     *
     * @var string
     */
    protected $flavor = null;

    /**
     * User provided parameters
     *
     * @var array
     */
    protected $userParameters = [];

    /**
     * Construct
     */
    public function __construct(DeployTargetInterface $target = null)
    {
        $this->target = $target;
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
     * Returns the VHost for this applciation
     *
     * @return VHost
     */
    public function getVHost()
    {
        return $this->target->getVHost($this->vhost);
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     * @return self
     */
    public function setState($state)
    {
        $this->state = (string)$state;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return PackageInterface
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @param PackageInterface $package
     * @return self
     */
    public function setPackage($package)
    {
        $this->previousPackage = $this->package;
        $this->package = $package;

        return $this;
    }

    /**
     * @return ApplicationPackage
     */
    public function getPreviousPackage()
    {
        return $this->previousPackage;
    }

    /**
     * @return DeployTarget
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return string
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * @param string $flavor
     */
    public function setFlavor($flavor)
    {
        $this->flavor = ($flavor !== null)? (string)$flavor : null;
        return $this;
    }

    /**
     * @return array|\Traversable
     */
    public function getUserParameters()
    {
        return $this->userParameters;
    }

    /**
     * @param array|\Traversable $parameters
     * @return self
     */
    public function setUserParameters($parameters)
    {
        $this->guardForArrayOrTraversable($parameters, 'user parameters');
        $this->userParameters = $parameters;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getPreviousParameters()
    {
        return $this->previousParameters;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExchangeInterface::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        // TODO Auto-generated method stub
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExportableInterface::toArray()
     */
    public function toArray()
    {
        // TODO Auto-generated method stub
    }
}
