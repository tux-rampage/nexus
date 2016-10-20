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

module.exports = (function() {
    var angular = require('angular');
    var ngAnimate = require('angular-animate');
    var ngAria = require('angular-aria');
    var ngResource = require('angular-resource');
    var ngMaterial = require('angular-material');
    var ngCookies = require('angular-cookies');

    var addConstants = require('./constants');
    var addControllers = require('./controllers');
    var addComponents = require('./components');
    var addServices = require('./services');

    var configure = require('./config');
    var initialize = require('./init');

    require('angular-ui-router/release/angular-ui-router');
    require('angular-oauth2/dist/angular-oauth2');

    var deps = [ngMaterial, 'ui.router', 'angular-oauth2', ngResource, ngAria, ngAnimate];
    var module = angular.module('nexus.ui.core', deps);

    addConstants(module);
    addComponents(module);
    addServices(module);
    addControllers(module);

    configure(module);
    initialize(module);

    return 'nexus.ui.core'
}());
