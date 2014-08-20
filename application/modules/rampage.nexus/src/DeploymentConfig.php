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

use Zend\Uri\Http as HttpUri;

/**
 * Deployment configuration
 */
class DeploymentConfig extends ArrayConfig
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
        $url = $this->get('server.master_url');

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
}
