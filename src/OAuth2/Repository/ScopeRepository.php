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

namespace Rampage\Nexus\OAuth2\Repository;

use Rampage\Nexus\OAuth2\Entities\Scope;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;


/**
 * Implements the scope repository
 */
class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * Available scopes
     *
     * @var array
     */
    private $scopes = [];

    /**
     * {@inheritDoc}
     * @see \League\OAuth2\Server\Repositories\ScopeRepositoryInterface::finalizeScopes()
     */
    public function finalizeScopes(array $scopes, $grantType, ClientEntityInterface $clientEntity, $userIdentifier = null)
    {
        return $scopes;
    }

    /**
     * {@inheritDoc}
     * @see \League\OAuth2\Server\Repositories\ScopeRepositoryInterface::getScopeEntityByIdentifier()
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        if (!in_array($identifier, $this->scopes)) {
            return null;
        }

        return new Scope($identifier);
    }


}