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

namespace rampage\nexus\api;

use rampage\core\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface as ServiceConfigInterface;

/**
 * @method \rampage\nexus\api\ServerApiInterface get($name, $options = array(), $usePeeringServiceManagers = true)
 */
class ServerApiManager extends AbstractPluginManager
{
    /**
     * @see \Zend\ServiceManager\AbstractPluginManager::__construct()
     */
    public function __construct(ServiceConfigInterface $configuration = null)
    {
        $this->invokableClasses = array(
            'rampage' => RampageServerApi::class,
        );

        $this->shareByDefault = false;
        $this->autoAddInvokableClass = true;

        parent::__construct($configuration);
    }

    /**
     * @see \Zend\ServiceManager\AbstractPluginManager::validatePlugin()
     */
    public function validatePlugin($plugin)
    {
        if (!$plugin instanceof ServerApiInterface) {
            throw new \UnexpectedValueException('Invalid server API');
        }
    }
}
