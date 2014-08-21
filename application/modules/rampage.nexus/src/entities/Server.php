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
     * @orm\ManyToMany(targetEntity="Cluster", mappedBy="servers", indexBy="id")
     * @var ArrayCollection|Cluster[]
     */
    protected $clusters = null;

    /**
     *
     * @orm\OneToMany(targetEntity="ServerApplicationState", mappedBy="server", fetch="EXTRA_LAZY", indexBy="application", cascade={"all"})
     * @var ArrayCollection|ServerApplicationState[]
     */
    protected $applications;

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

    /**
     * @param ApplicationInstance $application
     * @return bool
     */
    public function hasApplication(ApplicationInstance $application)
    {
        foreach ($this->getClusters() as $cluster) {
            foreach ($cluster->getApplications() as $assignedApplication) {
                if ($application->getId() == $assignedApplication->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param ApplicationInstance $application
     * @param string $state
     * @throws \UnexpectedValueException
     * @return self
     */
    public function setApplicationState(ApplicationInstance $application, $state = null)
    {
        if (!$application->getId() && !$this->getId()) {
            throw new \UnexpectedValueException('setApplicationState requires a persisted application and server instance!');
        }

        $state = $this->getApplicationState($application);

        if (!$state) {
            $state = new ServerApplicationState($this, $application);
            $this->applications[$application->getId()] = $state;
        }

        if ($state !== null) {
            $state->setState($state);
        }

        return $this;
    }

    /**
     * @param ApplicationInstance $application
     * @return ServerApplicationState|null
     */
    public function getApplicationState(ApplicationInstance $application)
    {
        if (!isset($this->applications[$application->getId()])) {
            return null;
        }

        return $this->applications[$application->getId()];
    }
}
