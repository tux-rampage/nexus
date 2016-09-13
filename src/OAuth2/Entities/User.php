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

use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use Rampage\Nexus\Entities\Api\ArrayExchangeInterface;
use Zend\Crypt\Password\PasswordInterface;

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
     * @param string $id
     */
    public function __construct(PasswordInterface $passwordStrategy, $id)
    {
        $this->id = $id;
        $this->passwordStrategy = $passwordStrategy;
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
     * @return boolean
     */
    public function verifyPassword($password)
    {
        return $this->passwordStrategy->verify($password, $this->password);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExchangeInterface::exchangeArray()
     */
    public function exchangeArray(array $array)
    {
        // TODO Auto-generated method stub

    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExportableInterface::toArray()
     */
    public function toArray()
    {
        // TODO Auto-generated method stub

    }



}
