<?php
/**
 * Copyright (c) 2015 Axel Helmert
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
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\Node\ConfigTemplate;

use Rampage\Nexus\Exception;
use Zend\Stdlib\SplPriorityQueue;

/**
 * Implements the default template locator
 */
class TemplateLocator implements TemplateLocatorInterface
{
    /**
     * @var SplPriorityQueue
     */
    protected $pathStack = [];

    /**
     * Initialize
     */
    public function __construct()
    {
        $this->pathStack = new SplPriorityQueue();
        $this->addPath(__DIR__ . '/../../../resources/config-templates', 0);
    }

    /**
     * @param string $path
     * @param number $priority
     * @return self
     */
    public function addPath($path, $priority = 1000)
    {
        $this->pathStack->insert($path, $priority);
        return $this;
    }

    /**
     * @param string $type
     * @param string $name
     */
    public function getConfigTemplate($type, $name, $flavor = null)
    {
        if ($flavor === null) {
            $flavor = 'default';
        }

        $file = sprintf('%s/%s/%s.conf.tpl', $type, $flavor, $name);

        foreach (clone $this->pathStack as $path) {
            $template = $path .'/' . $file;

            if (is_file($template) && is_readable($template)) {
                return file_get_contents($template);
            }
        }

        if ($flavor != 'default') {
            return $this->getConfigTemplate($type, $name);
        }

        throw new Exception\RuntimeException(sprintf('Could not find a config template for %s in %s', $name, $type));
    }
}
