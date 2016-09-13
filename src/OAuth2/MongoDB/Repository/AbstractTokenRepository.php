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

namespace Rampage\Nexus\OAuth2\MongoDB\Repository;

use Rampage\Nexus\MongoDB\Driver\DriverInterface;
use Rampage\Nexus\MongoDB\Driver\CollectionInterface;
use Zend\Hydrator\Strategy\StrategyInterface;

/**
 * Abstract token repository implementation
 */
abstract class AbstractTokenRepository
{
    /**
     * @var CollectionInterface
     */
    protected $collection;

    /**
     * @var StrategyInterface
     */
    protected $dateStrategy;

    /**
     * @param DriverInterface $driver
     */
    public function __construct(DriverInterface $driver)
    {
        $this->collection = $driver->getCollection($this->getCollectionName());
        $this->dateStrategy = $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_DATE);
    }

    /**
     * Must return the mongo db collection name
     *
     * @return string
     */
    abstract protected function getCollectionName();

    /**
     * Revoke the given token id
     *
     * @param string $id
     */
    protected function revokeById($id)
    {
        $this->collection->update(['_id' => $id], [
            '$set' => [
                'revoked' => true
            ]
        ]);
    }

    /**
     * Check revocation
     *
     * @param string $id
     * @return boolean
     */
    protected function isRevoked($id)
    {
        $data = $this->collection->findOne(['_id' => $id]);
        return ($data && $data['revoked']);
    }
}
