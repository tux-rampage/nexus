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

use rampage\nexus\exceptions;
use rampage\nexus\entities\ApplicationInstance;

use Zend\Stdlib\Hydrator\AbstractHydrator;
use rampage\nexus\entities\ApplicationPackage;


class LocalStateHydrator extends AbstractHydrator
{
    protected $idReflection;

    protected $packageIdReflection;

    public function __construct()
    {
        $reflection = new \ReflectionClass(ApplicationInstance::class);
        $this->idReflection = $reflection->getProperty('id');
        $this->idReflection->setAccessible(true);

        $this->packageIdReflection = (new \ReflectionClass(ApplicationPackage::class))->getProperty('id');
        $this->packageIdReflection->setAccessible(true);
    }

    /**
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::extract()
     */
    public function extract($object)
    {
        if (!$object instanceof ApplicationInstance) {
            throw new exceptions\InvalidArgumentException(sprintf(
                '%s can only extract %s, received %s.',
                static::class,
                ApplicationInstance::class,
                is_object($object)? get_class($object) : gettype($object)
            ));
        }

        $data = [
            'id' => $this->idReflection->getValue($object),
            'name' => $object->getName(),
            'path' => $object->getPath(),
            'state' => $object->getState(),
            'userParameters' => $object->getUserParameters(),
            'packageId' => $object->getPackage()->getId(),
        ];


        return $data;
    }

    /**
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::hydrate()
     */
    public function hydrate(array $data, $object)
    {
        if (!$object instanceof ApplicationInstance) {
            throw new exceptions\InvalidArgumentException(sprintf(
                '%s expects an instance of %s to hydrate to, received %s.',
                static::class,
                ApplicationInstance::class,
                is_object($object)? get_class($object) : gettype($object)
            ));
        }

        if (!isset($data['id']) || !$data['id']) {
            throw new exceptions\LogicException('Cannot hydrate application instance without id');
        }

        $this->idReflection->setValue($object, $data['id']);
        $object->setName($data['name'])
            ->setPath($data['path'])
            ->setState($data['state'])
            ->setUserParameters($data['userParameters']);

        if ($data['packageId']) {
            $package = new ApplicationPackage();
            $this->packageIdReflection->setValue($package, $data['packageId']);
        }

        return $object;
    }
}