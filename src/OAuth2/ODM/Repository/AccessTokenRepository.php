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

namespace Rampage\Nexus\OAuth2\ODM\Repository;

use Rampage\Nexus\OAuth2\Entities\AccessToken;

use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;

/**
 * Implements the oauth2 access token repo using mongo db
 */
final class AccessTokenRepository extends AbstractTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\OAuth2\ODM\Repository\AbstractTokenRepository::getEntityClass()
     */
    protected function getEntityClass()
    {
        return AccessToken::class;
    }

    /**
     * {@inheritDoc}
     * @see \League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface::getNewToken()
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        $token = new AccessToken($clientEntity, $scopes);

        if ($userIdentifier !== null) {
            $token->setUserIdentifier($userIdentifier);
        }

        return $token;
    }

    /**
     * {@inheritDoc}
     * @see \League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface::isAccessTokenRevoked()
     */
    public function isAccessTokenRevoked($tokenId)
    {
        return $this->isRevoked($tokenId);
    }

    /**
     * {@inheritDoc}
     * @see \League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface::persistNewAccessToken()
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $this->objectManager->persist($accessTokenEntity);
        $this->objectManager->flush($accessTokenEntity);
    }

    /**
     * {@inheritDoc}
     * @see \League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface::revokeAccessToken()
     */
    public function revokeAccessToken($tokenId)
    {
        $this->revokeById($tokenId);
    }
}
