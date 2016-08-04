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

use Rampage\Nexus\MongoDB\Driver\CollectionInterface;

class Collection implements CollectionInterface
{
    /**
     * @var \MongoCollection
     */
    private $mongoCollection = null;

    /**
     * @var Driver
     */
    private $driver;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     * @param \MongoDB $database
     */
    public function __construct(Driver $driver, $name)
    {
        $this->name = $name;
        $this->driver = $driver;
    }

    /**
     * @return \MongoCollection
     */
    private function getMongoCollection()
    {
        if (!$this->mongoCollection) {
            $this->mongoCollection = $this->driver->selectCollection($this->name);
        }

        return $this->mongoCollection;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Driver\CollectionInterface::createIdValue()
     */
    public function createIdValue()
    {
        return new \MongoId();
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Driver\CollectionInterface::find()
     */
    public function find(array $query, array $fields = null, $limit = null, $skip = null, array $order = null)
    {
        $cursor = $this->getMongoCollection()->find($query, $fields);

        if ($skip) {
            $cursor->skip($skip);
        }

        if ($limit) {
            $cursor->limit($limit);
        }

        if ($order) {
            $cursor->sort($order);
        }

        return $cursor;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Driver\CollectionInterface::findOne()
     */
    public function findOne(array $query, array $fields = null, array $order = null)
    {
        return $this->getMongoCollection()->findOne($query, $fields);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Driver\CollectionInterface::getName()
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Driver\CollectionInterface::insert()
     */
    public function insert(array $document, $upsert = false)
    {
        $this->getMongoCollection()->insert($document);
        return $document['_id'];
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Driver\CollectionInterface::remove()
     */
    public function remove(array $query, $multiple = false)
    {
        $this->getMongoCollection()->remove($query, [
            'justOne' => !$multiple
        ]);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Driver\CollectionInterface::update()
     */
    public function update(array $query, array $document, $multiple = false, $upsert = false)
    {
        $this->getMongoCollection()->update($query, $document, [
            'multiple' => $multiple,
            'upsert' => $upsert
        ]);
    }
}
