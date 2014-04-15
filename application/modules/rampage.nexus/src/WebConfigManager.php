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

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;
use rampage\core\exception\InvalidServiceTypeException;


class WebConfigManager extends AbstractPluginManager implements ConfigTemplateLocatorAwareInterface
{
    use traits\ConfigTemplateLocatorAwareTrait;

    /**
     * @see \Zend\ServiceManager\AbstractPluginManager::__construct()
     */
    public function __construct(ConfigInterface $configuration = null)
    {
        $this->invokableClasses = array(
            'fpm' => __NAMESPACE__ . '\FPMWebConfig',
            'nginx' => __NAMESPACE__ . '\NginxWebConfig'
        );

        parent::__construct($configuration);
        $this->autoAddInvokableClass = true;
    }

    /**
     * @see \Zend\ServiceManager\AbstractPluginManager::createFromInvokable()
     */
    protected function createFromInvokable($canonicalName, $requestedName)
    {
        $class = $this->invokableClasses[$canonicalName];

        if (!self::isSubclassOf($class, WebConfigInterface::class)) {
            throw new InvalidServiceTypeException(sprintf('The requested service class %s does not implement %s', $class, WebConfigInterface::class));
        }

        return $class::factory($this->creationOptions, $this->configTemplateLocator);
    }

    /**
     * @see \Zend\ServiceManager\AbstractPluginManager::validatePlugin()
     */
    public function validatePlugin($plugin)
    {
        if (!$plugin instanceof WebConfigInterface) {
            throw new InvalidServiceTypeException(sprintf(
                'Expected service to implement %s, %s given',
                WebConfigInterface::class,
                is_object($plugin)? get_class($plugin) : gettype($plugin)
            ));
        }
    }
}
