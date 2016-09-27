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

namespace Rampage\Nexus\OAuth2;

use Rampage\Nexus\Config\PropertyConfigInterface;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

return [
    'dependencies' => [
        'factories' => [
            ResourceServer::class => ServiceFactory\ResourceServerFactory::class,
            AuthorizationServer::class => ServiceFactory\AuthorizationServerFactory::class,
        ]
    ],
    'di' => [
        'preferences' => [
            AccessTokenRepositoryInterface::class => MongoDB\Repository\RefreshTokenRepository::class,
            ClientRepositoryInterface::class => Repository\UIClientRepository::class,
            ScopeRepositoryInterface::class => Repository\ScopeRepository::class,
        ],
        'instances' => [
            Repository\UIClientRepository::class => [
                'preferences' => [
                    PropertyConfigInterface::class => 'RuntimeConfig',
                ]
            ]
        ]
    ]
];