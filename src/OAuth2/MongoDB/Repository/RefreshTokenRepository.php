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

use Rampage\Nexus\OAuth2\Entities\RefreshToken;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;

class RefreshTokenRepository extends AbstractTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\OAuth2\MongoDB\Repository\AbstractTokenRepository::getCollectionName()
     */
    protected function getCollectionName()
    {
        return 'OAuth2RefreshTokens';
    }

    /**
     * {@inheritDoc}
     * @see \League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface::getNewRefreshToken()
     */
    public function getNewRefreshToken()
    {
        return new RefreshToken();
    }

    /**
     * {@inheritDoc}
     * @see \League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface::isRefreshTokenRevoked()
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        return $this->isRevoked($tokenId);
    }

    /**
     * {@inheritDoc}
     * @see \League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface::persistNewRefreshToken()
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $data = [
            '_id' => $refreshTokenEntity->getIdentifier(),
            'expires' => $this->dateStrategy->extract($refreshTokenEntity->getExpiryDateTime()),
            'revoked' => false,
            'accessTokenId' => $refreshTokenEntity->getAccessToken()->getIdentifier()
        ];

        $this->collection->insert($data);
    }

    /**
     * {@inheritDoc}
     * @see \League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface::revokeRefreshToken()
     */
    public function revokeRefreshToken($tokenId)
    {
        $this->revokeById($tokenId);
    }
}