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

use Rampage\Nexus\ServiceFactory\Middleware\RestMiddlewareFactory;
use Zend\Expressive\Helper;


return [
    'dependencies' => [
        'factories' => [
            Helper\ServerUrlMiddleware::class => Helper\ServerUrlMiddlewareFactory::class,
            Helper\UrlHelperMiddleware::class => Helper\UrlHelperMiddlewareFactory::class,
            RestMiddlewareFactory::REST_MIDDLEWARE => RestMiddlewareFactory::class,
        ],
    ],
    // This can be used to seed pre- and/or post-routing middleware
    'middleware_pipeline' => [
        // An array of middleware to register. Each item is of the following
        // specification:
        //
        // [
        //  Required:
        //     'middleware' => 'Name or array of names of middleware services and/or callables',
        //  Optional:
        //     'path'     => '/path/to/match', // string; literal path prefix to match
        //                                     // middleware will not execute
        //                                     // if path does not match!
        //     'error'    => true, // boolean; true for error middleware
        //     'priority' => 1, // int; higher values == register early;
        //                      // lower/negative == register last;
        //                      // default is 1, if none is provided.
        // ],
        //
        // While the ApplicationFactory ignores the keys associated with
        // specifications, they can be used to allow merging related values
        // defined in multiple configuration files/locations. This file defines
        // some conventional keys for middleware to execute early, routing
        // middleware, and error middleware.
        'always' => [
            'middleware' => [
                // Add more middleware here that you want to execute on
                // every request:
                // - bootstrapping
                // - pre-conditions
                // - modifications to outgoing responses
                Helper\ServerUrlMiddleware::class,
                Middleware\ParseRequestMiddleware::class,
                Middleware\PrettyJsonMiddleware::class,
                Middleware\HeadRequestMiddleware::class,
            ],
            'priority' => 10000,
        ],

//         'routing' => [
//             'middleware' => [
//                 'rewrites' => Middleware\RewriteMiddleware::class,
//                 'route' => ApplicationFactory::ROUTING_MIDDLEWARE,
//                 // Add more middleware here that needs to introspect the routing
//                 // results; this might include:
//                 // - route-based authentication
//                 // - route-based validation
//                 // - etc.
//                 'beforeDispatch' => [
//                     'middleware' => [
//                         Helper\UrlHelperMiddleware::class,
//                     ]
//                 ],
//                 'dispatch' => ApplicationFactory::DISPATCH_MIDDLEWARE,
//             ],
//             'priority' => 1,
//         ],

        'error' => [
            'middleware' => [
                // Add error middleware here.
            ],
            'error'    => true,
            'priority' => -10000,
        ],
    ],
];
