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

namespace Rampage\Nexus\MongoDB\Hydration\EntityHydrator;

use Rampage\Nexus\Entities\ApplicationInstance;
use Rampage\Nexus\Exception\LogicException;
use Rampage\Nexus\Package\PackageInterface;
use Zend\Hydrator\Strategy\StrategyInterface;


/**
 * Package reference hydration strategy
 *
 * This startegy is intented to be used for `ApplicationInstance` enities
 */
final class PackageStrategy implements StrategyInterface
{
    /**
     * @param array $data
     * @return ApplicationInstance
     */
    private function getContext(array &$data)
    {
        if (!isset($data[ApplicationInstanceHydrator::HYDRATION_CONTEXT_KEY]) || !($data[ApplicationInstanceHydrator::HYDRATION_CONTEXT_KEY] instanceof ApplicationInstance)) {
            throw new LogicException(__CLASS__ . ' requires a context of type ' . ApplicationInstance::class . ' for hydration');
        }

        return $data[ApplicationInstanceHydrator::HYDRATION_CONTEXT_KEY];
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Hydrator\Strategy\StrategyInterface::extract()
     */
    public function extract($value)
    {
        if ($value instanceof PackageInterface) {
            return $value->getId();
        }

        return null;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Hydrator\Strategy\StrategyInterface::hydrate()
     */
    public function hydrate($value, array &$data = [])
    {
        if ($value === null) {
            return null;
        }

        $context = $this->getContext($data);
        return $context->getApplication()->findPackage($value);
    }
}
