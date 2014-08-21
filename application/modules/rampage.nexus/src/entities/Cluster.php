<?php
/**
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
 */
class Cluster
{
    /**
     * @orm\Id @orm\Column(type="integer") @orm\GeneratedValue
     * @var int
     */
    protected $id = null;

    /**
     * @orm\Column(type="string", nullable=false)
     * @var string
     */
    protected $name = null;

    /**
     * @orm\ManyToMany(targetEntity="Server", indexBy="id")
     * @orm\JoinTable(name="cluster_servers",
     *      joinColumns={
     *          @orm\JoinColumn(name="cluster_id", referencedColumnName="id", nullable=false)
     *      },
     *      inverseJoinColumns={
     *          @orm\JoinColumn(name="server_id", referencedColumnName="id", nullable=false, unique=true)
     *      }
     * )
     * @var ArrayCollection|Server[]
     */
    protected $servers = null;

    /**
     * @orm\OneToMany(targetEntity="ApplicationInstance", mappedBy="cluster")
     * @var ArrayCollection|ApplicationInstance[]
     */
    protected $applications = null;

    /**
     * @orm\Column(type="string", nullable=true)
     * @var string
     */
    protected $type = null;

    /**
     * Construct
     */
    public function __construct($type = null)
    {
        $this->type = $type;
        $this->servers = new ArrayCollection();
        $this->applications = new ArrayCollection();
    }

    /**
     * @return number
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param Server $server
     * @return \rampage\nexus\entities\Cluster
     */
    public function addServer(Server $server)
    {
        $server->getClusters()->add($this);
        $this->servers->add($server);

        return $this;
    }

    /**
     * @return Server[]
     */
    public function getServers()
    {
        return $this->servers;
    }

    /**
     * @return ApplicationInstance[]
     */
    public function getApplications()
    {
        return $this->applications;
    }
}
