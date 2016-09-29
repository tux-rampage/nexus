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

namespace Rampage\Nexus\OAuth2\MongoDB\Hydration;

use Rampage\Nexus\MongoDB\Hydration\ReflectionHydrator;
use Rampage\Nexus\MongoDB\Driver\DriverInterface;

/**
 * User hydrator implementation
 */
final class UserHydrator extends ReflectionHydrator
{
    public function __construct(DriverInterface $driver)
    {
        parent::__construct([
            'password',
            'name',
            'email'
        ]);

        $this->addStrategy('*', $driver->getTypeHydrationStrategy(DriverInterface::STRATEGY_STRING));
    }
}