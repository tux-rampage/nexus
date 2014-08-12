<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2014 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 */

namespace rampage\nexus\features;

interface PackageTypeProviderInterface
{
    /**
     * @return \rampage\nexus\ApplicationPackageInterface[]
     */
    public function getDeploymentPackageTypes();
}
