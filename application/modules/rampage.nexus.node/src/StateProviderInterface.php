<?php
/**
 * Copyright (c) 2015 Axel Helmert
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
 * @copyright Copyright (c) 2015 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\node;

use rampage\nexus\entities\ApplicationInstance;


/**
 * Implementation for the current application state
 */
interface StateProviderInterface
{
    /**
     * Get the state of all installed applications
     *
     * @return ApplicationInstance[]
     */
    public function getInstalledApplications();

    /**
     * Update the given application instance
     *
     * @param ApplicationInstance $instance
     */
    public function publishApplicationState(ApplicationInstance $instance);

    /**
     * Get the currently installed package file for this application instance
     *
     * @param ApplicationInstance $instance  The application instance to get the package for
     * @return \SplFileInfo|null  The fileinfo of the package or null if the package could not be located
     */
    public function getInstalledApplicationPackage(ApplicationInstance $instance);

    /**
     * Update the application state
     *
     * @param ApplicationInstance $instance    The Instance to update state information for
     * @param bool                $statusOnly  Only update the status, when this is true
     * @return self
     */
    public function updateApplicationState(ApplicationInstance $instance);

    /**
     * Remove the application
     *
     * This is called when an application is removed. It will remove all state information
     * for the given instance.
     *
     * @param ApplicationInstance $instance
     * @return self
     */
    public function removeApplication(ApplicationInstance $instance);
}
