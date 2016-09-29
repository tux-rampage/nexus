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

namespace Rampage\Nexus\OAuth2\Entities;

use Rampage\Nexus\Entities\Api\ArrayExchangeInterface;

use League\OAuth2\Server\Entities\UserEntityInterface;
use Psr\Http\Message\StreamInterface;

use Zend\Crypt\Password\PasswordInterface;
use Zend\Crypt\Password\Bcrypt as BcryptPasswordStrategy;
use Zend\Stdlib\Parameters;

/**
 * Implements the user entity
 */
class User implements UserEntityInterface, ArrayExchangeInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $password;

    /**
     * @var PasswordInterface
     */
    private $passwordStrategy;

    /**
     * The display name
     *
     * @var string
     */
    private $name;

    /**
     * The user's avatar
     *
     * @var StreamInterface
     */
    private $avatar;

    /**
     * @var string
     */
    private $email;

    /**
     * @param string $id
     */
    public function __construct($id, PasswordInterface $passwordStrategy = null)
    {
        $this->id = $id;
        $this->passwordStrategy = $passwordStrategy? : new BcryptPasswordStrategy();
    }

    /**
     * Inject the password startegy to use
     *
     * @param PasswordInterface $passwordStrategy
     * @return \Rampage\Nexus\OAuth2\Entities\User
     */
    public function setPasswordStrategy(PasswordInterface $passwordStrategy)
    {
        $this->passwordStrategy = $passwordStrategy;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \League\OAuth2\Server\Entities\UserEntityInterface::getIdentifier()
     */
    public function getIdentifier()
    {
        return $this->id;
    }

    /**
     * @param string $password
     * @return self
     */
    public function setPassword($password)
    {
        $this->password = $this->passwordStrategy->create($password);
        return $this;
    }

    /**
     * @param string $password
     * @return boolean
     */
    public function verifyPassword($password)
    {
        return $this->passwordStrategy->verify($password, $this->password);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param \Psr\Http\Message\StreamInterface $image
     * @return self
     */
    public function setAvatar($image)
    {
        $this->avatar = $image;
        return $this;
    }

    /**
     * @param string $email
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExchangeInterface::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        $data = new Parameters($array);

        $this->setName($data->get('name', $this->name))
            ->setEmail($data->get('email', $this->email));

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExportableInterface::toArray()
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'hasAvatar' => ($this->avatar !== null)
        ];
    }



}
