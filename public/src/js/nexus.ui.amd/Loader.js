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

var Promise = require('promise-polyfill');
var setAsap = require('setasap');

Promise._immediateFn = setAsap;

/**
 * AMD Loader
 */
function Loader()
{
    var _self = this;
    var deferred = {};
    var baseUrl = '';
    var shims = {}

    var loaded = {
        amd: this
    };

    /**
     * Create a shim factory
     *
     * @param {String} name
     * @return {Function}
     */
    function createShimFactory(name)
    {
        var exports = shims[name].exports;
        var shim = Object.create(shims[name]);
        shim.exports = (exports)? global[exports] : undefined;

        var factory = shim[name].factory || function() {
            return this.exports;
        };

        return function() {
            factory.apply(shim, arguments);
        };
    }

    /**
     * Load a script URL
     */
    function loadScript(name, deferred)
    {
        var url = baseUrl + name;
        var head = document.getElementsByTagName("head")[0] || document.documentElement;
        var script = document.createElement("script");

        // Handle Script loading
        var done = false;

        // Attach handlers for all browsers
        script.onload = script.onreadystatechange = function() {
            if (done || (this.readyState && (this.readyState !== "loaded") && (this.readyState !== "complete"))) {
                return;
            }

            done = true;

            // Handle memory leak in IE
            script.onload = script.onreadystatechange = null;
            if (head && script.parentNode ) {
                head.removeChild(script);
            }
        };

        script.src = url;
    }

    /**
     * Load a module
     *
     * @param {String} name
     * @returns {Promise}
     */
    function load(name)
    {
        if (loaded[name]) {
            return loaded[name];
        }

        if (deferred[name]) {
            return deferred[name].promise;
        }

        var def = {
            resolved: false
        };

        def.promise = new Promise(function(resolve, reject) {
            def.resolve = resolve;
            def.reject = reject;
        });

        deferred[name] = def;

        if (shims[name]) {
            // FIXME: handle shims
        } else {
            loadScript(name, def);
        }

        return def.promise;
    }

    /**
     * Load all given modules async
     *
     * @private
     * @param {Array} modules
     * @returns {Promise}
     */
    function loadAll(modules)
    {
        var promises = [];

        for (var i = 0; i < modules.length; i++) {
            promises.push(load(modules[i]));
        }

        return Promise.all(promises);
    }

    /**
     * Set a resolved dependency
     *
     * @param {String} name
     * @param {Object} module
     */
    this.set = function(name, module)
    {
        loaded[name] = module;

        if (deferred[name] && !deferred[name].resolved) {
            deferred[name].resolved = true;
            deferred[name].resolve(module);
        }
    };

    /**
     * Define a module
     */
    this.define = function(name, dependencies, factory)
    {
        if (!factory) {
            factory = dependencies;
            dependencies = [];
        }

        if (!dependencies.length) {
            _self.set(name, factory());
            return;
        }

        promise = loadAll(dependencies);

        promise.then(function(deps) {
            _self.set(name, factory.apply(global, deps));
        });

        promise['catch'](function(rejection) {
            if (!deferred[name] || deferred[name].resolved) {
                return;
            }

            deferred[name].resolved = true;
            deferred[name].reject(rejection);
        });
    }

    /**
     * Require dependencies
     */
    this.require = function(dependencies, factory)
    {
        if (!dependencies.length) {
            return factory();
        }

        promise = loadAll(dependencies);

        promise.then(function(deps) {
            factory.apply(global, deps);
        });

        promise['catch'](function(rejection) {
            if (rejection instanceof Error) {
                throw rejection;
            }

            throw new Error(rejection);
        });
    };

    /**
     * Create a shim
     *
     * @param {String} name
     * @param {Object} config
     */
    this.shim = function(name, config)
    {
        shims[name] = config;
        return this;
    }
}

module.exports = Loader;
