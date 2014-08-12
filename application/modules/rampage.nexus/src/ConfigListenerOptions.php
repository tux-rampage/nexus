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

use ReflectionObject;
use CallbackFilterIterator;
use DirectoryIterator;
use Phar;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\EventManagerInterface;

use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\Listener\ConfigListener as ZendConfigListener;


class ConfigListenerOptions implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * @return string[]
     */
    public static function getGlobPaths($confDir = null)
    {
        $confDir = $confDir? : APPLICATION_DIR . 'config';
        $local = APPLICATION_DEVELOPMENT? 'dev' : 'local';

        return array(
            $confDir . '/conf.d/{,*.}{global,' . $local . '}.php',
            RAMPAGE_PREFIX . '/etc/conf.d/*.conf',
            '/etc/rampage-nexus/conf.d/*.conf',
        );
    }

    /**
     * @return string[]
     */
    public static function getStaticPaths()
    {
        $paths = array(
            RAMPAGE_PREFIX . '/etc/rampage-nexus.conf',
            '/etc/rampage-nexus/deployment.conf',
        );

        $existingFileFilter = function($file) {
            return file_exists($file) && is_readable($file);
        };

        return array_filter($paths, $existingFileFilter);
    }

    /**
     * {@inheritdoc}
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULES, array($this, 'onLoadModules'), 100);
    }

    /**
     * @return string[]
     */
    protected function addFiles($paths)
    {
        $dirIterator = new DirectoryIterator(APPLICATION_DIR . 'config/conf.d/');
        $local = APPLICATION_DEVELOPMENT? 'dev' : 'local';
        $prepend = array();
        $filters = array(
            '^global\.php$',
            '^' . $local . '\.php$',
            '\.global\.php$',
            '\.' . $local . '\.php$',
        );

        foreach ($filters as $regex) {
            $fileFilter = function(\SplFileInfo $file) use ($regex) {
                return (bool)preg_match("~$regex~", $file->getFilename());
            };

            foreach ((new CallbackFilterIterator($dirIterator, $fileFilter)) as $file) {
                $prepend[] = array(
                    'type' => ConfigListenerOptions::STATIC_PATH,
                    'path' => $file->getPathname()
                );
            }
        }

        return array_merge($prepend, $paths);
    }

    /**
     * @param ModuleEvent $event
     */
    public function onLoadModules(ModuleEvent $event)
    {
        $this->addConfigs($event->getConfigListener());
    }

    /**
     * @param ZendConfigListener $listener
     */
    public function addConfigs(ZendConfigListener $listener)
    {
        if (!Phar::running()) {
            return;
        }

        $reflection = new ReflectionObject($listener);
        $property = $reflection->getProperty('paths');
        $property->setAccessible(true);

        $orig = $property->getValue($listener);
        $paths = $this->addFiles($orig);

        $property->setValue($listener, $paths);
    }
}
