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

use Rampage\Nexus\Exception\UnexpectedValueException;
use Rampage\Nexus\Entities\Api\ArrayExportableInterface;

class BuildNotification implements ArrayExportableInterface
{
    use ResourceTrait;

    public function __construct(array $data)
    {
        $this->_construct($data);
    }

    /**
     * Returns the Jenkins URL
     *
     * @return string
     */
    public function getJenkinsUrl()
    {
        $fullUrl = $this->properties->get('build.full_url');
        $url = $this->properties('build.url');
        $offset = strpos($fullUrl, $url);

        if (!$offset) {
            throw new UnexpectedValueException(sprintf('The build url "%s" is expected to be present in the full url "%s".', $url, $fullUrl));
        }

        return substr($fullUrl, 0, $offset);
    }

    public function getJobName()
    {
        return $this->properties->get('name');
    }

    /**
     * Returns the job names if this is a group job
     *
     * @return string[]|null
     */
    public function getFacetedJobNames()
    {
        $url = $this->properties->get('url');
        $url = preg_replace('~^job/~', '', $url);
        $jobs = explode('/job/', $url);

        array_shift($jobs);

        if (empty($jobs)) {
            return null;
        }

        return $jobs;
    }

    /**
     * The affected build id, that triggered this notiication
     *
     * @return int
     */
    public function getBuildId()
    {
        return $this->properties->get('build.number');
    }

    /**
     * Returns the build phase
     *
     * @return string
     */
    public function getPhase()
    {
        return $this->properties->get('build.phase');
    }

    /**
     * Returns the build status
     *
     * @return mixed
     */
    public function getStatus()
    {
        return $this->properties->get('build.status');
    }

    /**
     * @return boolean
     */
    public function isStable()
    {
        return ($this->getStatus() == 'SUCCESS');
    }

    /**
     * @return boolean
     */
    public function isUsable()
    {
        return in_array($this->getStatus(), ['SUCCESS', 'UNSTABLE']);
    }

    /**
     * {@inheritDoc}
     * @see \Rampage\Nexus\Entities\Api\ArrayExportableInterface::toArray()
     */
    public function toArray()
    {
        return $this->data->getArrayCopy();
    }
}
