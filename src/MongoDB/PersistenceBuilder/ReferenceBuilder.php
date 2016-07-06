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
    use PropertyPathTrait;

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
    public function buildInsertDocument($object, $property, $prefix, InvokableChain $actions)
    {
        $state = $this->getOrCreateObjectState($object);

        if ($state->getState() == EntityState::STATE_REMOVED) {
            throw new LogicException('Cannot reference a removed entity');
        }

        if ($this->cascade & self::CASCADE_PERSIST) {
            $callback = $this->entityBuilder->buildPersist($object, $state);

            $actions->add($callback);
            $this->unitOfWork->updateState($object, $state);
        }

        if (!$state->getId()) {
            throw new LogicException(sprintf('Cannot persist entity reference to "%s" without id', $this->class));
        }

        return $state->getId();
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateBuilderInterface::buildUndefinedInDocument()
     */
    public function buildUndefinedInDocument($property, $prefix, $stateValue, InvokableChain $actions)
    {
        if (!$stateValue || (($this->cascade & self::CASCADE_REMOVE) != self::CASCADE_REMOVE)) {
            return;
        }

        if (!$this->unitOfWork->hasInstanceByIdentifier($this->class, $stateValue)) {
            throw new LogicException(sprintf('Cannot remove untracked reference %s', $this->class));
        }

        $object = $this->unitOfWork->getInstanceByIdentifier($this->class, $stateValue);
        $refState = $this->unitOfWork->getState($object);

        if ($refState->getState() == EntityState::STATE_REMOVED) {
            return;
        }

        if ($refState->getState() == EntityState::STATE_PERSISTED) {
            $actions->add($this->entityBuilder->buildRemove($object));
        }

        $this->unitOfWork->updateState($object, new EntityState(EntityState::STATE_REMOVED, []), $this->class);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\PersistenceBuilder\AggregateBuilderInterface::buildUpdateDocument()
     */
    public function buildUpdateDocument($object, $property, $prefix, &$stateValue, InvokableChain $actions)
    {
        $previousId = $stateValue;
        $state = $this->getOrCreateObjectState($object);

        if ($previousId != $state->getId()) {
            $this->buildUndefinedInDocument($property, $prefix, $previousId, $actions);
        }

        $id = $this->buildInsertDocument($object, $property, $prefix, $actions);
        $stateValue = $id;

        return [
            '$set' => [
                $this->prefixPropertyPath($property, $prefix) => $id
            ]
        ];
    }
}
