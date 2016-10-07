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

namespace Rampage\Nexus\OAuth2\Action;

use Rampage\Nexus\Exception\Http\BadRequestException;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Lcobucci\JWT\Parser as JwtParser;

use Zend\Diactoros\Response\EmptyResponse;


/**
 * Revoke action
 */
class RevokeAction
{
    const TYPE_ACCESS_TOKEN = 'access_token';
    const TYPE_REFRESH_TOKEN = 'refresh_token';

    /**
     * @var ClientRepositoryInterface
     */
    private $clientRepository;

    /**
     * @var AccessTokenRepositoryInterface
     */
    private $accessTokenRepository;

    /**
     * @var RefreshTokenRepositoryInterface
     */
    private $refreshTokenRepository;

    /**
     * @param ServerRequestInterface $request
     * @throws BadRequestException
     * @return NULL|\League\OAuth2\Server\Entities\ClientEntityInterface
     */
    protected function validateClient(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody();
        $clientId = (isset($data['client_id']))? $data['client_id'] : null;
        $secret = (isset($data['client_secret']))? $data['client_secret'] : null;
        $client = ($clientId)? $this->clientRepository->getClientEntity($clientId, 'revoke', $secret, true) : null;

        if (!$client) {
            throw new BadRequestException('Client authentication failed', BadRequestException::UNAUTHORIZED);
        }

        return $client;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        if ($request->getMethod() != 'POST') {
            throw new BadRequestException('Invalid request method', BadRequestException::NOT_ALLOWED);
        }

        $this->validateClient($request);

        $data = $request->getParsedBody();
        $token = isset($data['token'])? (new JwtParser())->parse($data['token']) : null;
        $type = isset($data['token_type_hint'])? $data['token_type_hint'] : self::TYPE_ACCESS_TOKEN;
        $tokenId = ($token)? $token->getClaim('jti') : null;

        if (!$tokenId) {
            throw new BadRequestException('Bad token value', BadRequestException::UNPROCESSABLE);
        }

        switch ($type) {
            case self::TYPE_ACCESS_TOKEN:
                $this->accessTokenRepository->revokeAccessToken($tokenId);
                break;

            case self::TYPE_REFRESH_TOKEN:
                $this->refreshTokenRepository->revokeRefreshToken($tokenId);
                break;

            default:
                throw new BadRequestException('Invalid token type', BadRequestException::UNPROCESSABLE);
                break;
        }

        return (new EmptyResponse());
    }
}
