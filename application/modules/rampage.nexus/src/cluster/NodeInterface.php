<?php
/**
 * Copyright (c) 2014 Axel Helmert
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
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\cluster;

use rampage\nexus\entities\ApplicationInstance;


interface NodeInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @param string $id
     */
    public function assignId($id);

    /**
     * @param ApplicationInstance $application
     */
    public function stage(ApplicationInstance $application);

    /**
     * @param ApplicationInstance $application
     */
    public function unstage(ApplicationInstance $application);

    /**
     * @param ApplicationInstance $application
     */
    public function activate(ApplicationInstance $application);

    /**
     * @param ApplicationInstance $application
     */
    public function deactivate(ApplicationInstance $application);
}
