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

namespace rampage\nexus\entities;

use Doctrine\ODM\MongoDB\Mapping\Annotations as odm;
use Zend\Form\Annotation as form;

/**
 * @odm\Document(collection="nodes")
 * @form\Hydrator("Zend\Stdlib\Hydrator\ClassMethods")
 */
class Node
{
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
     * @return \MongoId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \rampage\nexus\entities\string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param \rampage\nexus\entities\string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return \rampage\nexus\entities\DeployTarget
     */
    public function getDeployTarget()
    {
        return $this->deployTarget;
    }

    /**
     * @param \rampage\nexus\entities\DeployTarget $deployTarget
     */
    public function setDeployTarget(DeployTarget $deployTarget)
    {
        $this->deployTarget = $deployTarget;
        return $this;
    }
}
