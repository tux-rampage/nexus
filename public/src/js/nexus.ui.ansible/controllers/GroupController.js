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

function GroupController(api)
{
    var _self = this;
    this.progress = { $resolved: true };

    if (!this.group) {
        this.group = {
            children: []
        };
    }

    this.save = function()
    {
        var promise;

        this.progress = {
            $resolved: false,
            success: undefined,
            error: undefined
        };

        if (!this.group.$save || !this.group.id) {
            var result = (this.group.id)?
                            api.ansible.groups.save({}, this.group) :
                            api.ansible.groups.create({}, this.group);

            angular.extend(result, this.group);
            this.group = result;
            promise = result.$promise;
        } else {
            promise = this.group.$save();
        }

        promise['finally'](function() {
            _self.progress.$resolved = true;
        });

        promise.then(function(result) {
            _self.progress.success = true;
        })['catch'](function(context) {
            _self.progress.error = true;
        });
    }
}

GroupController.$inject = [ 'rampage.nexus.RestApi' ];
module.exports = GroupController;
