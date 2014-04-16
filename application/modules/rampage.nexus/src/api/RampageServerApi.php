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

use rampage\nexus\entities\Server;
use rampage\nexus\entities\ApplicationInstance;
use rampage\nexus\traits\HttpClientAwareTrait;

use Zend\Http\Request as HttpRequest;
use Zend\Uri\Http as HttpUri;
use Zend\Stdlib\Parameters;
use Zend\Json\Json;


class RampageServerApi implements ServerApiInterface
{
    use HttpClientAwareTrait;

    const API_VERSION = '1.0';

    protected function createRequest($url, $path)
    {
        $request = new HttpRequest();
        $request->setUri($url);

        $uri = $request->getUri();
        $basePath = rtrim($uri->getPath(), '/');

        $uri->setPath(sprintf('%s/rest/%s/%s', $basePath, self::API_VERSION, ltrim($path, '/')));
        return $request;
    }

    /**
     * @param string $path
     * @param array $params
     */
    protected function fetchJson(Server $server, $path, $params = array())
    {
        $request = $this->createRequest($server->getUrl(), $path);
        $request->setQuery(new Parameters($params));

        $response = $this->getHttpClient()->send($request);
        if (!$response->isSuccess()) {
            throw new \RuntimeException('Failed to execute API request: ' . $path);
        }

        return Json::decode($response->getBody(), Json::TYPE_OBJECT);
    }

    /**
     * @see \rampage\nexus\api\ServerApiInterface::getServerName()
     */
    public function getServerName(Server $server)
    {
        $info = $this->fetchJson($server, '/server/info');

        if (!isset($info->servername) || !$info->servername) {
            $uri = new HttpUri($server->getUrl());
            $info->servername = $uri->getHost();
        }

        return $info->servername;
    }

    /**
     * @see \rampage\nexus\api\ServerApiInterface::isClusterSupported()
     */
    public function isClusterSupported(Server $server)
    {
        return true;
    }

    /**
     * @see \rampage\nexus\api\ServerApiInterface::attach()
     */
    public function attach(Server $server)
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\api\ServerApiInterface::deploy()
     */
    public function deploy(Server $server, ApplicationInstance $instance)
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\api\ServerApiInterface::detach()
     */
    public function detach(Server $server)
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\api\ServerApiInterface::remove()
     */
    public function remove(Server $server, ApplicationInstance $aplication)
    {
        // TODO Auto-generated method stub

    }

    /**
     * @see \rampage\nexus\api\ServerApiInterface::status()
     */
    public function status(Server $server, ApplicationInstance $application)
    {
        // TODO Auto-generated method stub

    }
}
