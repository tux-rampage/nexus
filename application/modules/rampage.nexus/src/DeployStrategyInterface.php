<?php
/**
 * This is part of rampage-nexus
 * Copyright (c) 2013 Axel Helmert
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
 * @category  library
 * @author    Axel Helmert
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus;

/**
 * Deploy strategy interface
 */
interface DeployStrategyInterface
{
    /**
     * Set the current application instance to perform the actions on
     *
     * @param entities\ApplicationInstance $instance
     * @return self
     */
    public function setApplicationInstance(entities\ApplicationInstance $instance);

    /**
     * Set the user parameters to use for deployment
     *
     * @param array|\ArrayAccess $parameters
     * @retrun self
     */
    public function setUserParameters($parameters);

    /**
     * Set the document root relative to the target directory
     *
     * @param string $dir
     * @return self
     */
    public function setWebRoot($dir);


    /**
     * Returns the darget directory of the application
     *
     * @return string
     */
    public function getTargetDirectory();

    /**
     * Prepare staging the application
     */
    public function prepareStaging();

    /**
     * Complete staging the application
     */
    public function completeStaging();

    /**
     * Prepare removing the application
     */
    public function prepareRemoval();

    /**
     * Complete removing the application
     *
     * This method will do cleanup tasks like removing the directory,
     * removing unused configs, deactivating the vhost (if applicable) and so on
     */
    public function completeRemoval();

    /**
     * Activate the application
     */
    public function activate();

    /**
     * Deactivate the application
     */
    public function deactivate();
}
