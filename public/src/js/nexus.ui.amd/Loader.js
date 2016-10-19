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
    var defined = {};

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
        var config = shims[name];
        var exports = config.exports;
        var shim = Object.create(config);

        return function() {
            shim.exports = (exports)? global[exports] : undefined;
            var factory = config.factory || function() {
                return this.exports;
            };

            return factory.apply(shim, arguments);
        };
    }

    /**
     * Load a script URL
     *
     * @param {String} name
     * @return {Promise}
     */
    function loadScript(name)
    {
        var url = baseUrl + name;
        var head = document.getElementsByTagName("head")[0] || document.documentElement;

        if (shims[name]) {
            var shim = shims[name];
            url = shim.url || url;
        }

        // Handle Script loading
        var done = false;

        return new Promise(function(resolve, reject) {
            var script = document.createElement("script");

            script.type = 'text/javascript';

            // Attach handlers for all browsers
            script.onload = script.onreadystatechange = function() {
                if (done || (this.readyState && (this.readyState !== "loaded") && (this.readyState !== "complete"))) {
                    return;
                }

                done = true;
                resolve(script);

                // Handle memory leak in IE
                script.onload = script.onreadystatechange = null;
                if (head && script.parentNode ) {
                    head.removeChild(script);
                }
            };

            script.onerror = function(e) {
                reject(new URIError('Failed to load script: ' + e.target.src));
            };

            //head.appendChild(script);
            script.src = url;
        });
    }

    /**
     * Promise for loading the shim
     */
    function loadShim(name)
    {
        var deps = shims[name].deps;

        function _resolveShim(resolvedDeps) {
            return loadScript(name).then(function() {
                var factory = createShimFactory(name);
                var value = factory.apply(_self, resolvedDeps);
                _self.set(name, value);

                return value;
            });
        }

        if (deps && deps.length) {
            return loadAll(deps).then(function(resolvedDeps) {
                return _resolveShim(resolvedDeps);
            });
        }

        return _resolveShim([]);
    }

    /**
     * Resolve the module and its dependencies
     */
    function resolve(factory, deps)
    {
        if (!deps) {
            return factory();
        }

        return loadAll(deps).then(function(resolvedDeps) {
            return factory.apply(window, resolvedDeps);
        });
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

        var defer = {};
        deferred[name] = defer;

        if (shims[name]) {
            defer.promise = loadShim(name);
        } else {
            defer.promise = loadScript(name).then(function() {
                if (!defined[name]) {
                    return Promise.reject(new Error('Failed to load "' + name + '": Missing define() for module'));
                }

                return resolve(defined[name].factory, defined[name].deps);
            });
        }

        return defer.promise;
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

        defined[name] = {
            factory: factory
        };

        if (dependencies && dependencies.length) {
            defined[name].deps = dependencies;
        }
    };

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
        }).catch(function(rejection) {
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
