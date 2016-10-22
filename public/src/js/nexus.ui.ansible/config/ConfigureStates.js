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

var angular = require('angular');

function ConfigureStates($stateProvider)
{
    function createCrudState(url, caption, icon) {
        return {
            'abstract': true,
            component: 'crudContainer',
            url: url,
            resolve: {
                caption: function() { return caption; },
                icon: function() { return icon; }
            }
        };
    };

    /**
     * Creates a list state config
     */
    function createListState(component, label, icon)
    {
        var config = {
            views: {
                'main': { component: component }
            },
            url: '',
            uiNav: {
                label: label,
                icon: icon,
                parent: 'ansible'
            }
        };

        console.debug(config);

        return config;
    };

    /**
     * Creates a form state config
     *
     * @param {String} component
     * @param {String} url
     * @param {String|Object} toolbar A string with the template relative to "assets/ansible/tem
     * @returns {Object}
     */
    function createFormState(component, url, toolbar)
    {
        var config = {
            views: {
                main: { component: component },
                toolbar: { templateUrl: 'assets/templates/crud/toolbar/detail.html' }
            },
            url: url
        }

        if (toolbar) {
            config.views.toolbar = (angular.isString(toolbar))? { templateUrl: 'assets/ansible/templates/' + toolbar } : toolbar;
        }

        return config;
    };

    $stateProvider
        .state('ansible', {
            'abstract': true,
            template: '<ui-view></ui-view>',
            uiNav: {
                label: 'Ansible Inventory',
            }
        })
        .state('ansible.hosts', createCrudState('/ansible/hosts', 'Ansible Inventory // Hosts', 'storage'))
        .state('ansible.hosts.list', createListState('ansibleHostList', 'Hosts', 'storage'))
        .state('ansible.hosts.add', createFormState('ansibleHost', '/add'))
        .state('ansible.hosts.detail', createFormState('ansibleHost', '/h/{hostName:[a-zA-Z0-9_.-]+}'))

        .state('ansible.groups', createCrudState('/ansible/groups', 'Ansible Inventory // Groups', 'style'))
        .state('ansible.groups.list', createListState('ansibleGroupList', 'Groups', 'style'))
        .state('ansible.groups.add', createFormState('ansibleGroup', '/add'));
}

ConfigureStates.$inject = [ '$stateProvider' ];
module.exports = ConfigureStates;
