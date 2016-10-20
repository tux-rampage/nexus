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

namespace Rampage\Nexus\Ansible;

use Rampage\Nexus\Repository\NodeRepositoryInterface;
use Rampage\Nexus\MongoDB\Repository\NodeRepository as DefaultNodeRepository;

/**
 * Config provider for ansible module
 */
class ConfigProvider
{
    /**
     * Provides the configuration
     */
    public function getConfig()
    {
        return [
            'ui' => [
                'modules' => [
                    'ansible' => 'nexus.ui.ansible'
                ]
            ],
            'di' => [
                'preferences' => [
                    Repository\GroupRepositoryInterface::class => MongoDB\GroupRepository::class,
                    Repository\HostRepositoryInterface::class => MongoDB\HostRepository::class,
                    NodeRepositoryInterface::class => MongoDB\NodeRepository::class,
                    InventoryProviderInterface::class => InventoryProvider::class,
                ],
                'instances' => [
                    MongoDB\HostRepository::class => [
                        'preferences' => [
                            NodeRepositoryInterface::class => DefaultNodeRepository::class,
                        ],
                        'parameters' => [
                            'groupRepository' => MongoDB\GroupRepository::class,
                        ]
                    ],

                    MongoDB\NodeRepository::class => [
                        'preferences' => [
                            NodeRepositoryInterface::class => DefaultNodeRepository::class,
                        ]
                    ]
                ]
            ],
            'commands' => [
                'ansible:inventory' => Command\InventoryCommand::class,
            ],
            'rest' => [
                'routes' => [
                    'ansible/hosts' => [
                        'name' => 'ansible/hosts',
                        'path' => '/ansible/hosts[/{id}]',
                        'middleware' => Action\HostAction::class,
                        'allow_methods' => [ 'GET', 'PUT', 'POST', 'DELETE' ],
                    ],

                    'ansible/groups' => [
                        'name' => 'ansible/groups',
                        'path' => '/ansible/groups[/{id}]',
                        'middleware' => Action\GroupAction::class,
                        'allow_methods' => [ 'GET', 'PUT', 'POST', 'DELETE' ],
                    ]
                ]
            ]
        ];
    }

    /**
     * @return string[][]
     */
    public function __invoke()
    {
        return $this->getConfig();
    }
}
