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

'use strict';

function ConfigureStates($stateProvider)
{
    $stateProvider
        .state('ansible', {
            'abstract': true,
            templateUrl: 'assets/ansible/templates/index.html',
            uiNav: {
                label: 'Ansible Inventory',
            }
        })
        .state('ansible.hosts', {
            'abstract': true,
            templateUrl: 'assets/ansible/templates/hosts.html',
            url: '/ansible/hosts'
        })
        .state('ansible.hosts.list', {
            views: {
                'main': { component: 'ansibleHostList' }
            },
            url: '',
            uiNav: {
                label: 'Hosts',
                icon: 'storage',
                parent: 'ansible'
            }
        })
        .state('ansible.hosts.add', {
            views: {
                'main': { component: 'ansibleHost' }
                //'toolbar': { templateUrl: 'assets/ansible/templates/hosts/toolbar/detail.html' }
            },
            url: '/add'
        })
        .state('ansible.hosts.detail', {
            views: {
                'main': { component: 'ansibleHost' },
                'toolbar': { templateUrl: 'assets/ansible/templates/hosts/toolbar/detail.html' }
            },
            url: '/h/{hostName:[a-zA-Z0-9_.-]+}'
        });
}

ConfigureStates.$inject = [ '$stateProvider' ];
module.exports = ConfigureStates;
