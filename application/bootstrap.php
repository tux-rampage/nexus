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

if (version_compare(PHP_VERSION, '5.5', '<')) {
    trigger_error(sprintf('This software requires at least PHP version 5.5, you have version %s installed. Please upgrade your PHP version.', PHP_VERSION), E_USER_ERROR);
    exit(1); // Force exit if the triggered error does not cause a fail
}

// Dev environment?
if (isset($_SERVER['APPLICATION_DEVELOPMENT']) && $_SERVER['APPLICATION_DEVELOPMENT']) {
    define('APPLICATION_DEVELOPMENT', !Phar::running());
} else {
    define('APPLICATION_DEVELOPMENT', false);
}

// Prefix path
if (Phar::running()) {
    $phar = new Phar(Phar::running(false));
    $meta = $phar->getMetadata();
    define('RAMPAGE_PREFIX', (isset($meta['prefix']))? $meta['prefix'] : '/opt/rampage');
    unset($meta, $phar);
} else {
    define('RAMPAGE_PREFIX', dirname(__DIR__));
}

define('APPLICATION_DIR', __DIR__ . '/');
require_once __DIR__ . '/../vendor/autoload.php';

// Register the final exception handler
rampage\core\Application::registerExceptionHandler(true);
