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

use Zend\Authentication\Adapter\AdapterInterface as AuthAdapterInterface;
use Zend\Authentication\Result as AuthResult;

use Zend\Http\Request as HttpRequest;
use Zend\Http\PhpEnvironment\Request as PhpHttpRequest;
use Zend\Http\Header\Date as DateHeader;
use Zend\Http\Header\UserAgent;
use Zend\Crypt\PublicKey\Rsa;

use DateTime;


class AuthTokenService implements AuthAdapterInterface
{
    /**
     * @var KeyStorageInterface
     */
    private $keyStorage = null;

    /**
     * @var HttpRequest
     */
    protected $request = null;

    /**
     * Maximum time difference in seconds
     *
     * @var int
     */
    protected $maxTimeDiff = 30;

    /**
     * @param string $name
     */
    public function __construct(KeyStorageInterface $keyStorage)
    {
        $this->keyStorage = $keyStorage;
        $this->request = new PhpHttpRequest();
    }

    /**
     * @param string $keyId
     * @param HttpRequest $request
     * @throws \LogicException
     * @return string
     */
    protected function createSignatureData($keyId, HttpRequest $request)
    {
        $userAgent = $request->getHeader('User-Agent');
        $path = $request->getUri()->getPath();
        $host = $request->getUri()->getHost();
        $date = $request->getHeader('Date');

        if (!$userAgent instanceof UserAgent) {
            throw new \LogicException('Cannot sign request without user agent');
        }

        if (!$date instanceof DateHeader) {
            $date = new DateHeader();
        }

        return implode('|', array($keyId, $host, $path, $date->getDate(), $userAgent->getFieldValue()));
    }

    /**
     * @param HttpRequest $request
     * @throws \LogicException
     * @return \rampage\nexus\api\AuthTokenService
     */
    public function signRequest(HttpRequest $request)
    {
        $keyId = $this->keyStorage->getFingerprint();
        $key = $this->keyStorage->getPrivateKey();
        $data = $this->createSignatureData($keyId, $request);

        $signature = (new Rsa())->sign($data, $key);
        $signature = base64_encode($signature);

        $request->getHeaders()
            ->addHeaderLine('X-Rampage-Auth-KeyId', $keyId)
            ->addHeaderLine('X-Rampage-Auth-Signature', $signature);

        return $this;
    }

    /**
     * @param HttpRequest $request
     * @return self
     */
    public function setRequest(HttpRequest $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param DateTime $date
     */
    protected function checkTimeDiff()
    {
        $header = $this->request->getHeader('Date');

        if (!$header instanceof DateHeader) {
            return false;
        }

        $date = $header->getDate();
        $now = new \DateTime(null, $date->date()->getTimezone());
        $max = $now->getTimestamp() + $this->maxTimeDiff;
        $min = $now->getTimestamp() - $this->maxTimeDiff;

        if (($date->getTimestamp() < $min) || ($date->getTimestamp() > $max)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     * @see \Zend\Authentication\Adapter\AdapterInterface::authenticate()
     */
    public function authenticate()
    {
        $keyId = $this->request->getHeader('X-Rampage-Auth-KeyId');
        $signature = $this->request->getHeader('X-Rampage-Auth-Signature');
        $key = ($keyId)? $this->keyStorage->findTrustedPublicKey($keyId->getFieldValue()) : null;

        if (!$key || !$signature || !$signature->getFieldValue()) {
            return new AuthResult(AuthResult::FAILURE, null);
        }

        if (!$this->checkTimeDiff()) {
            return new AuthResult(AuthResult::FAILURE, null, array(
                sprintf('Request time difference too big. Maximum difference is %d seconds', $this->maxTimeDiff)
            ));
        }

        $data = $this->createSignatureData($keyId, $this->request);
        $rawSignature = base64_decode($signature);

        if (!(new Rsa())->verify($data, $rawSignature, $key->getKey())) {
            return new AuthResult(AuthResult::FAILURE, null);
        }

        return new AuthResult(AuthResult::SUCCESS, $key);
    }
}
