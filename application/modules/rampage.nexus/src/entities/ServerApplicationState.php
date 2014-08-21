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

/**
 * @orm\Entity
 */
class ServerApplicationState
{
    /**
     * @orm\Id
     * @orm\ManyToOne(targetEntity="Server", inversedBy="applications")
     * @var Server
     */
    protected $server;

    /**
     * @orm\Id
     * @orm\ManyToOne(targetEntity="ApplicationInstance")
     * @var ApplicationInstance
     */
    protected $application;

    /**
     * @orm\Column(type="string", nullable=false)
     * @var string
     */
    protected $state = ApplicationInstance::STATE_PENDING;

    /**
     * @param Server $server
     * @param ApplicationInstance $application
     */
    public function __construct(Server $server, ApplicationInstance $application)
    {
        $this->server = $server;
        $this->application = $application;
    }

    /**
     * @return \rampage\nexus\entities\ApplicationInstance
     */
    public function getApplication()
    {
        return $this->application;
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



}
