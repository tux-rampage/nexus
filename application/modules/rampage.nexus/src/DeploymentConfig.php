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
 * @category  library
 * @author    Axel Helmert
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus;

use Zend\Config\Config;
use Zend\Uri\Http as HttpUri;

/**
 * Deployment configuration
 */
class DeploymentConfig extends Config
{
    const SERVER_TYPE_STANDALONE = 'standalone';
    const SERVER_TYPE_MASTER = 'master';
    const SERVER_TYPE_NODE = 'node';

    /**
     * @return string
     */
    public function getServerType()
    {
        return (string)$this->server->type;
    }

    /**
     * @return boolean
     */
    public function isStandalone()
    {
        return ($this->getServerType() == self::SERVER_TYPE_STANDALONE);
    }

    /**
     * @return boolean
     */
    public function isMaster()
    {
        return ($this->getServerType() == self::SERVER_TYPE_MASTER);
    }

    /**
     * @return boolean
     */
    public function isNode()
    {
        return ($this->getServerType() == self::SERVER_TYPE_NODE);
    }

    /**
     * @param string $path
     * @return string
     */
    public function getMasterApiUrl($path = null)
    {
        $url = $this->server->master_url;

        if (empty($url)) {
            return null;
        }

        $url = new HttpUri($url);
        $restPath = rtrim($url->getPath(), '/') . '/rest';

        if ($path) {
            $restPath .= '/' . ltrim($path, '/');
        }

        $url->setPath($restPath);

        return $url;
    }

    /**
     * {@inheritdoc}
     * @see \Zend\Config\Config::get()
     */
    public function get($name, $default = null)
    {
        if (strpos($name, '.') === false) {
            return parent::get($name, $default);
        }

        @list($section, $key) = explode('.', $name, 2);
        $node = parent::get($section);

        if (!$node instanceof self) {
            return $default;
        }

        return $node->get($key);
    }


    public function getNode($name)
    {
        $node = $this->get($name);

        if ($node instanceof self) {
            return $node;
        }

        if ($node instanceof \Traversable) {
            $node = iterator_to_array($node);
        }

        return new static(is_array($node)? $node : array());
    }

}
