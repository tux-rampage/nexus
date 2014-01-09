<?php

// OPTIONAL: Change the directory one level up
chdir(dirname(__DIR__));

require_once __DIR__ . '/../application/bootstrap.php';
rampage\core\Application::init(include APPLICATION_DIR . 'config/application.conf.php')->run();
