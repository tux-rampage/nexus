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

namespace rampage\nexus\entities;

use rampage\auth\IdentityInterface;

use Doctrine\ORM\Mapping as orm;
use Zend\Crypt\PublicKey\Rsa\PublicKey;

/**
 * @orm\Entity
 */
class TrustedPublicKey implements IdentityInterface
{
    /**
     * @orm\Id @orm\Column(type="string", nullable=false)
     * @var string
     */
    private $id;

    /**
     * @orm\Column(name="key", type="text", nullable=false)
     * @var string
     */
    protected $cert = null;

    /**
     * @orm\Column(type="string", nullable=true)
     * @var string
     */
    protected $name = null;

    /**
     * @orm\ManyToOne(targetEntity="Server")
     * @orm\JoinColumn(name="server_id", referencedColumnName="id", nullable=true)
     * @var Server
     */
    protected $server = null;

    /**
     * @orm\Column(type="bool", nullable=false)
     * @var bool
     */
    protected $revoked = false;

    /**
     * @var PublicKey
     */
    protected $key = null;

    /**
     * @param PublicKey|string $cert
     */
    public function __construct($cert = null)
    {
        if (!$cert) {
            return;
        }

        if ($cert instanceof PublicKey) {
            $this->key = $cert;
            $this->cert = $this->key->toString();
        } else {
            $this->cert = $cert;
        }

        $this->id = $this->createFingerPrint();
    }

    /**
     * @see \rampage\auth\IdentityInterface::getCredentialHash()
     */
    public function getCredentialHash()
    {
        return $this->getId();
    }

    /**
     * @see \rampage\auth\IdentityInterface::getIdentity()
     */
    public function getIdentity()
    {
        return 'key:' . $this->getId();
    }

    /**
     * @throws \RuntimeException
     */
    protected function loadKey()
    {
        $this->key = $this->cert? new PublicKey($this->cert) : null;

        if (!$this->key) {
            throw new \RuntimeException('Unable to initialize public key.');
        }
    }

    /**
     * @throws \LogicException
     * @return string
     */
    protected function createFingerPrint()
    {
        $details = openssl_pkey_get_details($this->getKey()->getOpensslKeyResource());
        $hash = sha1(base64_decode($details['key']));

        return $hash;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return PublicKey
     */
    public function getKey()
    {
        if (!$this->key) {
            $this->loadKey();
        }

        return $this->key;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \rampage\nexus\entities\Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param \rampage\nexus\entities\Server $server
     */
    public function setServer($server)
    {
        $this->server = $server;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isRevoked()
    {
        return $this->revoked;
    }

    /**
     * @param boolean $revoked
     */
    public function setRevoked($revoked = true)
    {
        $this->revoked = (bool)$revoked;
        return $this;
    }
}
