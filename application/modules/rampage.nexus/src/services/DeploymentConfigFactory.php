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

namespace rampage\nexus\services;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Config\Config;
use Zend\Config\Reader\Ini as IniConfigReader;

use SplFileInfo;

/**
 * System Config Factory
 */
class DeploymentConfigFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $pathManager = $serviceLocator->get('PathManager');
        $global = $serviceLocator->get('config');
        $file = new SplFileInfo($pathManager->get('etc', 'rampage-nexus.conf'));
        $config = new Config($global['deployment_config']);

        if ($file->isReadable() && $file->isFile()) {
            $config->merge(new Config((new IniConfigReader())->fromFile($file->getPathname())));
        }

        return $config;
    }
}
