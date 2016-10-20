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

/**
 * @param {$state} $state
 * @param {$log} $log
 */
function NavigationController($state, $log)
{
    var states = $state.get();
    var subItems = {};

    this.items = [];

    /**
     * Push a child item
     */
    function pushChild(state)
    {
        if (!subItems[state.uiNav.parent]) {
            subItems[state.uiNav.parent] = [];
        }

        subItems[state.uiNav.parent].push(state);
    }

    for (var i = 0; i < states.length; i++) {
        var state = states[i];

        if (!state.uiNav) {
            continue;
        }

        if (state.uiNav.parent) {
            pushChild(state);
            continue;
        }

        this.items.push(state);
    }

    this.getChildren = function(state)
    {
        if (!subItems[state.name]) {
            return [];
        }

        return subItems[state.name];
    }

    this.close = function() {
        $mdSideNav('ui-nav').close();
    };
};

NavigationController.$inject = ['$state', '$log'];
module.exports = NavigationController;
