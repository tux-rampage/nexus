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

use Rampage\Nexus\Exception\LogicException;

/**
 * Job resource
 */
class Job
{
    use ResourceTrait;

    /**
     * @var Job
     */
    private $group;

    /**
     * Sub jobs
     *
     * @var array
     */
    private $jobs = [];

    /**
     * Loaded builds
     *
     * @var array
     */
    private $builds = [];

    /**
     * @param string $name
     * @param array $data
     */
    public function __construct($data, Job $group = null, ClientInterface $api = null)
    {
        if ($group && !$group->isGroup()) {
            throw new LogicException('Cannot define non-group job as group');
        }

        $this->group = $group;
        $this->_construct($data);
    }

    /**
     * The full qualified job name
     *
     * @return string
     */
    public function getFullName()
    {
        if ($this->group) {
            return $this->group->getFullName() . '/job/' . $this->getName();
        }

        return $this->getName();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->properties->get('url');

    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->properties->get('name');

    }

    /**
     * Returns whether this is a group job or not
     *
     * @return bool
     */
    public function isGroup()
    {
        return isset($this->data['jobs']);
    }

    /**
     * Returns the build ids for this job
     *
     * @return int[]
     */
    public function getBuilds()
    {
        $builds = $this->properties->get('builds');

        if (!is_array($builds)) {
            return [];
        }

        return array_map(function($build) { return $build['number']; }, $builds);
    }

    /**
     * Get nested jobs
     *
     * @return string[]
     */
    public function getJobs()
    {
        if (!$this->isGroup()) {
            return [];
        }

        $jobs = $this->properties->get('jobs');
        return array_map(function($job) { return $job['name']; }, $jobs);
    }

    /**
     * @param string $id
     * @return Build
     */
    public function getBuild($id)
    {
        if (!isset($this->builds[$id])) {
            $this->builds[$id] = $this->getApi()->getBuild($this, $id);
        }

        return $this->builds[$id];
    }

    /**
     * @param string $name
     */
    public function getJob($name)
    {
        if (!$this->isGroup()) {
            throw new LogicException('Cannot get a sub-job from a non-group job');
        }

        if (!isset($this->jobs[$name])) {
            $this->jobs[$name] = $this->getApi()->getJob($name, $this);
        }

        return $this->jobs[$name];
    }
}
