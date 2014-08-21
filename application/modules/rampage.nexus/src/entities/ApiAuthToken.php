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

use DateTime;


class ApiAuthToken
{
    /**
     * @orm\Id @orm\Column(type="string")
     * @var string
     */
    private $id = null;

    /**
     * @orm\Column(type="string", nullable=false)
     * @var string
     */
    protected $userAgent = null;

    /**
     * @orm\Column(type="datetime", nullable=false)
     * @var DateTime
     */
    protected $validTo = null;

    /**
     * @orm\ManyToOne(targetEntity="Server")
     * @JoinColumn(name="server_id", referencedColumnName="id", nullable=true)
     *
     * @var Server
     */
    protected $server = null;

    /**
     * @param string $userAgent
     */
    public function __construct($userAgent = null, Server $server)
    {
        $this->userAgent = $userAgent;

        if ($userAgent) {
            $this->id = $this->randomString();
            $this->validTo = new DateTime('+1 hour');
        }
    }

    /**
     * @param number $size
     * @return string
     */
    protected function randomString($size = 32)
    {
        $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789._-';
        $max  = strlen($pool) - 1;
        $result = '';

        while (strlen($result) < $size) {
            $pos = rand(0, $max);
            $result .= substr($pool, $pos, 1);
        }

        return $result;
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
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @return DateTime
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * @return Server
     */
    public function getServer()
    {
        return $this->server;
    }
}
