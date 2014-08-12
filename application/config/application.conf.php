<?php
/**
 * Copyright (c) 2014 Axel Helmert
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
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus;


// This is the ZF2 MVC config
// Refer to http://framework.zend.com/ for further information
return array(
    'modules' => require __DIR__ . '/modules.conf.php',

    // Define additional pathmanager locations
    'path_manager' => array(
        '__file__' => '{{root_dir}}/etc/paths.conf',
        'root' => RAMPAGE_PREFIX,
        'app' => APPLICATION_DIR,
        'etc' => '{{root_dir}}/etc',
    ),

    // These are various options for the listeners attached to the ModuleManager
    'module_listener_options' => array(
        // An array of paths from which to glob configuration files after
        // modules are loaded. These effectively override configuration
        // provided by modules themselves. Paths may use GLOB_BRACE notation.
        'config_glob_paths' => ConfigListenerOptions::getGlobPaths(__DIR__),
        'config_static_paths' => ConfigListenerOptions::getStaticPaths(),
    ),
);
