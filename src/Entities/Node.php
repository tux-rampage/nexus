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

use rampage\nexus\exceptions;

use Doctrine\ODM\MongoDB\Mapping\Annotations as odm;
use Zend\Form\Annotation as form;
use rampage\core\ArrayConfig;

/**
 * @odm\Document(collection="nodes")
 * @form\Hydrator("Zend\Stdlib\Hydrator\ClassMethods")
 */
class Node
{
    const STATE_FAILURE = 'failure';
    const STATE_BUILDING = 'building';
    const STATE_READY = 'ready';
    const STATE_UNINITIALIZED = 'uninitialized';

    /**
     * @odm\Id
     * @form\Exclude()
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
     * @odm\ReferenceOne(targetDocument="DeployTarget");
     * @form\Name("deploy_target")
     * @var DeployTarget
     */
    protected $deployTarget = null;

    /**
     * @odm\String()
     * @form\Type("text")
     * @form\Validator({"type": "uri"})
     * @var string
     */
    protected $url = null;

    /**
     * @odm\String
     * @form\Exclude
     * @var string
     */
    protected $state = self::STATE_UNINITIALIZED;

    /**
     * @odm\String
     * @form\Exclude
     * @var string
     */
    protected $certificate = null;

    /**
     * @odm\Hash
     * @form\Exclude
     * @var array
     */
    protected $serverInfo = [];

    /**
     * @form\Exclude
     * @odm\NotSaved
     * @var ArrayConfig
     */
    protected $info = null;

    /**
     * @return \MongoId
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
     * @return \Rampage\Nexus\Entities\DeployTarget
     */
    public function getDeployTarget()
    {
        return $this->deployTarget;
    }

    /**
     * @param \Rampage\Nexus\Entities\DeployTarget $deployTarget
     */
    public function setDeployTarget(DeployTarget $deployTarget)
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
     * @param string $state
     * @return self
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return ArrayConfig|mixed
     */
    public function getServerInfo($key = null)
    {
        if (!$this->info) {
            $this->info = new ArrayConfig($this->serverInfo);
        }

        if ($key !== null) {
            $this->info->get($key);
        }

        return $this->info;
    }

    /**
     * @param array $serverInfo
     * @return self
     */
    public function setServerInfo($serverInfo)
    {
        if (!is_array($serverInfo) || !($serverInfo instanceof \ArrayAccess)) {
            throw new exceptions\InvalidArgumentException(sprintf(
                '$servrInfo must be an array or implement ArrayAccess, [%s] given',
                is_object($serverInfo)? get_class($serverInfo) : gettype($serverInfo)
            ));
        }

        $this->serverInfo = $serverInfo;
        $this->info = null;

        return $this;
    }

    /**
     * @return string
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * @param string $certificate
     * @return self
     */
    public function setCertificate($certificate)
    {
        $this->certificate = $certificate? (string)$certificate : null;
        return $this;
    }
}
