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

var LoaderError = require('./LoaderError');

/**
 * Promise API: Prefer a native implementation if available - fallback to Polyfill if not
 */
if (!Promise) {
    var Promise = global.Promise || require('promise-polyfill');

    if (!global.Promise) {
        console.debug('No builtin Promise API: Fallback to polyfill');
        Promise._immediateFn = require('setasap');
    }
}

/**
 * A very simple AMD Loader
 *
 * This is not intended to implement a std amd loader. Its sole purpose is
 * to enable async angular module loading, to keep the whole application modular
 */
function Loader()
{
    var _self = this;
    var deferred = {};
    var baseUrl = 'js/';
    var defined = {};

    var loaded = {
        amd: this
    };

    /**
     * Load a script URL
     *
     * @param {String} name
     * @return {Promise}
     */
    function loadScript(name)
    {
        var url = baseUrl + name + '.js';
        var head = document.getElementsByTagName("head")[0] || document.documentElement;

        // Handle Script loading
        var done = false;

        return new Promise(function(resolve, reject) {
            var script = document.createElement("script");

            script.type = 'text/javascript';
            script.async = true;

            /**
             * Handle on load event
             */
            function onScriptLoad(event) {
                if (done || (this.readyState && (this.readyState !== "loaded") && (this.readyState !== "complete"))) {
                    return;
                }

                console.debug('Script "' + name + '" loaded');
                done = true;
                resolve(script);

                // Handle memory leak in IE
                script.onload = script.onreadystatechange = null;
                if (head && script.parentNode ) {
                    head.removeChild(script);
                }
            };

            /**
             * Handle loading script errors
             */
            function onError(e) {
                reject(new URIError('Failed to load script: ' + e.target.src));
            };

            script.addEventListener('load', onScriptLoad);
            script.addEventListener('error', onError);

            script.src = url;
            head.appendChild(script);
        });
    }

    /**
     * Resolve the module and its dependencies
     */
    function resolveModule(factory, deps)
    {
        if (!deps) {
            return factory();
        }

        return loadAll(deps).then(function(resolvedDeps) {
            loaded[name] = factory.apply(global, resolvedDeps);
            return loaded[name];
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
            return Promise.resolve(loaded[name]);
        }

        if (deferred[name]) {
            return deferred[name].promise;
        }

        var defer = {};
        deferred[name] = defer;

        defer.promise = loadScript(name).then(function() {
            if (!defined[name]) {
                return Promise.reject(new Error('Failed to load "' + name + '": Missing define() for module'));
            }

            return resolveModule(defined[name].factory, defined[name].deps);
        });

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
     * @param {String} url
     */
    this.setBaseUrl = function(url)
    {
        if (!url.match(/\/$/)) {
            url += '/';
        }

        baseUrl = url;
        return this;
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

        var promise = loadAll(dependencies);

        promise.then(function(deps) {
            factory.apply(global, deps);
        })['catch'](function(rejection) {
            throw new LoaderError(rejection);
        });
    };

    /**
     * Reads all dependencies from the document
     */
    this.getUiDepsFromDocument = function()
    {
        var root = document.getElementById('nexus.ui');
        var attr = root? root.getAttribute('data-modules') : null;
        var deps = [];

        try {
            deps = attr? JSON.parse(attr) : [];
        } catch (e) {
            console.error(e);
        }

        if (!deps instanceof Array) {
            console.warn('nexus.ui.amd.Loader::getUiDepsFromDocument(): Bad dependencies type');
            deps = [];
        }

        return deps;
    };
}

module.exports = Loader;
