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

namespace Rampage\Nexus\Job;

use Serializable;

/**
 * Defines the interface for jobs
 */
interface JobInterface extends Serializable
{
    /**
     * Returns the job priority
     *
     * This is only a hint for the Queue/Scheduler implementation
     * It's up to the Scheduler implementation whether to respect it or not
     *
     * @return  int The job priority
     */
    public function getPriority();

    /**
     * Execute the job
     *
     * @throws  \Throwable  This method may throw any exception. It's up to the Queue/Scheduler implementation to handle it properly
     * @return void
     */
    public function run();
}
