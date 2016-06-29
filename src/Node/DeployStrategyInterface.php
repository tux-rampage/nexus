<?php
/**
 * Copyright (c) 2016 Axel Helmert
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Axel Helmert
 * @copyright Copyright (c) 2016 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace Rampage\Nexus\Node;

use Rampage\Nexus\Entities\ApplicationInstance;

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
