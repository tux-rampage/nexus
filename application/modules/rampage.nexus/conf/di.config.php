<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2014 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\nexus;

return array(
    'definition' => array(
        'compiler' => array(
            __DIR__ . '/_compiled-di-definition.php'
        ),
        'runtime' => array(
            'enabled' => true,
        ),
    ),
    'instance' => array(
        'aliases' => array(
            'ConfigTemplateLocator' => ConfigTemplateLocator::class,
            'DeployStrategy' => DefaultDeployStrategy::class,
            'WebConfig' => WebConfigInterface::class,
        ),
        'preferences' => array(
            ConfigTemplateLocator::class => 'ConfigTemplateLocator',
            DefaultDeployStrategy::class => 'DeployStrategy',
            WebConfigInterface::class => 'WebConfig',
        ),

        'DeployStrategy' => array(
            'injections' => array('WebCofig'),
        ),
    ),
);
