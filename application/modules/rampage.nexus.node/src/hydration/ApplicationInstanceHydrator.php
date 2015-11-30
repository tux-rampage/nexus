<?php
/**
 * Copyright (c) 2015 Axel Helmert
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
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\node\hydration;

use Zend\Hydrator\Reflection as ReflectionHydrator;
use Zend\Hydrator\HydrationInterface;
use rampage\nexus\entities\ApplicationInstance;
use rampage\nexus\entities\ApplicationPackage;
use Zend\Hydrator\Strategy\ClosureStrategy;
use rampage\nexus\exceptions\LogicException;


class ApplicationInstanceHydrator implements HydrationInterface
{
    /**
     * @var ReflectionHydrator
     */
    protected $reflectionHydrator;


    public function __construct()
    {
        $this->reflectionHydrator = new ReflectionHydrator();
        $packageStrategy = new ClosureStrategy(null, function($data) {
            return $this->hydratePackage($data);
        });

        $this->reflectionHydrator
            ->addStrategy('package', $packageStrategy)
            ->addStrategy('previousPackage', $packageStrategy);
    }

    /**
     * @param array $data
     * @return ApplicationPackage
     */
    protected function hydratePackage($data)
    {
        if (!is_array($data)) {
            return null;
        }

        $package = new ApplicationPackage();
        unset($data['file']);

        $this->reflectionHydrator->hydrate($data, $package);
        return $package;
    }

    /**
     * @see \Zend\Hydrator\HydrationInterface::hydrate()
     */
    public function hydrate(array $data, $object = null)
    {
        if ($object && !($object instanceof ApplicationInstance)) {
            throw new LogicException(sprintf(
                '%s can only hydrate %s objects, %s given',
                __CLASS__, ApplicationInstance::class,
                is_object($object)? get_class($object) : gettype($object)
            ));
        } else if (!$object) {
            $object = new ApplicationInstance();
        }

        unset($data['target']);
        $this->reflectionHydrator->hydrate($data, $object);

        return $object;
    }
}
