<?php
/**
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2015 LUKA netconsult GmbH (www.luka.de)
 */

namespace rampage\nexus\node;

use rampage\nexus\entities\ApplicationInstance;


/**
 * Interface for deploy strategies.
 */
interface DeployStrategyInterface
{
    /**
     * Remove everythig to allow building from scratch
     */
    public function purge();

    /**
     * Stage the given application instance and prepare it for activation
     *
     * @param ApplicationInstance $instance
     */
    public function stage(ApplicationInstance $instance);

    /**
     * @param ApplicationInstance $instance
     */
    public function prepareActivation(ApplicationInstance $instance);

    /**
     * Activate the given application instance
     *
     * @param ApplicationInstance $instance
     */
    public function activate(ApplicationInstance $instance);

    /**
     * Remove the given application instance
     *
     * @param ApplicationInstance $instance
     */
    public function remove(ApplicationInstance $instance);

    /**
     * Roll back to the given instance
     *
     * @param ApplicationInstance $toInstance
     */
    public function rollback(ApplicationInstance $toInstance);
}
