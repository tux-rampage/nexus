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

namespace Rampage\Nexus\MongoDB\Hydration;

use Zend\Hydrator\Reflection as BaseReflectionHydrator;
use Zend\Hydrator\NamingStrategy\ArrayMapNamingStrategy;


/**
 * Reflection hydrator
 *
 * This works basically as Zend's reflection hydrator with the following differences
 *
 *  * Property names used for strategies and filters will be looked up using the
 *    class property names consistently in `hydrate()` and `extract()`
 *  * `hydrate()` will also filter the properties being hydrated by using
 *    the filters added with `addFilter()`, just like `extract()`.
 */
class ReflectionHydrator extends BaseReflectionHydrator
{
    /**
     * The filter index for allowed properties
     */
    const FILTER_ALLOWED_PROPERTIES = 'allowedProperties';

    /**
     * @var callable[]
     */
    protected $hydrationInterceptors = [];

    /**
     * Specifies the properties that allow null values to be persisted
     *
     * @var array
     */
    protected $nullableProperties = [];

    /**
     * @var array
     */
    protected $properties = null;

    /**
     * Creates the hydrator with a default property map
     *
     * __Example:__
     *
     * ```php
     * $hydrator = new ReflectionHydrator([
     *      'foo',          // Allow foo
     *      'bar' => 'baz'  // Allow bar and extract to/hydrate from key baz
     * ]);
     * ```
     *
     * @param   string[]    $properties The properties that are allowed to be hydrated/extracted
     *                                  this may also be a mapping or a combination of mapping/listing
     */
    public function __construct(array $properties = null, $identifier = 'id')
    {
        if ($properties !== null) {
            $map = $this->buildPropertyMap($properties);
            $map[$identifier] = '_id';

            $this->properties = array_keys($map);
            $this->addFilter(self::FILTER_ALLOWED_PROPERTIES, function($property) use ($map) {
                return array_key_exists($property, $map);
            });
        } else {
            $map = [ $identifier => '_id' ];
        }

        $this->setNamingStrategy(new ArrayMapNamingStrategy($map));
        parent::__construct();
    }

    /**
     * @param callable $interceptor
     * @return self
     */
    public function addHydrationInterceptor(callable $interceptor)
    {
        $this->hydrationInterceptors[] = $interceptor;
        return $this;
    }

    /**
     * Provides a fluent interface
     *
     * @param string $property
     * @return self
     */
    public function addNullableProperty($property)
    {
        $this->nullableProperties[$property] = $property;
        return $this;
    }

    /**
     * Build a usable property map
     *
     * @param array $properties
     * @return string[]
     */
    protected function buildPropertyMap(array $properties)
    {
        $map = [];

        foreach ($properties as $property => $key) {
            if (is_int($property)) {
                $map[$key] = $key;
                continue;
            }

            $map[$property] = $key;
        }

        return $map;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Hydrator\Reflection::extract()
     */
    public function extract($object)
    {
        $result = [];

        foreach (self::getReflProperties($object) as $property) {
            $propertyName = $property->getName();
            $key = $this->extractName($propertyName, $object);

            if (!$this->filterComposite->filter($propertyName)) {
                continue;
            }

            $value = $property->getValue($object);
            $extracted = $this->extractValue($propertyName, $value, $object);

            if (($extracted !== null) || isset($this->nullableProperties[$propertyName])) {
                $result[$key] = $extracted;
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Hydrator\Reflection::hydrate()
     */
    public function hydrate(array $data, $object)
    {
        foreach ($this->hydrationInterceptors as $interceptor) {
            $interceptor($data, $object);
        }

        $reflProperties = self::getReflProperties($object);
        $propertyNames = $this->properties? : array_keys($reflProperties);
        $propertyNames = array_combine($propertyNames, $propertyNames);

        foreach ($data as $key => $value) {
            $name = $this->hydrateName($key, $data);
            unset($propertyNames[$name]);

            if (!$this->filterComposite->filter($name) || !isset($reflProperties[$name])) {
                continue;
            }

            $reflProperties[$name]->setValue($object, $this->hydrateValue($name, $value, $data));
        }

        foreach ($propertyNames as $name) {
            if (!$this->filterComposite->filter($name) || !isset($reflProperties[$name])) {
                continue;
            }

            $reflProperties[$name]->setValue($object, $this->hydrateValue($name, null, $data));
        }

        return $object;
    }
}
