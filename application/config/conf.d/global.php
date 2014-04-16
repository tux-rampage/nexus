<?php

return [
    'rampage' => [
        'default_theme' => 'rampage.nexus'
    ],

    'system_config' => [
        'server' => [
            'type' => 'standalone'
        ],
        'web' => []
    ],

    'view_manager' => [
        'layout' => 'layout',

        'display_exceptions' => true,
        'exception_template' => 'error/500',
        'display_not_found_reason' => true,
        'not_found_template'       => 'error/404',
    ]
];