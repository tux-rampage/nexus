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

import angular from 'angular';
import ngAnimate from 'angular-animate';
import ngAria from 'angular-aria';
import ngRoute from 'angular-route';
import ngResource from 'angular-resource';
import ngMaterial from 'angular-material';
import 'angular-ui-router/release/angular-ui-router';
import configure from './config';
import addComponents from './components';

var module = angular.module('uiRampageNexus', [ngMaterial, ngRoute, ngResource, ngAria, ngAnimate]);

configure(module);
addComponents(module);

export default {
    name: 'uiRampageNexus',
    module: module
};
