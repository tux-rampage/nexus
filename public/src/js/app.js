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
var ngAnimate = require('angular-animate');
var ngAria = require('angular-aria');
var ngResource = require('angular-resource');
var ngMaterial = require('angular-material');

var addConstants = require('./constants');
var configure = require('./config');
var addComponents = require('./components');
var addServices = require('./services');

require('angular-ui-router/release/angular-ui-router');

var module = angular.module('uiRampageNexus', [ngMaterial, 'ui.router', ngResource, ngAria, ngAnimate]);

addConstants(module);
addComponents(module);
addServices(module);

configure(module);

module.exports = {
    name: 'uiRampageNexus',
    module: module
};
