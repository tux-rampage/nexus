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

namespace Rampage\Nexus\MongoDB\PersistenceBuilder;

use Rampage\Nexus\MongoDB\UnitOfWork;
use Rampage\Nexus\MongoDB\EntityState;
use Rampage\Nexus\Exception\LogicException;
use Rampage\Nexus\MongoDB\InvokableChain;


/**
 * Entity reference persistence builder
 */
class ReferenceBuilder implements AggregateBuilderInterface
{
    const CASCADE_NONE = 0;
    const CASCADE_PERSIST = 1;
    const CASCADE_REMOVE = 2;
    const CASCADE_ALL = 3;

    /**
     * @var PersistenceBuilderInterface
     */
    protected $entityBuilder;

    /**
     * @var UnitOfWork
     */
    protected $unitOfWork;

    /**
     * @var int
     */
    protected $cascade = self::CASCADE_NONE;

    /**
     * @var The entity class name for this builder
     */
    protected $class;

    /**
     * @param PersistenceBuilderInterface $entityBuilder
     */
    public function __construct(PersistenceBuilderInterface $entityBuilder, UnitOfWork $unitOfWork, $class)
    {
        $this->entityBuilder = $entityBuilder;
        $this->unitOfWork = $unitOfWork;
        $this->class = $class;
    }

    /**
     * Set the cascade flags
     *
     * You may combine the cascade option by binary or:
     *
     * ```php
     * $pb->setCascadeOptions(ReferenceBuilder::CASCADE_PERSIST | ReferenceBuilder::CASCADE_REMOVE);
     * ```
     *
     * @param unknown $cascade
     */
    public function setCascadeOptions($cascade)
    {
        $this->cascade = (int)$cascade;
    }

    /**
     * @param object $object
     * @return EntityState
     */
    private function getOrCreateObjectState($object)
    {
        if ($this->unitOfWork->isAttached($object)) {
            $state = $this->unitOfWork->getState($object);
        } else {
            $state = new EntityState(EntityState::STATE_NEW, []);
        }

        return $state;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateBuilderInterface::buildInsertDocument()
     */
    public function buildInsertDocument($object, array &$parent, $property, $prefix, array &$root)
    {
        $state = $this->getOrCreateObjectState($object);

        if ($state->getState() == EntityState::STATE_REMOVED) {
            throw new LogicException('Cannot reference a removed entity');
        }

        if ($this->cascade & self::CASCADE_PERSIST) {
            $callback = $this->entityBuilder->buildPersist($object, $state);
            $parent[$property] = $state->getId();

            $this->unitOfWork->updateState($object, $state);

            return $callback;
        }

        if (!$state->getId()) {
            $path = ($prefix == '')? $property : $prefix . '.' . $property;
            throw new LogicException('Cannot persist entity reference "%s" without id', $path);
        }

        $parent[$property] = $state->getId();
        return null;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateBuilderInterface::buildUndefinedInDocument()
     */
    public function buildUndefinedInDocument($property, $prefix, $stateValue, EntityState $state)
    {
        if (!$stateValue || (($this->cascade & self::CASCADE_REMOVE) == 0)) {
            return null;
        }

        if (!$this->unitOfWork->hasInstanceByIdentifier($this->class, $stateValue)) {
            $path = ($prefix == '')? $property : $prefix . '.' . $property;
            throw new LogicException(sprintf('Cannot remove untracked reference %s in %s', $this->class, $path));
        }

        $object = $this->unitOfWork->getInstanceByIdentifier($this->class, $stateValue);
        $refState = $this->unitOfWork->getState($object);

        if ($refState->getState() == EntityState::STATE_REMOVED) {
            return null;
        }

        $callback = null;

        if ($refState->getState() == EntityState::STATE_PERSISTED) {
            $callback = $this->entityBuilder->buildRemove($object);
        }

        $this->unitOfWork->updateState($object, new EntityState(EntityState::STATE_REMOVED, []));
        return $callback;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateBuilderInterface::buildUpdateDocument()
     */
    public function buildUpdateDocument($object, array &$parent, $property, $prefix, array &$root, EntityState $state)
    {
        $previousId = $parent[$property];
        $state = $this->getOrCreateObjectState($object);
        $callbacks = new InvokableChain();

        if ($previousId != $state->getId()) {
            $removeCallback = $this->buildUndefinedInDocument($property, $prefix, $previousId, $state);

            if ($removeCallback) {
                $callbacks->add($removeCallback);
            }
        }

        $insertCallback = $this->buildInsertDocument($object, $parent, $property, $prefix, $root);
        if ($insertCallback) {
            $callbacks->add($insertCallback);
        }

        return ($callbacks->count())? $callbacks : null;
    }
}
