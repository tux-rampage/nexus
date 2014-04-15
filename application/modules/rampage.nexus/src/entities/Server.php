<?php
/**
 * This is part of application_name
 * Copyright (c) 2014 Axel Helmert
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
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\entities;

use Doctrine\ORM\Mapping as orm;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @orm\Entity
 * @orm\Table(
 *     name="server",
 *     uniqueConstraints={
 *         @orm\UniqueConstraint(name="UINQ_SERVERNAME", columns={"name"})
 *     }
 * )
 */
class Server
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $type = null;

    /**
     * @var string
     */
    protected $url = null;

    /**
     * @orm\ManyToMany(targetEntity="Cluster", mappedBy="servers")
     * @var ArrayCollection|Cluster[]
     */
    protected $clusters = null;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->clusters = new ArrayCollection();
    }

    /**
     * @return int
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
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
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = (string)$type;
        return $this;
    }

    /**
     * @param string $url
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = (string)$url;
        return $this;
    }

    /**
     * @return Cluster[]
     */
    public function getClusters()
    {
        return $this->clusters;
    }
}