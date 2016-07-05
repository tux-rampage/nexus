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

use Zend\Hydrator\HydratorInterface;


/**
 * Trait for implementing persistence builders
 */
trait PersistenceBuilderTrait
{
    use PropertyPathTrait;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @var Driver\CollectionInterface
     */
    protected $collection;

    /**
     * @var AggregateBuilderInterface[]
     */
    protected $aggregationProperties = [];

    /**
     * @var array
     */
    protected $mappedRefProperties = [];

    /**
     * @var array
     */
    protected $discriminatorMap = [];

    /**
     * @var string
     */
    protected $discriminatorField = null;

    /**
     * @return mixed
     */
    protected function createIdentity()
    {
        return $this->collection->createIdValue();
    }

    /**
     * Define a property as aggregate
     *
     * @param string $property
     * @param PersistenceBuilderInterface $persistenceBuilder
     *
     * @return self
     */
    public function setAggregatedProperty($property, AggregateBuilderInterface $persistenceBuilder)
    {
        $this->aggregationProperties[$property] = $persistenceBuilder;
        return $this;
    }

    /**
     * Define a non-owning side property
     *
     * This property will not be persisted.
     *
     * @param string $property
     * @return self
     */
    public function addMappedRefProperty($property)
    {
        $this->mappedRefProperties[$property] = $property;
        return $this;
    }

    /**
     * @param string $discriminatorField
     * @return self
     */
    public function setDiscriminatorField($discriminatorField)
    {
        $this->discriminatorField = ($discriminatorField !== null)? (string)$discriminatorField : null;
        return $this;
    }

    /**
     * Set the discriminator map
     *
     * @param   string[string]    $map  The mapping of class name to discriminator value
     */
    public function setDiscriminatorMap(array $map)
    {
        $this->discriminatorMap = [];

        foreach ($map as $class => $value) {
            $this->addToDiscriminatorMap($class, $value);
        }

        return $this;
    }

    /**
     * Add a class to the discriminator map
     *
     * @param string $class
     * @param string $value
     * @return self
     */
    public function addToDiscriminatorMap($class, $value)
    {
        $this->discriminatorMap[$class] = (string)$value;
        return $this;
    }

    /**
     * @param string $property
     * @return boolean
     */
    protected function isExcludedFromDiff($property)
    {
        return (isset($this->mappedRefProperties[$property]) || isset($this->aggregationProperties[$property]));
    }

    /**
     * Maps the object class to the discriminator value
     *
     * @param object $object
     * @return string
     */
    protected function mapTypeToDiscriminator($object)
    {
        $class = get_class($object);

        if (!isset($this->discriminatorMap[$class])) {
            return $class;
        }

        return $this->discriminatorMap[$class];
    }
}
