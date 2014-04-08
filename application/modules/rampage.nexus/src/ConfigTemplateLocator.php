<?php
/**
 * This is part of rampage-nexus
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

use Zend\Stdlib\SplPriorityQueue;
use SplFileInfo;
use RuntimeException;

class ConfigTemplateLocator
{
    protected $paths = null;

    /**
     * @var entities\ApplicationInstance
     */
    protected $application = null;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->paths = new SplPriorityQueue();
        $this->addPath(dirname(__DIR__) . '/resource/config-teplates');
    }

    /**
     * @param string $name
     * @return ConfigTemplate
     */
    public function resolve($name)
    {
        if ($this->application) {
            $template = $this->application->getCurrentVersion()->getConfigTemplate($name);

            if ($template) {
                return $template->getTemplate();
            }
        }

        $queue = clone $this->paths;

        foreach ($queue as $path) {
            $file = new SplFileInfo($path . '/' . $name . '.conf');

            if ($file->isFile() && $file->isReadable()) {
                return new ConfigTemplate(file_get_contents($file->getPathname()));
            }
        }

        throw new RuntimeException('Failed to load config template: ' . $name);
    }

    /**
     * @param string $path
     * @param int $priority
     * @return self
     */
    public function addPath($path, $priority = 1)
    {
        $this->paths->insert((string)$path, $priority);
        return $this;
    }

    /**
     * Set application
     */
    public function setApplication(entities\ApplicationInstance $application)
    {
        $this->application = $application;
        return $this;
    }
}
