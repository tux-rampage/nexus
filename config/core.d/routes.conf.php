<?php

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\FastRouteRouter::class,
        ],
    ],

    'routes' => [
//         [
//             'name' => 'index',
//             'path' => '/',
//             'middleware' => Rampage\Nexus\Middleware\Index::class,
//             'allowed_methods' => ['GET'],
//         ],
    ],
];
