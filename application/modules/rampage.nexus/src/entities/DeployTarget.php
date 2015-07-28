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

/**
 * @odm\Document(collection="deploytargets")
 */
class DeployTarget
{
    const TYPE_RAMPAGE = 'rampage';
    const TYPE_ZENDSERVER = 'zendserver';

    /**
     * @odm\Id
     * @var \MongoId
     */
    private $id = null;

    /**
     * @odm\String
     * @var string
     */
    protected $name = null;

    /**
     * @odm\String
     * @var string
     */
    protected $type = self::TYPE_RAMPAGE;

    /**
     * @odm\ReferenceMany(targetDocument=Node, mappedBy="deployTarget")
     * @var Node[]
     */
    protected $nodes = [];

    /**
     * @odm\ReferenceMany(targetDocument="ApplicationInstance", mappedBy="target")
     * @var ApplicationInstance[]
     */
    protected $applications = [];

    /**
     * @return MongoId
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
     * @return
     */
    public function getTypeInstance()
    {

    }

    /**
     * @return Node[]
     */
    public function getNodes()
    {
        return $this->nodes;
    }
}
