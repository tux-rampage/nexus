<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2015 LUKA netconsult GmbH (www.luka.de)
 */

namespace rampage\nexus;

use rampage\nexus\entities\ApplicationInstance;


interface DeployStrategyInterface
{
    /**
     * @param ApplicationInstance $instance
     */
    public function deploy(ApplicationInstance $instance);

    /**
     * @param ApplicationInstance $instance
     */
    public function remove(ApplicationInstance $instance);

    /**
     * @param ApplicationInstance $instance
     */
    public function rollback(ApplicationInstance $instance);
}
