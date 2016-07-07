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

namespace Rampage\Nexus\MongoDB\Driver\Legacy\Hydration;

use Rampage\Nexus\MongoDB\Driver\Legacy\Driver;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\Hydrator\Strategy\StrategyInterface;


/**
 * The default strategy provider
 */
class StrategyProvider extends AbstractPluginManager
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\AbstractPluginManager::__construct()
     */
    public function __construct()
    {
        $this->instanceOf = StrategyInterface::class;
        parent::__construct(null, [
            'invokables' => [
                Driver::STRATEGY_ID => IdStartegy::class,
                Driver::STRATEGY_DATE => DateStrategy::class,
                Driver::STRATEGY_DYNAMIC => DynamicStrategy::class,
                Driver::STRATEGY_STRING => StringStrategy::class
            ]
        ]);
    }
}
