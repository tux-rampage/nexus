(function() {
    'use strict';

    (function(factory) {
        if (typeof(define) != 'undefined') {
            define(['angular'], factory);
        } else {
            factory(angular);
        }
    }(function(angular) {
        var app = angular.module('rampageNexus', [
          'ngRoute',
          'rampageNexusControllers',
          'rampageNexusServices',
        ]);

        app.config(['$routeProvider', 'rampageNexusRouterService'], function($routeProvider, routerService) {
            routerService.attach($routeProvider);
        });
    }));
}());
