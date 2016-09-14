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

namespace Rampage\Nexus\BuildSystem\Jenkins\Repository;

use Rampage\Nexus\Config\PropertyConfigInterface;
use Rampage\Nexus\BuildSystem\Jenkins\PackageScanner\InstanceConfig;
use Rampage\Nexus\BuildSystem\Jenkins\BuildNotification;

/**
 * Implements the instance repository from runtime config
 */
final class ConfiguredInstancesRepository implements InstanceRepositoryInterface
{
    /**
     * @var PropertyConfigInterface
     */
    private $properties;

    /**
     * @var InstanceConfig[]
     */
    private $instances = [];

    /**
     * @param PropertyConfigInterface $config
     */
    public function __construct(PropertyConfigInterface $config)
    {
        $this->properties = $config;
        $this->buildInstances();
    }

    /**
     * Check traversability
     *
     * @param unknown $var
     * @return boolean
     */
    private function isTraversable($var)
    {
        return (is_array($var) || ($var instanceof \Traversable));
    }

    /**
     * Builds the instance configs
     */
    private function buildInstances()
    {
        $instances = $this->properties->get('jenkins.instances', []);

        if (!$this->isTraversable($instances)) {
            return;
        }

        foreach ($instances as $key => $data) {
            if (!is_array($data) || !isset($data['url'])) {
                continue;
            }

            $key = (string)$key;
            $config = new InstanceConfig($key, $data['url']);
            $this->instances[$key] = $config;

            foreach (['include', 'exclude'] as $type) {
                $method = $type . 'Project';

                if (isset($data[$type]) && $this->isTraversable($data[$type])) {
                    foreach ($data[$type] as $name) {
                        if (!is_string($name)) {
                            continue;
                        }

                        $config->$method($name);
                    }
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\BuildSystem\Jenkins\Repository\InstanceRepositoryInterface::find()
     */
    public function find($key)
    {
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\BuildSystem\Jenkins\Repository\InstanceRepositoryInterface::findAll()
     */
    public function findAll()
    {
        return $this->instances;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\BuildSystem\Jenkins\Repository\InstanceRepositoryInterface::findByBuildNotification()
     */
    public function findByBuildNotification(BuildNotification $notification)
    {
        $normalizedUrl = rtrim($notification->getJenkinsUrl(), '/');
        $matched = [];

        foreach ($this->instances as $instance) {
            if (rtrim($instance->getJenkinsUrl(), '/') == $normalizedUrl) {
                $matched[] = $instance;
            }
        }

        return $matched;
    }
}
