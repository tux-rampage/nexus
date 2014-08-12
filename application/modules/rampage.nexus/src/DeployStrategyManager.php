<?php
/**
 * Copyright (c) 2014 Axel Helmert
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
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus;

use rampage\core\AbstractPluginManager;
use rampage\core\exception\InvalidPluginException;
use Zend\ServiceManager\ConfigInterface;

class DeployStrategyManager extends AbstractPluginManager
{
    /**
     * @see \Zend\ServiceManager\AbstractPluginManager::__construct()
     */
    public function __construct(ConfigInterface $config = null)
    {
        parent::__construct($config);

        if (!$this->has('default')) {
            $this->setInvokableClass('default', DefaultDeployStrategy::class);
        }
    }

    /**
     * @see \Zend\ServiceManager\AbstractPluginManager::validatePlugin()
     */
    public function validatePlugin($plugin)
    {
        if (!$plugin instanceof DeployStrategyInterface) {
            throw new InvalidPluginException(sprintf(
                'The plugin strategy mus implement rampage\nexus\DeployStrategyInterface, %s given',
                is_object($plugin)? get_class($plugin) : gettype($plugin)
            ));
        }
    }
}
