<?php

namespace rampage\nexus;

use rampage\core\Application;
use Zend\Http\Request;

require_once __DIR__ . '/../application/bootstrap.php';
$app = Application::init(include APPLICATION_DIR . 'config/application.conf.php');
$request = new Request();

$request->getUri()->setPath('/index/no-route');
$app->getMvcEvent()->setRequest($request);
$app->run();