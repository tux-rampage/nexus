<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @license   LUKA Proprietary
 * @copyright Copyright (c) 2016 LUKA netconsult GmbH (www.luka.de)
 */

namespace Rampage\Nexus\MongoDB\Mapping;

/**
 * Interface for mapping providers
 */
interface MappingProviderInterface
{
    /**
     * Returns the class mapping
     *
     * @param   string                  $class  The class name
     * @return  ClassMappingInterface           The mapping information
     */
    public function getClassMapping($class);
}
