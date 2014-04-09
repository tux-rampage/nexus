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
    'factories' => array(
        'WebConfig' => services\WebConfigFactory::class,
        'SystemConfig' => services\SystemConfigFactory::class,
    ),
    'aliases' => array(
        DeployStrategyInterface::class => 'DeployStrategy',
        WebConfigInterface::class => 'WebConfig',
    )
);
