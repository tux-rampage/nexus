<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2015 LUKA netconsult GmbH (www.luka.de)
 */

namespace rampage\nexus;

use rampage\nexus\entities\ApplicationInstance;


interface DeployStrategyInterface
{
    public function deploy(ApplicationInstance $instance);
}
