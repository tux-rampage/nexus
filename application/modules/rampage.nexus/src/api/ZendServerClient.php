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

use rampage\nexus\traits\HttpClientAwareTrait;

use Zend\Http\Request as HttpRequest;
use Zend\Http\Header\Date as DateHeader;
use Zend\Http\Client as HttpClient;
use Zend\Http\Client\Adapter\Curl as CurlHttpAdapter;
use Zend\Version\Version as ZFVersion;
use Zend\Config\Config as ArrayConfig;
use rampage\core\xml\SimpleXmlElement;
use Zend\Stdlib\Parameters;
use rampage\core\exception\RuntimeException;


class ZendServerClient
{
    use HttpClientAwareTrait;

    const ZS_API_VERSION = '1.6';
    const USER_AGENT = 'ZendServerClient/1.0';

    /**
     * @var ArrayConfig
     */
    protected $apiVersionMap = array(
        'applicationGetStatus' => 1.2,
        'applicationRemove' => 1.2,
        'clusterGetServersCount' => 1.3,
    );

    /**
     * @var ArrayConfig
     */
    protected $apiMethodRequestMap = array(
        'applicationGetStatus' => HttpRequest::METHOD_GET,
        'applicationRemove' => HttpRequest::METHOD_POST,
        'clusterGetServersCount' => HttpRequest::METHOD_GET,
    );


    /**
     * @var string
    */
    protected $userAgent = null;

    /**
     * @var string
     */
    protected $uri = null;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->apiVersionMap = new ArrayConfig($this->apiVersionMap);
        $this->userAgent = implode(' ', array(
            self::USER_AGENT,
            'ZendHttpClient/' . ZFVersion::VERSION
        ));
    }

    /**
     * @return \Zend\Http\Client
     */
    protected function createHttpClient()
    {
        $client = new HttpClient();
        $adapter = new CurlHttpAdapter();

        $adapter->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
        $client->setAdapter($adapter);

        return $client;
    }

    /**
     * @param string $agent
     * @return self
     */
    public function setUserAgent($agent)
    {
        $this->userAgent = $agent;
        return $this;
    }

    /**
     * Set the webservice uri
     *
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @param HttpRequest $request
     * @param string $user
     * @param string $key
     * @return \rampage\nexus\api\ZendServerApi
     */
    private function signRequest(HttpRequest $request)
    {
        $date = $request->getHeader('Date');
        $uri = $request->getUri();
        $user = $uri->getUser();
        $key = $uri->getPassword();

        $uri->setUser(null)
            ->setPassword(null)
            ->setUserInfo(null);

        if (!$date instanceof DateHeader) {
            $date = new DateHeader();
            $request->getHeaders()->addHeader($date);
        }

        $parts = array(
            $request->getUri()->getHost(),
            $request->getUri()->getPath(),
            $this->userAgent,
            $date->getDate()
        );

        $signature = hash_hmac('sha256', implode(':', $parts), $key);

        $request->getHeaders()
            ->addHeaderLine('X-Zend-Signature', "$user; $signature");

        return $this;
    }

    /**
     * @param string $action
     * @return \Zend\Http\Request
     */
    protected function createRequest($action)
    {
        $request = new HttpRequest();
        $version = $this->apiVersionMap[$action]? : self::ZS_API_VERSION;
        $method = $this->apiRequestMethodMap[$action]? : HttpRequest::METHOD_POST;

        $request->getHeaders()
            ->addHeaderLine('User-Agent', $this->userAgent)
            ->addHeaderLine('Accept', 'application/vnd.zend.serverapi+xml;version=' . $version);

        $request->setMethod($method)
            ->setUri($this->uri);

        $uri = $request->getUri();
        $path = rtrim($uri->getPath(), '/');

        $uri->setPath($path . '/ZendServer/Api/' . $action);

        return $request;
    }

    /**
     * @param string $method
     * @param array $args
     * @throws RuntimeException
     * @return SimpleXmlElement
     */
    protected function execSimple($method, $args = array())
    {
        $request = $this->createRequest($method);
        $params = new Parameters($args);

        if ($request->getMethod() == HttpRequest::METHOD_POST) {
            $request->setPost($params);
        } else {
            $request->setQuery($params);
        }

        $this->signRequest($request);
        $result = $this->getHttpClient()->send($request);

        if (!$result->isSuccess()) {
            throw new RuntimeException('Failed to execute ZendServer API call: ' . $method);
        }

        return simplexml_load_string($result->getBody(), SimpleXmlElement::class);
    }

    /**
     * @return int
     */
    public function clusterGetServersCount()
    {
        $result = $this->execSimple(__FUNCTION__);
        return intval((string)$result->responseData->serversCount);
    }

    /**
     * @param array $applications
     * @return SimpleXmlElement
     */
    public function applicationGetStatus($applications = null)
    {
        return $this->execSimple(__FUNCTION__, $applications? compact('applications') : array());
    }

    /**
     * @param int $appId
     * @return SimpleXmlElement
     */
    public function applicationRemove($appId)
    {
        return $this->execSimple(__FUNCTION__, compact('appId'));
    }

    /**
     * @param string $name
     * @return SimpleXmlElement|false
     */
    public function applicationGetStatusByName($name)
    {
        $xml = $this->applicationGetStatus();
        $result = $xml->xpath(sprintf('./responseData/applicationList/applicationInfo[userAppName = %s]', $xml->quoteXpathValue($name)));

        return $result->current();
    }

    /**
     * @param string $name
     * @return int|null
     */
    public function findDeployedApplicationId($name)
    {
        $xml = $this->applicationGetStatusByName($name);
        if (!$xml instanceof SimpleXmlElement) {
            return false;
        }

        return (string)$node->id;
    }
}
