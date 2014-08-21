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


class AuthToken implements AuthAdapterInterface
{
    /**
     * @var string
     */
    private $privateKey = null;

    /**
     * @var KeyStorageInterface
     */
    private $keyStorage = null;

    /**
     * @var HttpRequest
     */
    protected $request = null;

    /**
     * @param string $name
     */
    public function __construct(KeyStorageInterface $keyStorage, $privateKey = null, $privatePassphrase = null)
    {
        $this->keyStorage = $keyStorage;

        if ($privateKey) {
            $this->privateKey = openssl_pkey_get_private($privateKey, $privatePassphrase);
        }

        $this->request = new PhpHttpRequest();
    }

    public function signRequest(HttpRequest $request)
    {
        if (!$this->privateKey) {
            throw new \LogicException('Cannot sign request without private key');
        }

        // TODO: Sign request

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
     * {@inheritdoc}
     * @see \Zend\Authentication\Adapter\AdapterInterface::authenticate()
     */
    public function authenticate()
    {
        $this->request->getHeader('X-Rampage-Auth')

        if (!$this->publicKey) {
            return new AuthResult(AuthResult::FAILURE, null);
        }


    }



}
