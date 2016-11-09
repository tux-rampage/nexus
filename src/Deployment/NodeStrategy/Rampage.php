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

namespace Rampage\Nexus\Deployment\NodeStrategy;

use Rampage\Nexus\Deployment\NodeStrategyInterface;
use Rampage\Nexus\Deployment\NodeInterface;

use Rampage\Nexus\Api\SignRequestInterface;
use Rampage\Nexus\Entities\Node;
use Rampage\Nexus\Entities\DeployTarget;
use Rampage\Nexus\Entities\ApplicationInstance;
use Rampage\Nexus\Exception\LogicException;
use Rampage\Nexus\Exception\ApiCallException;
use Rampage\Nexus\Exception\SecurityViolationException;
use Rampage\Nexus\StringStream;

use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\Exception\RequestException as HttpRequestException;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

use Zend\Stdlib\Parameters;
use Zend\Diactoros\Uri;
use Zend\Diactoros\Request;


/**
 * Rempage node implementation
 */
class Rampage implements NodeStrategyInterface
{
    const TYPE_ID = 'rampage';

    /**
     * @var Node
     */
    private $entity = null;

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
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeStrategyInterface::setEntity()
     */
    public function setEntity(Node $entity)
    {
        $this->entity = $entity;
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
            return $this->entity->getUrl();
        }

        $uri = new Uri($this->entity->getUrl());
        $path = $uri->getPath();

        return $uri->withPath(rtrim($path, '/') . '/' . $route);
    }

    /**
     * @param ResponseInterface $response
     * @throws ApiCallException
     * @return Parameters
     */
    private function decodeJsonResponseBody(ResponseInterface $response)
    {
        $contentType = strtolower($response->getHeader('Content-Type'));

        if (!strpos($contentType, 'application/json') !== 0) {
            throw new ApiCallException('Bad response content type');
        }

        $contents = $response->getBody()->getContents();
        $data = json_decode($contents, true);

        if (!is_array($data)) {
            throw new ApiCallException('Failed to decode response body: ' . json_last_error_msg());
        }

        return new Parameters($data);
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
        $state = $this->entity->getState();

        if (!$application) {
            return ($state == self::STATE_READY);
        }

        if ($this->entity->getApplicationState($application) == self::STATE_READY) {
            return ($state != self::STATE_BUILDING);
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

            if ($body === null) {
                $body = 'php://temp';
            } else if (!($body instanceof StreamInterface) && !is_resource($body)) {
                if (!is_string($body)) {
                    $body = json_encode($body);
                }

                $body = new StringStream($body);
            }

            $request = new Request($this->buildUrl($route), $method, $body);
            return $this->http->send($request, $options);
        } catch (HttpRequestException $e) {
            $this->entity->setState(self::STATE_UNREACHABLE);
            throw new ApiCallException($e->getMessage(), $e->getCode(), $e);
        } catch (SecurityViolationException $e) {
            $this->entity->setState(self::STATE_SECURITY_VIOLATED);
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

        $response = $this->request('POST', 'rebuild', [
            'applicationId' => $application? $application->getId() : null
        ]);

        $body = $this->decodeJsonResponseBody($response);
        $this->entity->setState($body->get('state', self::STATE_BUILDING));
        $this->entity->updateApplicationStates($body->get('applicationStates'));

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

        $data = $this->decodeJsonResponseBody($response);
        $serverInfo = $data->get('serverInfo');

        $this->entity->setState($data->get('state', self::STATE_UNINITIALIZED));
        $this->entity->setApplicationStates($data->get('applicationStates'));

        if (is_array($serverInfo)) {
            $this->entity->setServerInfo($serverInfo);
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
        $data = $this->decodeJsonResponseBody($response);

        $this->entity->setState($data->get('state', $this->entity->getState()));
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::acceptsClusterSibling()
     */
    public function acceptsClusterSibling(NodeInterface $node)
    {
        return ($node->getTypeId() == self::TYPE_ID);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::attach()
     */
    public function attach(DeployTarget $deployTarget)
    {
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::canSync()
     */
    public function canSync()
    {
        $invalidStates = [
            self::STATE_BUILDING,
            self::STATE_UNREACHABLE,
            self::STATE_SECURITY_VIOLATED
        ];

        return !in_array($this->entity->getState(), $invalidStates);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Deployment\NodeInterface::detach()
     */
    public function detach()
    {
    }
}
