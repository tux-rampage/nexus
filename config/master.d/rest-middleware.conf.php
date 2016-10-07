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

use League\OAuth2\Server\Middleware\ResourceServerMiddleware;
use Zend\Expressive\Container\ApplicationFactory;
use Zend\Expressive\Helper\UrlHelperMiddleware;

/**
 * Defines the routing config
 */
return [
    'rest' => [
        // This can be used to seed pre- and/or post-routing middleware
        'middleware_pipeline' => [
            'routing' => [
                'middleware' => [
                    'route' => ApplicationFactory::ROUTING_MIDDLEWARE,
                    'beforeDispatch' => [
                        'middleware' => [
                            OAuth2\Middleware\ResourceServerMiddleware::class,
                            UrlHelperMiddleware::class,
                        ]
                    ],
                    'dispatch' => ApplicationFactory::DISPATCH_MIDDLEWARE,
                ],
                'priority' => 1,
            ],
        ],
        'routes' => [
            'index' => [
                'name' => 'index',
                'path' => '/',
                'middleware' => Action\IndexAction::class,
                'allowed_methods' => ['GET'],
            ],

            'nodes' => [
                'name' => 'nodes',
                'path' => '/nodes[/{id}]',
                'middleware' => Action\NodesAction::class,
            ],

            'applications' => [
                'name' => 'applications',
                'path' => '/applications[/{id}]',
                'middleware' => Action\ApplicationsAction::class,
                'allow_methods' => [ 'GET' ],
            ],

            'applications/packages' => [
                'name' => 'applications/packages',
                'path' => '/applications/{appId}/packages[/{id}]',
                'middleware' => Action\ApplicationPackagesAction::class,
                'allow_methods' => [ 'GET' ],
            ],

            'applications/icon' => [
                'name' => 'noauth:applications/icon',
                'path' => '/applications/{id}/icon',
                'middleware' => Action\ApplicationIconAction::class,
                'allow_methods' => [ 'GET' ],
            ],

        ],
    ]
];
