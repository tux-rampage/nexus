<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @license   LUKA Proprietary
 * @copyright Copyright (c) 2016 LUKA netconsult GmbH (www.luka.de)
 */

namespace Rampage\Nexus\Config;

/**
 * Array config
 *
 * Allows to access even nested array elements as properties.
 */
class ArrayConfig implements PropertyConfigInterface
{
    /**
     * Config data array
     *
     * @var array
     */
    protected $data = [];

    /**
     * Properties cache
     *
     * @var array
     */
    protected $properties = [];

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Flatten the config data into the property value
     *
     * @param   string  $property
     * @return  mixed
     */
    private function flatten($property)
    {
        $parts = explode('.', $property);
        $current = $this->data;

        foreach ($parts as $key) {
            if (!is_array($current) || !isset($current[$key])) {
                return null;
            }

            $current = $current[$key];
        }

        return $current;
    }

    /**
     * Returns the value for the requested property
     *
     * nested properties might be dot separated
     *
     * @param   string  $property
     * @param   mixed   $default
     * @return  mixed
     */
    public function get($property, $default = null)
    {
        if (!array_key_exists($property, $this->properties)) {
            $this->properties[$property] = $this->flatten($property);
        }

        if (!isset($this->properties[$property])) {
            return $default;
        }

        return $this->properties[$property];
    }
}
