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

function Authentication(OAuth, OAuthToken)
{
    this.$resolved = false;
    this.$promise = null;

    this.isAuthenticated = OAuth.isAuthenticated();

    /**
     * Authenticate by the given credentials
     */
    this.authenticate = function(credentials)
    {
        var _self = this;
        var result = Object.create(this);

        this.$promise = OAuth.getAccessToken(credentials);

        this.$promise.then(function(response) {
            result.token = response.data;
            _self.isAuthenticated = OAuth.isAuthenticated();
        });

        this.$promise['finally'](function(rejection) {
            _self.$resolved = true;
        });

        return result;
    };
};

Authentication.Factory = function(OAuth, OAuthToken) {
    return new Authentication(OAuth, OAuthToken);
};

Authentication.Factory.$inject = ['OAuth', 'OAuthToken'];
module.exports = Authentication.Factory;
