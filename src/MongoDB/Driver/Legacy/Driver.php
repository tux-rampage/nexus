<?php
/**
 * Copyright (c) 2016 Axel Helmert
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
 * @copyright Copyright (c) 2016 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\MongoDB\Driver\Legacy;

use Rampage\Nexus\MongoDB\Driver\DriverInterface;
use Rampage\Nexus\MongoDB\Driver\Legacy\Hydration\StrategyProvider;
use Interop\Container\ContainerInterface;

final class Driver implements DriverInterface
{
    /**
     * @var \MongoDB
     */
    private $database = null;

    /**
     * @var string
     */
    private $dbName;

    /**
     * @var \MongoClient
     */
    private $client = null;

    /**
     * @var string
     */
    private $server;

    /**
     * @var array
     */
    private $options;

    /**
     * @var ContainerInterface
     */
    private $strategyProvider;

    /**
     * @param string $server
     * @param string $databaseName
     * @param array $options
     */
    public function __construct($server, $databaseName, array $options = [])
    {
        $this->server = $server;
        $this->options = $options;
        $this->dbName = $databaseName;
        $this->strategyProvider = new Hydration\StrategyProvider();
    }

    /**
     * @return \MongoClient
     */
    protected function connect()
    {
        if (!$this->client) {
            $this->client = new \MongoClient($this->server, $this->options);
        }

        return $this->client;
    }

    /**
     * @param string $name
     * @return \MongoDB
     */
    protected function selectDatabase($name)
    {
        return $this->connect()->selectDB($name);
    }

    /**
     * @param string $name
     * @return \MongoDB
     */
    protected function getDatabase()
    {
        if (!$this->database) {
            $this->database = $this->selectDatabase($this->dbName);
        }

        return $this->database;
    }

    /**
     * @param string $name
     * @param string $database
     * @return \MongoCollection
     */
    public function selectCollection($name, $database = null)
    {
        $db = ($database)? $this->selectDatabase($database) : $this->getDatabase();
        return $db->selectCollection($name);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Driver\DriverInterface::getCollection()
     */
    public function getCollection($name)
    {
        return new Collection($this, $name);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Driver\DriverInterface::getTypeHydrationStrategy()
     */
    public function getTypeHydrationStrategy($type)
    {
        return $this->strategyProvider->get($type);
    }
}
