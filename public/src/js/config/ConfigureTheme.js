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

module.exports = (function () {

    var ConfigureTheme = function ($mdThemingProvider) {
        $mdThemingProvider.definePalette('nexusPalette', {
            '50': '008ac8',
            '100': '008ac8',
            '200': '008ac8',
            '300': '008ac8',
            '400': '008ac8',
            '500': '008ac8',
            '600': '008ac8',
            '700': '008ac8',
            '800': '008ac8',
            '900': '008ac8',
            'A100': '008ac8',
            'A200': '008ac8',
            'A400': '008ac8',
            'A700': '008ac8',
            'contrastDefaultColor': 'light',    // whether, by default, text (contrast)
                                                // on this palette should be dark or light
            'contrastDarkColors': ['50', '100', //hues which contrast should be 'dark' by default
                '200', '300', '400', 'A100'],
            'contrastLightColors': undefined    // could also specify this if default was 'dark'
        });

        $mdThemingProvider.theme('default')
            .primaryPalette('nexusPalette')
    };

    ConfigureTheme.$inject = ['$mdThemingProvider'];
    return ConfigureTheme
}());
