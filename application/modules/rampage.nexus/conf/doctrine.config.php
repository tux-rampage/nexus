<?php
/**
 * Copyright (c) 2014 Axel Helmert
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Axel Helmert
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */
namespace rampage\nexus;

use Doctrine\DBAL\Driver\PDOSqlite\Driver as DefaultDBDriver;
use Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;

return [

    'connection' => [
        'orm_default' => [
            'driverClass' => DefaultDBDriver::class,
            'params' => [
                'path' => RAMPAGE_PREFIX . '/var/db/deployment.db'
            ]
        ]
    ],

    'configuration' => [
        // Configuration for service `doctrine.configuration.orm_default` service
        'orm_default' => [
            'metadata_cache' => 'rampagenexus',
            'query_cache' => 'rampagenexus',
            'result_cache' => 'rampagenexus',
            'hydration_cache' => 'rampagenexus',

            'generate_proxies' => true,
            'proxy_dir' => APPLICATION_DIR . '/_generated/orm.proxies/default',
            'proxy_namespace' => 'orm\proxies',
            'naming_strategy' => UnderscoreNamingStrategy::class
        ]
    ],

    'driver' => [
        'rampage_nexus_entities' => [
            'class' => AnnotationDriver::class,
            'cache' => 'rampagenexus',
            'paths' => [
                __DIR__ . '/../src/entities'
            ]
        ],

        'orm_default' => [
            'drivers' => [
                __NAMESPACE__ . '\entities' => 'rampage_nexus_entities'
            ]
        ]
    ]
];
