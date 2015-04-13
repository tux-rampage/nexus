<?php

return [
    'rampage' => [
        'default_theme' => 'rampage.nexus'
    ],

    'deployment' => [
        'server' => [
            'type' => 'standalone'
        ],
        'web' => []
    ],

    'view_manager' => [
        'layout' => 'layout',

        'display_exceptions' => true,
        'display_not_found_reason' => true,
        'exception_template' => 'error/500',
        'not_found_template' => 'error/404',
    ]
];
