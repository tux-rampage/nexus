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

namespace Rampage\Nexus\Persistence;

use Rampage\Nexus\Exception\InvalidArgumentException;
use Rampage\Nexus\Persistence\Predicate\PredicateSet;

/**
 * Persistence query
 */
class Query
{
    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'desc';

    /**
     * @var string
     */
    protected $entity;

    /**
     * @var Predicate\PredicateSet
     */
    protected $predicates;

    /**
     * @var int
     */
    protected $limit = null;

    /**
     * @var int
     */
    protected $offset = null;

    /**
     * @var string[]
     */
    protected $orders = [];

    /**
     * @param string $entity
     */
    public function __construct($entity = null)
    {
        if ($entity !== null) {
            $this->setEntity($entity);
        }

        $this->predicates = new PredicateSet();
    }

    /**
     * Set the entity name to query
     *
     * @param   string  $entity The class name of the entity
     * @return  self
     *
     * @throws  InvalidArgumentException
     */
    public function setEntity($entity)
    {
        if (!is_string($entity) || ($entity == '')) {
            throw new InvalidArgumentException('Invalid entity for query: ' . (is_string($entity)? $entity : '[' . gettype($entity) . ']'));
        }

        $this->entity = $entity;
        return $this;
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return number
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param number $limit
     * @return self
     */
    public function setLimit($limit)
    {
        $this->limit = ($limit && $limit > 0)? (int)$limit : null;
        return $this;
    }

    /**
     * @return number
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param number $offset
     * @return self
     */
    public function setOffset($offset)
    {
        $this->offset = (($offset !== null) && ($offset >= 0))? (int)$offset : null;
        return $this;
    }

    /**
     * Limit the result set
     *
     * @param int $limit
     * @param int $offset
     * @return self
     */
    public function limit($limit, $offset = null)
    {
        $this->setLimit($limit);
        $this->setOffset($offset);

        return $this;
    }

    /**
     * @param Predicate\PredicateInterface $predicate
     * @return self
     */
    public function addPredicate(Predicate\PredicateInterface $predicate)
    {
        $this->predicates->addPredicate($predicate);
        return $this;
    }

    /**
     * @return multitype:\Rampage\Nexus\Persistence\Predicate\PredicateInterface
     */
    public function getPredicates()
    {
        return $this->predicates;
    }

    /**
     * @return multitype:\Rampage\Nexus\Persistence\string
     */
    public function getOrders()
    {
        return $this->orders;
    }
}
