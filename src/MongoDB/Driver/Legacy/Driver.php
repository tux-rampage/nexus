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

class Driver implements DriverInterface
{
    /**
     * @var \MongoDB
     */
    private $database;

    /**
     * @var string
     */
    private $dbName;

    /**
     * @var \MongoClient
     */
    private $client;

    public function __construct($server, $databaseName, array $options = null)
    {
        $this->client = new \MongoClient($server, $options);
        $this->dbName = $databaseName;
    }

    /**
     * @param string $name
     * @return \MongoDB
     */
    protected function selectDatabase($name)
    {
        return $this->client->selectDB($name);
    }

    /**
     * @param string $name
     * @return \MongoDB
     */
    protected function getDatabase()
    {
        if (!$this->database) {
            $this->database = $this->client->selectDB($this->dbName);
        }

        return $this->database;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Driver\DriverInterface::getCollection()
     */
    public function getCollection($name)
    {
        return new Collection($name, $this->getDatabase());
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Driver\DriverInterface::getTypeHydrationStrategy()
     */
    public function getTypeHydrationStrategy($type)
    {
        if ($type == 'id') {
            return new Hydration\IdStartegy();
        }
    }
}