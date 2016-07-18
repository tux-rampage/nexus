<?php
/**
 * Contains the default zend expressive configuration
 */

namespace Rampage\Nexus;

use Zend\Expressive\ConfigManager\ConfigManager;
use Zend\Expressive\ConfigManager\PhpFileProvider;
use Zend\Di\ConfigProvider as DiConfigProvider;

$configManager = new ConfigManager(
    [
        new DiConfigProvider(),
        new PhpFileProvider(__DIR__ . '/core.d/*.conf.php'),
    ]
);

return $configManager->getMergedConfig();
