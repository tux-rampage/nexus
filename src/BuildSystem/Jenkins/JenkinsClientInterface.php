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

namespace Rampage\Nexus\BuildSystem\Jenkins;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface to interact with Jenkins instances
 */
interface ClientInterface
{
    /**
     * Returns all jobs on the jenkins instance
     *
     * @return string[]
     */
    public function getJobs();

    /**
     * Returns the requested job instance
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @param string $name
     * @param Job $group
     * @return Job
     */
    public function getJob($name, Job $group = null);

    /**
     * Returns the requested build for the given job instance
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @param Job $job
     * @param int $id
     * @return Build
     */
    public function getBuild(Job $job, $id);

    /**
     * Download the given artifact
     *
     * @param   Artifact    $artifact   The artifact to download
     * @param   string      $toFile     The file to download to. If omitted the content will be returned
     * @return  ResponseInterface       The HTTP response
     */
    public function downloadArtifact(Artifact $artifact, $toFile = null);
}
