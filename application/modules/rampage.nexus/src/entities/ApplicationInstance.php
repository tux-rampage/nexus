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

namespace rampage\nexus\entities;

use Doctrine\ODM\MongoDB\Mapping\Annotations as odm;

use Zend\Form\Annotation as form;
use Zend\Stdlib\Guard\ArrayOrTraversableGuardTrait;


/**
 * @odm\Document(collection="applicationInstances")
 * @form\Hydrator("Zend\Stdlib\Hydrator\ClassMethods")
 */
class ApplicationInstance
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
     * @odm\Id
     * @form\Exclude
     * @var \MongoId
     */
    private $id = null;

    /**
     * @odm\String
     * @form\Type("text")
     * @var string
     */
    protected $name = null;

    /**
     * @odm\String
     * @form\Exclude
     * @var string
     */
    protected $state = self::STATE_PENDING;

    /**
     * @form\Type("text")
     * @form\Attributes({
     *  "type": "appPackageSelector"
     * })
     *
     * @odm\ReferenceOne(targetDocument="ApplicationPackage")
     *
     * @var ApplicationPackage
     */
    protected $package = null;

    /**
     * @form\Exclude
     * @odm\ReferenceOne(targetDocument="ApplicationPackage")
     * @var ApplicationPackage
     */
    protected $previousPackage = null;

    /**
     * @odm\ReferenceOne(targetDocument="DeployTarget", nullable=false)
     * @form\Exclude
     * @var DeployTarget
     */
    protected $target = null;

    /**
     * @form\Type("text")
     * @odm\String
     * @var string
     */
    protected $path = null;

    /**
     * @odm\Hash
     * @var array
     */
    protected $userParameters = [];

    /**
     * @odm\Hash()
     * @var array
     */
    protected $previousParameters = null;

    /**
     * Construct
     */
    public function __construct(DeployTarget $target = null)
    {
        $this->target = null;
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
     */
    public function setName($name)
    {
        $this->name = (string)$name;
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
     * @param string $state
     * @return self
     */
    public function setState($state)
    {
        $this->state = (string)$state;
        return $this;
    }

    /**
     * @return ApplicationPackage
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @param ApplicationPackage $package
     * @return self
     */
    public function setPackage(ApplicationPackage $package)
    {
        $this->package = $package;
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
     * @param string $path
     * @return self
     */
    public function setPath($path)
    {
        $this->path = (string)$path;
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
     * @param ApplicationPackage $previousPackage
     * @return self
     */
    public function setPreviousPackage(ApplicationPackage $previousPackage)
    {
        $this->previousPackage = $previousPackage;
        return $this;
    }

    /**
     * @return DeployTarget
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param DeployTarget $target
     * @return self
     */
    public function setTarget(DeployTarget $target)
    {
        $this->target = $target;
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
     * @param array $previousParameters
     */
    public function setPreviousParameters($previousParameters)
    {
        $this->previousParameters = $previousParameters;
        return $this;
    }
}
