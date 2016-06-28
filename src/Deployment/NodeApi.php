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

namespace Rampage\Nexus\Deployment;

use rampage\nexus\exceptions;
use rampage\nexus\entities\ApplicationInstance;
use rampage\nexus\entities\Node;

use Zend\Crypt\PublicKey\Rsa;

use Zend\Http\Client as HttpClient;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Header\Date as DateHeader;


class NodeApi
{
    protected $client;

    /**
     * @var Rsa
     */
    protected $rsa;

    /**
     * @param PrivateKey $rsa
     * @param HttpClient $client
     */
    public function __construct(Rsa $rsa, HttpClient $client = null)
    {
        $this->client = $client? : new HttpClient();
        $this->rsa = $rsa;
    }

    /**
     * @param Node $node
     * @param string $path
     * @return HttpRequest
     */
    protected function prepareRequest(Node $node, $path)
    {
        $request = new HttpRequest();
        $uri = $request->setUri($node->getUrl())->getUri();
        $path = rtrim($uri->getPath(), '/') . '/' . ltrim($path);
        $date = new \DateTime();

        $uri->setPath($path);
        $request->getHeaders()
            ->addHeaderLine('User-Agent', 'rampage.nexus/1.0')
            ->addHeader((new DateHeader())->setDate($date));

        return $request;
    }

    /**
     * @param HttpRequest $request
     */
    protected function sign(HttpRequest $request)
    {
        $uri = $request->getUri();
        $data = [
            $request->getMethod(),
            $uri->getPath(),
            $uri->getHost(),
            $request->getHeader('User-Agent')->getFieldValue(),
            $request->getHeader('Date')->getFieldValue(),
        ];

        $sig = $this->rsa->sign(json_encode($data));
        $request->getHeaders()->addHeaderLine('X-Rampage-Master-Signature', base64_encode($sig));

        return $this;
    }

    /**
     * @param HttpRequest $request
     * @return array
     */
    private function send(HttpRequest $request)
    {
        $this->sign($request);

        $response = $this->client->send($request);
        if (!$response->isSuccess()) {
            throw new exceptions\NodeApiException($response->getReasonPhrase());
        }

        return json_decode($response->getContent(), true);
    }

    /**
     * @param Node $node
     * @param string $path
     * @param array $body
     * @return \Rampage\Nexus\Deployment\array
     */
    protected function post(Node $node, $path, $body)
    {
        $request = $this->prepareRequest($node, $path);
        $request->setMethod(HttpRequest::METHOD_POST)
            ->setContent($body);

        return $this->send($request);
    }

    /**
     * @param Node $node
     * @param string $path
     */
    protected function get(Node $node, $path)
    {
        $request = $this->prepareRequest($node, $path);
        $request->setMethod(HttpRequest::METHOD_GET);

        return $this->send($request);
    }

    /**
     * @param Node $node
     */
    public function detatch(Node $node)
    {
        return $this;
    }

    /**
     * @param Node $node
     */
    public function attach(Node $node)
    {
        $request = $this->prepareRequest($node, 'rebuild');
        return $this->send($request);
    }

    /**
     * @param Node $node
     * @param ApplicationInstance $instance
     * @return self
     */
    public function requestDeploy(Node $node, ApplicationInstance $instance)
    {
        $this->post($node, 'deploy', [
            'instanceId' => $instance->getId()
        ]);

        return $this;
    }

    /**
     * @param Node $node
     * @param ApplicationInstance $instance
     * @return self
     */
    public function requestRemove(Node $node, ApplicationInstance $instance)
    {
        $this->post($node, 'remove', [
            'instanceId' => $instance->getId()
        ]);

        return $this;
    }

    /**
     * Update node information
     *
     * @param Node $node
     */
    public function update(Node $node)
    {
        $data = $this->get($node, 'status');
        $node->setServerInfo($data['serverInfo']);

        return $this;
    }
}