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

var ui = require('nexus.ui.core');
var angular = require('angular');
var amd = require('nexus.ui.amd');

amd.set('angular', angular);

document.addEventListener("DOMContentLoaded", function(event) {
    var deps = amd.getUiDepsFromDocument();

    function _extractDeps(args)
    {
        var array = [];

        for (var i = 0; i < args.length; i++) {
            if (!angular.isString(args[i])) {
                continue;
            }

            array.push(args[i]);
        }

        return array;
    }

    amd.require(deps, function() {
        var modules = _extractDeps(arguments);

        console.debug({resolvedDeps: arguments, d: modules});
        modules.unshift(ui);

        var element = document.getElementById('nexus.ui');
        if (!element) {
            throw new Error('Bootstrapping failed: Missing root element');
        }

        angular.bootstrap(document, modules);
    })
});
