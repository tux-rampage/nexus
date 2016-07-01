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

namespace Rampage\Nexus\MongoDB;

use Zend\Hydrator\HydratorInterface;


/**
 * The default persistence builder
 */
class DefaultPersistenceBuilder implements PersistenceBuilderInterface
{
    /**
     * @var UnitOfWork
     */
    protected $unitOfWork;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @param UnitOfWork $unitOfWork
     * @param HydratorInterface $hydrator
     */
    public function __construct(UnitOfWork $unitOfWork, HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
        $this->unitOfWork = $unitOfWork;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilderInterface::buildPersist()
     */
    public function buildPersist($object, EntityState &$state)
    {
        // TODO Auto-generated method stub

    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilderInterface::buildRemove()
     */
    public function buildRemove($object)
    {
        // TODO Auto-generated method stub

    }
}
