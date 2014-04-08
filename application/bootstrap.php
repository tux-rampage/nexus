<?php

if (version_compare(PHP_VERSION, '5.5', '<')) {
    trigger_error(sprintf('This software requires at least PHP version 5.5, you have version %s installed. Please upgrade your PHP version.', PHP_VERSION), E_USER_ERROR);
    exit(1); // Force exit if the triggered error does not cause a fail
}

define('APPLICATION_DIR', __DIR__ . '/');
require_once __DIR__ . '/../vendor/autoload.php';

// Register the final exception handler
rampage\core\Application::registerExceptionHandler(true);

// Register the error to exception handler
//rampage\core\Application::registerDevelopmentErrorHandler(true);
