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

use rampage\nexus\orm\DeploymentRepository;
use rampage\nexus\DeploymentConfig;

use Zend\Crypt\PublicKey\Rsa\PrivateKey;
use Zend\Crypt\PublicKey\RsaOptions;
use rampage\nexus\entities\TrustedPublicKey;
use rampage\nexus\entities\ConfigProperty;



class LocalKeyStorage implements KeyStorageInterface
{
    /**
     * @var DeploymentRepository
     */
    protected $repository = null;

    /**
     * @var PrivateKey
     */
    private $privateKey = null;

    /**
     * @param DeploymentRepository $repository
     * @param DeploymentConfig $config
     */
    public function __construct(DeploymentRepository $repository, DeploymentConfig $config)
    {
        $this->repository = $repository;
        $key = $config->getPrivateKey();

        if ($key) {
            $this->privateKey = new PrivateKey($key, $config->getPrivateKeyPassphrase());
            return;
        }

        $property = $this->repository->findConfigProperty('.keystore.private_key');

        if ($property && $property->getValue()) {
            $this->privateKey = new PrivateKey($property->getValue());
            return;
        }

        $property = new ConfigProperty('.keystore.private_key', ConfigProperty::TYPE_STRING);
        $this->privateKey = (new RsaOptions())->generateKeys()->getPrivateKey();

        $property->setValue($this->privateKey->toString());
        $this->repository->persist($property, true);
    }

    /**
     * @see \rampage\nexus\api\KeyStorageInterface::findPublicKey()
     */
    public function findTrustedPublicKey($id)
    {
        $key = $this->repository->find(TrustedPublicKey::class, $id);

        if ($key && $key->isRevoked()) {
            $key = null;
        }

        return $key;
    }

    /**
     * @see \rampage\nexus\api\KeyStorageInterface::getPrivateKey()
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @see \rampage\nexus\api\KeyStorageInterface::getFingerprint()
     */
    public function getFingerprint()
    {
        return (new TrustedPublicKey($this->getPrivateKey()->getPublicKey()))->getId();
    }
}
