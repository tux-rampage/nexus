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

namespace Rampage\Nexus\OAuth2\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use League\OAuth2\Server\Middleware\ResourceServerMiddleware as DefaultResourceServerMiddleware;
use Zend\Stratigility\MiddlewareInterface;
use Zend\Expressive\Router\RouteResult;


/**
 * Middleware for authentication
 */
class ResourceServerMiddleware extends DefaultResourceServerMiddleware implements MiddlewareInterface
{
    /**
     * Execute the authentication middleware
     *
     * @param   RequestInterface    $request
     * @param   ResponseInterface   $response
     * @param   callable            $next       The next middleware to invoke
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        /** @var RouteResult $result */
        $result = $request->getAttribute(RouteResult::class);

        if ($result && (substr($result->getMatchedRouteName(), 0, 7) === 'noauth:')) {
            return $next? $next($request, $response) : $response;
        }

        return parent::__invoke($request, $response, $next);
    }
}
