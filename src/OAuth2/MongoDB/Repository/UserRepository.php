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

namespace Rampage\Nexus\OAuth2\MongoDB\Repository;

use Rampage\Nexus\MongoDB\Repository\AbstractRepository;
use Rampage\Nexus\MongoDB\Driver\DriverInterface;

use Rampage\Nexus\OAuth2\Entities\User;
use Rampage\Nexus\OAuth2\Entities\UIClient;
use Rampage\Nexus\OAuth2\Repository\UserRepositoryInterface;
use Rampage\Nexus\OAuth2\MongoDB\Hydration\UserHydrator;

use League\OAuth2\Server\Entities\ClientEntityInterface;

use Zend\Crypt\Password\PasswordInterface;


/**
 * Implements the user repository
 */
class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    const COLLECTION = 'users';

    /**
     * @var PasswordInterface
     */
    private $passwordStrategy = null;

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Repository\AbstractRepository::__construct()
     */
    public function __construct(DriverInterface $driver, PasswordInterface $passwordStrategy)
    {
        parent::__construct($driver, new UserHydrator($driver), self::COLLECTION);
        $this->passwordStrategy = $passwordStrategy;

    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\MongoDB\Repository\AbstractRepository::newEntityInstance()
     */
    protected function newEntityInstance(array &$data)
    {
        return new User($this->passwordStrategy, null);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Repository\PrototypeProviderInterface::getPrototypeByData()
     */
    public function getPrototypeByData($data)
    {
        if (!is_array($data)) {
            $data = [];
        }

        return $this->newEntityInstance($data);
    }

    /**
     * Save the user entity
     *
     * @param User $user
     */
    public function save(User $user)
    {
        $this->doPersist($user);
        return $this;
    }

    /**
     * Removes a user from persistence
     *
     * @param User $user
     */
    public function remove(User $user)
    {
        $this->doRemove($user);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \League\OAuth2\Server\Repositories\UserRepositoryInterface::getUserEntityByUserCredentials()
     */
    public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $clientEntity)
    {
        if ($clientEntity->getIdentifier() !== UIClient::ID) {
            return null;
        }

        /** @var User $user */
        $user = $this->findOne($username);

        if (!$user || !$user->verifyPassword($password)) {
            return null;
        }

        return $user;
    }
}
