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

use Rampage\Nexus\Package\PackageInterface;

use Rampage\Nexus\Exception\LogicException;
use Rampage\Nexus\Exception\UnexpectedValueException;

use Zend\Stdlib\Guard\ArrayOrTraversableGuardTrait;
use Zend\Stdlib\Parameters;
use Rampage\Nexus\Exception\InvalidArgumentException;


/**
 * Defines a deployed application instance
 *
 * This entity is aggregated by the deploy target
 */
class ApplicationInstance implements Api\ArrayExchangeInterface
{
    const STATE_DEPLOYED = 'deployed';
    const STATE_ERROR = 'error';
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
     * The vhost within the teploy target
     *
     * @var VHost
     */
    private $vhost = null;

    /**
     * The target path within the vhost
     *
     * @var string
     */
    protected $path = '/';

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
     * Pervious user parameters
     *
     * @var array
     */
    protected $previousUserParameters = null;

    /**
     * @var bool
     */
    private $isRemoved = false;

    /**
     * Construct
     *
     * @param   string  $id     The instance identifier
     * @param   string  $path   The location path within the vhost
     */
    public function __construct(Application $application, $id, $path = null)
    {
        if (!preg_match('~^[a-z0-9-_]+$~i', $id)) {
            throw new InvalidArgumentException('Bad application instance identifier: ' . $id);
        }

        if ($path) {
            if (!preg_match('~^/?[a-z0-9-_]+(/[a-z0-9-_]+)*/?$~i', $path)) {
                throw new InvalidArgumentException('Bad application path: ' . $path);
            }

            $path = '/' . trim($path, '/') . '/';
        }

        $this->application = $application;
        $this->id = $id;
        $this->path = $path? : '/';
    }

    /**
     * Returns the application identifier
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns the VHost for this applciation
     *
     * @return VHost
     */
    public function getVHost()
    {
        return $this->vhost;
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
     * Returns the application this instance references
     *
     * @return \Rampage\Nexus\Entities\Application
     */
    public function getApplication()
    {
        if (!$this->application) {
            throw new LogicException('Missing application instance');
        }

        return $this->application;
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
    public function setPackage(PackageInterface $package)
    {
        if (!$this->application->hasPackage($package)) {
            throw new LogicException(sprintf('Package %s does not provide application %s', $this->package->getId(), $this->application->getName()));
        }

        $this->previousPackage = $this->package;
        $this->previousUserParameters = $this->userParameters;
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

    public function isRemoved()
    {
        return $this->isRemoved;
    }

    /**
     * Perform application removal
     *
     * @return self
     */
    public function remove()
    {
        $this->state = self::STATE_REMOVING;
        $this->isRemoved = true;
        return $this;
    }

    /**
     * Rollback to the previouis instance state
     *
     * @throws LogicException
     * @return self
     */
    public function rollback()
    {
        if (!$this->previousPackage) {
            throw new LogicException('Cannot roll back without previous package');
        }

        $this->package = $this->previousPackage;
        $this->userParameters = $this->previousUserParameters? : [];
        $this->previousPackage = null;
        $this->previousUserParameters = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExchangeInterface::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        $data = new Parameters($array);
        $packageId = $data->get('package');

        $this->label = $data->get('label');
        $this->flavor = $data->get('flavor');

        $this->setUserParameters($data->get('userParameters'));

        if ($packageId) {
            $package = $this->application->findPackage($packageId);

            if (!$packageId) {
                throw new UnexpectedValueException(sprintf(
                    'The package id %s does not exist for Application %s',
                    $packageId,
                    $this->application->getName()
                ));
            }

            $this->setPackage($package);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExportableInterface::toArray()
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'application' => [
                'id' => $this->getApplication()->getId(),
                'label' => $this->getApplication()->getLabel(),
                'package' => $this->package? $this->package->toArray() : null,
                'previousPackage' => $this->previousPackage? $this->previousPackage->toArray() : null,
            ],
            'flavor' => $this->flavor,
            'path' => $this->path,
            'state' => $this->state,
            'userParameters' => $this->userParameters,
            'vhost' => $this->vhost->getName()
        ];
    }
}
