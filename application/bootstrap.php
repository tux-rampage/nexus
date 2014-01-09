<?php


define('APPLICATION_DIR', __DIR__ . '/');
require_once __DIR__ . '/../vendor/autoload.php';

// Register the final exception handler
rampage\core\Application::registerExceptionHandler(true);

// Register the error to exception handler
//rampage\core\Application::registerDevelopmentErrorHandler(true);
