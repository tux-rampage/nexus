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

namespace Rampage\Nexus;

use Rampage\Nexus\Middleware\RestApiMiddleware;
use League\OAuth2\Server\Middleware\AuthorizationServerMiddleware;


/**
 * Defines the routing config
 */
return [
    'routes' => [
        'nodeApi' => [
            'name' => 'nodeApi',
            'path' => '/deploy-node',
            'allowed_methods' => ['GET', 'POST'],
            'middleware' => [
                Action\NodeApi\NodeContextMiddleware::class,
                Action\NodeApi\NodeAction::class,
            ]
        ],

        'nodeApi/package' => [
            'name' => 'nodeApi/package',
            'path' => '/deploy-node/package',
            'allowed_methods' => ['GET'],
            'middleware' => [
                Action\NodeApi\NodeContextMiddleware::class,
                Action\NodeApi\PackageAction::class,
            ]
        ],


        'auth' => [
            'name' => 'auth',
            'path' => '/authorize',
            'allow_methods' => ['GET', 'POST'],
            'middleware' => [
                AuthorizationServerMiddleware::class,
            ]
        ],

        'nodes' => [
            'name' => 'nodes',
            'path' => '/nodes[/{id}]',
            'middleware' => RestApiMiddleware::class . '\Node',
        ],
    ],
];
