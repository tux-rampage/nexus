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
    'doctrine' => include __DIR__ . '/module.d/doctrine.conf.php',
    'di' => include __DIR__ . '/module.d/di.conf.php',

    'rampage' => [
        'themes' => [
            'rampage.nexus' => [
                'path' => __DIR__ . '/../theme',
                'fallbacks' => [ 'rampage.gui' ],
            ],
        ],
        'resources' => [
            'rampage.nexus' => dirname(__DIR__) . '/resources',
        ],
    ],

    'deployment_config' => [
    ],

    'router' => [
        'routes' => require __DIR__ . '/module.d/routes.conf.php',
    ]
];
