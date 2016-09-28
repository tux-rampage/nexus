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
 * @param {Authentication} auth
 */
function AuthController(auth)
{
    this.busy = false;
    this.auth = auth;
    this.credentials = {};

    this.login = function() {
        this.busy = true;

        auth.authenticate(this.credentials).$promise['finally'](function() {
            this.busy = false;
        });
    };
}

AuthController.$inject = ['$state', '$log', 'rampage.nexus.Authentication'];
module.exports = AuthController;
