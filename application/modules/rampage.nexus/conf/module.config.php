<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2014 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\nexus;

return [
    'navigation' => include __DIR__ . '/navigation.config.php',
    'doctrine' => include __DIR__ . '/doctrine.config.php',
    'di' => include __DIR__ . '/di.config.php',

    'rampage' => [
        'resources' => [
            'rampage.nexus' => dirname(__DIR__) . '/resources',
        ],
        'themes' => [
            'rampage.nexus' => [
                'path' => dirname(__DIR__) . '/default-theme'
            ]
        ]
    ],

    'controllers' => [
        'index' => controllers\IndexController::class,
    ],

    'deployment_config' => [
    ],

//     'router' => [
//         'routes' => require __DIR__ . '/routes.config.php',
//     ]
];
