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

namespace Rampage\Nexus\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Stratigility\MiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Middleware that allows pretty printing json responses
 */
class PrettyJsonMiddleware implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return boolean
     */
    private function isPrettyRequested(ServerRequestInterface $request)
    {
        return array_key_exists('pretty', $request->getQueryParams());
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Stratigility\MiddlewareInterface::__invoke()
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $out = null)
    {
        $response = $out($request, $response);

        if (!($response instanceof JsonResponse) || !$this->isPrettyRequested($request)) {
            return $response;
        }

        $pretty = new JsonResponse(
            json_decode($response->getBody()->__toString()),
            $response->getStatusCode(),
            $response->getHeaders(),
            JsonResponse::DEFAULT_JSON_FLAGS | JSON_PRETTY_PRINT
        );

        return $pretty->withStatus($response->getStatusCode(), $response->getReasonPhrase());
    }
}
