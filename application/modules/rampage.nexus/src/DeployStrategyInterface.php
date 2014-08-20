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

use Zend\EventManager\ListenerAggregateInterface;

/**
 * Deploy strategy interface
 */
interface DeployStrategyInterface extends WebConfigAwareInterface, ListenerAggregateInterface
{
    /**
     * Returns the darget directory of the application
     *
     * @return string
     */
    public function getTargetDirectory();

    /**
     * Returns the base dir of this application
     *
     * This might be useful for chroot
     *
     * @return bool
     */
    public function getBaseDir();

    /**
     * Returns the full qualified web root directory path
     *
     * @return string
     */
    public function getWebRoot();

    /**
     * Returns the current application which is about to be deployed
     *
     * @return entities\ApplicationInstance
     */
    public function getApplication();
}
