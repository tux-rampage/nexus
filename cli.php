<?php

require_once __DIR__ . '/application/bootstrap.php';
rampage\core\Application::init(include APPLICATION_DIR . 'config/application.conf.php')->run();
