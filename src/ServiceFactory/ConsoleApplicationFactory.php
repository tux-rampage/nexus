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

namespace Rampage\Nexus\ServiceFactory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Symfony\Component\Console\Application;
use Rampage\Nexus\Version;


class ConsoleApplicationFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     */
    protected function getCommands(ContainerInterface $container)
    {
        if (!$container->has('config')) {
            return [];
        }

        $config = $container->get('config');

        if (!isset($config['commands']) || (!is_array($config['commands']) && !($config['commands'] instanceof \Traversable))) {
            return [];
        }

        return $config['commands'];
    }

    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        $application = new Application('Rampage Nexus - PHP Deployment', Version::getVersion());

        foreach ($this->getCommands($container) as $command) {
            if (is_string($command)) {
                $command = $container->get($command);
            }

            $application->add($command);
        }

        return $application;
    }
}
