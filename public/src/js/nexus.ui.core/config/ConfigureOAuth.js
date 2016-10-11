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
 * @param {String} cname
 * @returns {String}
 */
function getCookie(cname)
{
    if (!cname.match(/^[a-z0-9_-]+$/i)) {
        console.warn('Unsupported cookie name: "' + cname + '"');
        return null;
    }

    var cookies = document.cookie.split(';');
    var namePattern = new RegExp('^\\s*' + cname + '\\s*=\\s*(.+)\\s*$');

    for (var i = 0; i < cookies.length; i++) {
        var c = cookies[i];
        var match = namePattern.exec(c);

        if (match) {
            return match[1];
        }
    }

    return null;
}

function ConfigureOAuth(OAuthProvider, OAuthTokenProvider, C)
{
    var secret = getCookie(C.AUTH.SECRET_COOKIE);

    if (!secret) {
        console.warn('No oauth client secret available!');
    }

    OAuthProvider.configure({
        baseUrl: C.AUTH.URL,
        clientId: C.AUTH.CLIENT_ID,
        clientSecret: secret,
        grantPath: '/token',
        revokePath: '/revoke'
    });

    OAuthTokenProvider.configure({
        options: {
            secure: false
        }
    });
}

ConfigureOAuth.$inject = [ 'OAuthProvider', 'OAuthTokenProvider', 'CONSTANTS' ];
module.exports = ConfigureOAuth;
