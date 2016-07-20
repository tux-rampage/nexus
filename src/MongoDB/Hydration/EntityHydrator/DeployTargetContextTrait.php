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

use Rampage\Nexus\MongoDB\Hydration\EmbeddedStrategy;
use Rampage\Nexus\Exception\LogicException;
use Rampage\Nexus\Deployment\DeployTargetInterface;

/**
 * Trait to extract the deploy target context from the hydration data
 */
trait DeployTargetContextTrait
{
    /**
     * @param array $data
     * @return DeployTargetInterface
     */
    private function getContext(array &$data)
    {
        if (!isset($data[EmbeddedStrategy::ROOT_CONTEXT_KEY]) || !($data[EmbeddedStrategy::ROOT_CONTEXT_KEY] instanceof DeployTargetInterface)) {
            throw new LogicException(__CLASS__ . ' requires a root context of type ' . DeployTargetInterface::class . ' for hydration');
        }

        return $data[EmbeddedStrategy::ROOT_CONTEXT_KEY];
    }
}
