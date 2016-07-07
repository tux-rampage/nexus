<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @license   LUKA Proprietary
 * @copyright Copyright (c) 2016 LUKA netconsult GmbH (www.luka.de)
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
    public function __construct(array $properties = null)
    {
        if ($properties !== null) {
            $map = $this->buildPropertyMap($properties);

            $this->setNamingStrategy(new ArrayMapNamingStrategy($map));
            $this->addFilter(self::FILTER_ALLOWED_PROPERTIES, function($property) use ($map) {
                return array_key_exists($property, $map);
            });
        }

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
            $result[$key] = $this->extractValue($propertyName, $value, $object);
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

        foreach ($data as $key => $value) {
            $name = $this->hydrateName($key, $data);

            if (!$this->filterComposite->filter($name) || !isset($reflProperties[$name])) {
                continue;
            }

            $reflProperties[$name]->setValue($object, $this->hydrateValue($name, $value, $data));
        }

        return $object;
    }
}
