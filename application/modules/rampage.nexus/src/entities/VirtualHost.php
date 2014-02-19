<?php
/**
 * This is part of rampage-nexus
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
 * Virtual host definition
 *
 * @orm\Entity
 * @orm\Table(
 *      name="virtual_host",
 *      uniqueConstraints={
 *          @orm\UniqueConstraint(name="UNIQ_VHOST", columns={"server_name", "port"})
 *      }
 * )
 */
class VirtualHost
{
    const DEFAULT_VHOST = '__default__';

    /**
     * @orm\Id @orm\Column(type="integer") @orm\GeneratedValue
     * @var int
     */
    protected $id = null;

    /**
     * @orm\Column(type="string", nullable=false)
     * @var string
     */
    protected $serverName = self::DEFAULT_VHOST;

    /**
     * @orm\Column(type="text", nullable=true)
     * @var string
     */
    protected $aliases = null;

    /**
     * @orm\Column(type="integer", nullable=false)
     * @var int
     */
    protected $port = 80;

    /**
     * @orm\Column(type="integer", nullable=true)
     * @var int
     */
    protected $sslPort = null;

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
    public function getServerName()
    {
        return $this->serverName;
    }

    /**
     * @return array|null
     */
    public function getAliases()
    {
        if ($this->aliases === null) {
            return $this->aliases;
        }

        $aliases = explode("\n", (string)$this->aliases);
        $aliases = array_map('trim', $aliases);
        $aliases = array_filter($aliases);

        return $aliases;
    }

    /**
     * @return number
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return number
     */
    public function getSslPort()
    {
        return $this->sslPort;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setServerName($name)
    {
        $this->serverName = $name;
        return $this;
    }

    /**
     * @param string $aliases
     * @return self
     */
    public function setAliases($aliases)
    {
        if (is_array($aliases)) {
            $aliases = implode("\n", $aliases);
        }

        $aliases = trim((string)$aliases);
        if ($aliases == '') {
            $aliases = null;
        }

        $this->aliases = $aliases;
        return $this;
    }

    /**
     * @param number $port
     * @return self
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @param number $port
     * @return self
     */
    public function setSslPort($port)
    {
        $this->sslPort = $port;
        return $this;
    }
}
