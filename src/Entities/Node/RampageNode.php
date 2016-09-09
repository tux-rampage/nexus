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

namespace Rampage\Nexus\Entities\Node;

use Rampage\Nexus\Api\SignRequestInterface;
use Rampage\Nexus\Entities\AbstractNode;
use Rampage\Nexus\Entities\ApplicationInstance;
use Rampage\Nexus\Exception\LogicException;
use Rampage\Nexus\Exception\ApiCallException;
use Rampage\Nexus\Exception\SecurityViolationException;

use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\Exception\RequestException as HttpRequestException;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

use Zend\Stdlib\Parameters;
use Zend\Diactoros\Uri;
use Rampage\Nexus\Middleware\DecodeJsonTrait;


/**
 * Rempage node implementation
 */
class RampageNode extends AbstractNode
{
    use DecodeJsonTrait;

    const TYPE_ID = 'rampage';

    /**
     * @var SignRequestInterface
     */
    private $signRequestStrategy;

    /**
     * @var HttpClientInterface
     */
    private $http;

    /**
     * @param HttpClientInterface $http
     */
    public function __construct(HttpClientInterface $http)
    {
        $this->http = $http;
    }

    /**
     * Builds the request URI
     *
     * @param string $route
     * @return \Zend\Diactoros\Uri
     */
    private function buildUrl($route = null)
    {
        if ($route == '') {
            return $this->url;
        }

        $uri = new Uri($this->url);
        $path = $uri->getPath();

        return $uri->withPath(rtrim($path, '/') . '/' . $route);
    }

    /**
     * @param ResponseInterface $response
     * @throws ApiCallException
     * @return mixed
     */
    private function decodeResponseBody(ResponseInterface $response)
    {
        $contentType = strtolower($response->getHeader('Content-Type'));

        if (!strpos($contentType, 'application/json') !== 0) {
            throw new ApiCallException('Bad response body');
        }

        $contents = $response->getBody()->getContents();
        $data = json_decode($contents, true);

        if (!is_array($data)) {
            throw new ApiCallException('Failed to decode response body: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Check for response success
     *
     * @param ResponseInterface $response
     * @return boolean
     */
    private function isSuccess(ResponseInterface $response)
    {
        $status = $response->getStatusCode();
        return (($status >= 200) && ($status < 400));
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::getTypeId()
     */
    public function getTypeId()
    {
        return self::TYPE_ID;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::canRebuild()
     */
    public function canRebuild(ApplicationInstance $application = null)
    {
        if (!$application) {
            return ($this->state == self::STATE_READY);
        }

        if ($this->getApplicationState($application) == self::STATE_READY) {
            return ($this->state != self::STATE_BUILDING);
        }

        return false;
    }

    /**
     * Perform a HTTP request
     *
     * @param   string                                  $method
     * @param   string                                  $route
     * @param   null|string|resource|StreamInterface    $body
     * @return  ResponseInterface
     */
    private function request($method, $route = null, $body = null)
    {
        try {
            $options = [];

            if ($body !== null) {
                $options['body'] = $body;
            }

            return $this->http->request($method, $this->buildUrl($route), $options);
        } catch (HttpRequestException $e) {
            $this->state = self::STATE_UNREACHABLE;
            throw new ApiCallException($e->getMessage(), $e->getCode(), $e);
        } catch (SecurityViolationException $e) {
            $this->state = self::STATE_SECURITY_VIOLATED;
            throw new ApiCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::rebuild()
     */
    public function rebuild(ApplicationInstance $application = null)
    {
        if (!$this->canRebuild($application)) {
            throw new LogicException('Cannot rebuild this application.');
        }

        $response = $this->request('POST', 'rebuild', json_encode([
            'applicationId' => $application? $application->getId() : null
        ]));

        $body = new Parameters($this->decodeResponseBody($response));
        $this->state = $body->get('state', self::STATE_BUILDING);

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::refresh()
     */
    public function refresh()
    {
        try {
            $response = $this->request('GET', $this->url);
        } catch (ApiCallException $e) {
            return;
        }

        $data = new Parameters($this->decodeResponseBody($response));
        $serverInfo = $data->get('serverInfo');

        $this->state = $data->get('state', self::STATE_UNINITIALIZED);
        $this->setApplicationStates($data->get('applicationStates'));

        if (is_array($serverInfo)) {
            $this->setServerInfo($serverInfo);
        }
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::sync()
     */
    public function sync()
    {
        if (!$this->canSync()) {
            throw new LogicException('This node cannot be synced!');
        }

        $response = $this->request('POST', 'sync', '{}');
        $data = new Parameters($this->decodeResponseBody($response));
        $this->state = $data->get('state', $this->state);
    }
}
