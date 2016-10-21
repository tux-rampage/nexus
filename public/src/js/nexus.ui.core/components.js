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

var C = require('./constants/Constants');

module.exports = function(ngModule) {
    ngModule
        .component('crudContainer', {
            templateUrl: 'assets/templates/crud/index.html',
            controller: require('./controllers/CrudContainerController'),
            bindings: {
                caption: '<',
                icon: '<'
            }
        })
        .component('uiNavigation', {
            templateUrl: 'assets/templates/navigation.html',
            controller: require('./controllers/NavigationController')
        })
        .component('uiLogin', {
            templateUrl: 'assets/templates/login.html',
            controller: require('./controllers/AuthController')
        })
        .component('uiAppList', {
            templateUrl: C.TEMPLATES.COLLECTION,
            controller: require('./controllers/ApplicationsController')
        })
        .component('uiAppDetail', {
            templateUrl: 'assets/templates/apps/detail.html',
            controller: require('./controllers/ApplicationDetailController')
        });
};
